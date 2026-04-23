<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\GenerationRequestService;
use App\Services\TokenService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GenerationRequestController extends Controller
{
    public function __construct(
        private GenerationRequestService $generationRequestService,
        private TokenService $tokenService
    ) {}

    public function index(): View
    {
        $requests = $this->generationRequestService->getUserRequests(
            auth()->id(),
            20
        );

        return view('web.generation-requests.index', [
            'requests' => $requests,
        ]);
    }

    public function create(Request $request): View
    {
        $templateUuid = $request->query('template');
        $currentBalance = $this->tokenService->getBalance(auth()->id());

        return view('web.generation-requests.create', [
            'templateUuid' => $templateUuid,
            'currentBalance' => $currentBalance,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'template_id' => 'nullable|exists:templates,uuid',
            'type' => 'required|in:custom_image,custom_video,template_image,template_video',
            'orientation' => 'nullable|in:landscape,portrait,square',
            'description' => 'nullable|string|max:1000',
            'input_image' => 'nullable|image|mimes:jpeg,png,jpg|max:10240',
        ]);

        try {
            $data = [
                'type' => $validated['type'],
                'orientation' => $validated['orientation'] ?? null,
                'description' => $validated['description'] ?? null,
            ];

            // Template-based request
            if (! empty($validated['template_id'])) {
                $data['template_id'] = $validated['template_id'];
            }

            // Handle image upload
            if ($request->hasFile('input_image')) {
                $file = $request->file('input_image');
                $path = $file->store('generation-requests', 'public');
                $data['input_image_path'] = $path;
            }

            $generationRequest = $this->generationRequestService->createRequest(
                auth()->id(),
                $data
            );

            return redirect()->route('generation-requests.show', $generationRequest->uuid)
                ->with('success', __('Talebiniz başarıyla oluşturuldu.'));
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function show(string $uuid): View
    {
        $request = $this->generationRequestService->getRequestByUuid($uuid);

        if (! $request || $request->user_id !== auth()->id()) {
            abort(404);
        }

        return view('web.generation-requests.show', [
            'request' => $request,
        ]);
    }

    public function destroy(string $uuid): RedirectResponse
    {
        $request = $this->generationRequestService->getRequestByUuid($uuid);

        if (! $request || $request->user_id !== auth()->id()) {
            abort(404);
        }

        try {
            $this->generationRequestService->deleteRequest($request);

            return redirect()->route('generation-requests.index')
                ->with('success', __('Talep başarıyla iptal edildi ve tokenlarınız iade edildi.'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
