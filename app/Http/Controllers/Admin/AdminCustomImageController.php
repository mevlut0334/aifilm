<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminCustomImageController extends Controller
{
    public function index(): View
    {
        $images = CustomImage::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.custom-images.index', compact('images'));
    }

    public function show(string $uuid): View
    {
        $image = CustomImage::with('user')
            ->where('uuid', $uuid)
            ->firstOrFail();

        return view('admin.custom-images.show', compact('image'));
    }

    public function updateProgress(Request $request, string $uuid): RedirectResponse
    {
        $image = CustomImage::where('uuid', $uuid)->firstOrFail();

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
        $image = CustomImage::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'status' => 'required|in:pending,processing,completed,failed',
            'admin_image_url' => 'nullable|string',
            'failure_reason' => 'nullable|string',
        ]);

        $updateData = ['status' => $validated['status']];

        if ($validated['status'] === 'completed' && ! empty($validated['admin_image_url'])) {
            // Admin panelden girilen linki olduğu gibi sakla
            $updateData['admin_image_url'] = $validated['admin_image_url'];
            $updateData['progress'] = 100;
        }

        if ($validated['status'] === 'failed' && ! empty($validated['failure_reason'])) {
            $updateData['failure_reason'] = $validated['failure_reason'];
        }

        if ($validated['status'] === 'processing') {
            $updateData['progress'] = max($image->progress, 10);
        }

        $image->update($updateData);

        return back()->with('success', 'Durum güncellendi.');
    }

    public function destroy(string $uuid): RedirectResponse
    {
        $image = CustomImage::where('uuid', $uuid)->firstOrFail();
        $image->delete();

        return redirect()->route('admin.custom-images.index')->with('success', 'Talep başarıyla silindi.');
    }
}
