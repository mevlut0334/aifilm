<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomImage;
use App\Models\GenerationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class AdminCustomImageController extends Controller
{
    public function index(): View
    {
        // CustomImage modelinden gelen eski custom image talepleri
        $customImages = CustomImage::with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        // GenerationRequest modelinden gelen custom_image tipindeki talepler
        $generationRequests = GenerationRequest::with('user')
            ->where('type', 'custom_image')
            ->orderBy('created_at', 'desc')
            ->get();

        // İkisini birleştir ve tarihe göre sırala
        $allImages = $customImages->concat($generationRequests)
            ->sortByDesc('created_at');

        // Manuel pagination
        $page = request()->get('page', 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $images = new LengthAwarePaginator(
            $allImages->slice($offset, $perPage)->values(),
            $allImages->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('admin.custom-images.index', compact('images'));
    }

    public function show(string $uuid): View
    {
        // Önce CustomImage modelinde ara
        $image = CustomImage::with('user')
            ->where('uuid', $uuid)
            ->first();

        // Bulamazsa GenerationRequest modelinde ara (custom_image tipinde)
        if (! $image) {
            $image = GenerationRequest::with('user')
                ->where('uuid', $uuid)
                ->where('type', 'custom_image')
                ->firstOrFail();
        }

        return view('admin.custom-images.show', compact('image'));
    }

    public function updateProgress(Request $request, string $uuid): RedirectResponse
    {
        // Önce CustomImage modelinde ara
        $image = CustomImage::where('uuid', $uuid)->first();

        // Bulamazsa GenerationRequest modelinde ara
        if (! $image) {
            $image = GenerationRequest::where('uuid', $uuid)
                ->where('type', 'custom_image')
                ->firstOrFail();
        }

        $validated = $request->validate([
            'progress' => 'required|integer|min:0|max:100',
        ]);

        $image->update([
            'progress' => $validated['progress'],
        ]);

        return back()->with('success', 'Progress güncellendi.');
    }

    public function updateStatus(Request $request, string $uuid): RedirectResponse
    {
        // Önce CustomImage modelinde ara
        $image = CustomImage::where('uuid', $uuid)->first();

        // Bulamazsa GenerationRequest modelinde ara
        if (! $image) {
            $image = GenerationRequest::where('uuid', $uuid)
                ->where('type', 'custom_image')
                ->firstOrFail();
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,processing,completed,failed',
            'admin_image_url' => 'nullable|string',
            'failure_reason' => 'nullable|string',
        ]);

        $updateData = ['status' => $validated['status']];

        if ($validated['status'] === 'completed' && ! empty($validated['admin_image_url'])) {
            // Admin panelden girilen linki olduğu gibi sakla
            // GenerationRequest için output_url kullan
            if ($image instanceof GenerationRequest) {
                $updateData['output_url'] = $validated['admin_image_url'];
            } else {
                $updateData['admin_image_url'] = $validated['admin_image_url'];
            }
            $updateData['progress'] = 100;
        }

        if ($validated['status'] === 'failed' && ! empty($validated['failure_reason'])) {
            $updateData['failure_reason'] = $validated['failure_reason'];
        }

        if ($validated['status'] === 'processing') {
            $updateData['progress'] = max($image->progress ?? 0, 10);
        }

        $image->update($updateData);

        return back()->with('success', 'Durum güncellendi.');
    }

    public function destroy(string $uuid): RedirectResponse
    {
        // Önce CustomImage modelinde ara
        $image = CustomImage::where('uuid', $uuid)->first();

        // Bulamazsa GenerationRequest modelinde ara
        if (! $image) {
            $image = GenerationRequest::where('uuid', $uuid)
                ->where('type', 'custom_image')
                ->firstOrFail();
        }

        $image->delete();

        return redirect()->route('admin.custom-images.index')->with('success', 'Talep başarıyla silindi.');
    }
}
