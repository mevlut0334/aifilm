<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Template;
use App\Services\TemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TemplateController extends Controller
{
    public function __construct(
        private TemplateService $templateService
    ) {}

    public function index(): View
    {
        $templates = $this->templateService->getAllTemplates();

        return view('admin.templates.index', [
            'templates' => $templates,
        ]);
    }

    public function create(): View
    {
        return view('admin.templates.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title_en' => 'required|string|max:255',
            'title_tr' => 'nullable|string|max:255',
            'description_en' => 'nullable|string',
            'description_tr' => 'nullable|string',
            'token_cost' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'order' => 'nullable|integer|min:0',
            'landscape_video' => 'nullable|file|mimes:mp4,mov,avi,webm|max:51200',
            'landscape_video_url' => 'nullable|string',
            'portrait_video' => 'nullable|file|mimes:mp4,mov,avi,webm|max:51200',
            'portrait_video_url' => 'nullable|string',
            'square_video' => 'nullable|file|mimes:mp4,mov,avi,webm|max:51200',
            'square_video_url' => 'nullable|string',
        ]);

        $data = [
            'title' => [
                'en' => $validated['title_en'],
                'tr' => $validated['title_tr'] ?? $validated['title_en'],
            ],
            'description' => [
                'en' => $validated['description_en'] ?? '',
                'tr' => $validated['description_tr'] ?? $validated['description_en'] ?? '',
            ],
            'token_cost' => $validated['token_cost'],
            'is_active' => $request->boolean('is_active', true),
            'order' => $validated['order'] ?? null,
        ];

        $template = $this->templateService->createTemplate($data);

        // Upload videos if provided
        foreach (['landscape', 'portrait', 'square'] as $orientation) {
            $fileKey = "{$orientation}_video";
            $urlKey = "{$orientation}_video_url";

            if ($request->hasFile($fileKey)) {
                $this->templateService->uploadVideo(
                    $template,
                    $request->file($fileKey),
                    $orientation
                );
            } elseif ($request->filled($urlKey)) {
                $this->downloadAndStoreVideo($template, $request->input($urlKey), $orientation);
            }
        }

        return redirect()->route('admin.templates.index')
            ->with('success', 'Template başarıyla oluşturuldu.');
    }

    public function edit(Template $template): View
    {
        return view('admin.templates.edit', [
            'template' => $template,
        ]);
    }

    public function update(Request $request, Template $template): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'title_en' => 'required|string|max:255',
                'title_tr' => 'nullable|string|max:255',
                'description_en' => 'nullable|string',
                'description_tr' => 'nullable|string',
                'token_cost' => 'required|integer|min:0',
                'is_active' => 'boolean',
                'order' => 'nullable|integer|min:0',
                'landscape_video' => 'nullable|file|mimes:mp4,mov,avi,webm|max:51200',
                'landscape_video_url' => 'nullable|string',
                'portrait_video' => 'nullable|file|mimes:mp4,mov,avi,webm|max:51200',
                'portrait_video_url' => 'nullable|string',
                'square_video' => 'nullable|file|mimes:mp4,mov,avi,webm|max:51200',
                'square_video_url' => 'nullable|string',
            ]);

            $oldTokenCost = $template->token_cost;

            $data = [
                'title' => [
                    'en' => $validated['title_en'],
                    'tr' => $validated['title_tr'] ?? $validated['title_en'],
                ],
                'description' => [
                    'en' => $validated['description_en'] ?? '',
                    'tr' => $validated['description_tr'] ?? $validated['description_en'] ?? '',
                ],
                'token_cost' => $validated['token_cost'],
                'is_active' => $request->boolean('is_active'),
                'order' => $validated['order'] ?? $template->order,
            ];

            $this->templateService->updateTemplate($template, $data);

            // Upload new videos if provided
            foreach (['landscape', 'portrait', 'square'] as $orientation) {
                $fileKey = "{$orientation}_video";
                $urlKey = "{$orientation}_video_url";

                if ($request->hasFile($fileKey)) {
                    $this->templateService->uploadVideo(
                        $template,
                        $request->file($fileKey),
                        $orientation
                    );
                } elseif ($request->filled($urlKey)) {
                    $this->downloadAndStoreVideo($template, $request->input($urlKey), $orientation);
                }
            }

            $template->refresh();

            $message = 'Template başarıyla güncellendi.';
            if ($oldTokenCost != $template->token_cost) {
                $message .= ' Token maliyeti '.$oldTokenCost.' → '.$template->token_cost.' olarak değiştirildi.';
            }

            return redirect()->route('admin.templates.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Güncelleme sırasında hata oluştu: '.$e->getMessage());
        }
    }

    public function destroy(Template $template): RedirectResponse
    {
        $this->templateService->deleteTemplate($template);

        return redirect()->route('admin.templates.index')
            ->with('success', 'Template başarıyla silindi.');
    }

    public function deleteVideo(Request $request, string $uuid, string $orientation): RedirectResponse
    {
        $template = $this->templateService->getTemplateByUuid($uuid);
        abort_if(! $template, 404);

        if (! in_array($orientation, ['landscape', 'portrait', 'square'])) {
            abort(400, 'Geçersiz orientation değeri.');
        }

        $this->templateService->deleteVideo($template, $orientation);

        return back()->with('success', ucfirst($orientation).' video başarıyla silindi.');
    }

    public function toggleActive(string $uuid): RedirectResponse
    {
        $template = $this->templateService->getTemplateByUuid($uuid);
        abort_if(! $template, 404);

        $this->templateService->toggleActive($template);

        return back()->with('success', 'Template durumu güncellendi.');
    }

    private function downloadAndStoreVideo(Template $template, string $url, string $orientation): void
    {
        try {
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                $response = Http::timeout(120)->get($url);

                if ($response->successful()) {
                    // Get extension
                    $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
                    if (empty($extension)) {
                        $extension = 'mp4';
                    }

                    // Delete old video if exists
                    $oldPath = $template->getVideoPathForOrientation($orientation);
                    if ($oldPath) {
                        Storage::disk('public')->delete($oldPath);
                    }

                    // Store new video
                    $path = "templates/{$template->uuid}/{$orientation}/".time().'.'.$extension;
                    Storage::disk('public')->put($path, $response->body());

                    // Update template
                    $fieldName = "{$orientation}_video_path";
                    $template->update([$fieldName => $path]);
                }
            }
        } catch (\Exception $e) {
            // Silently fail - video won't be updated
        }
    }
}
