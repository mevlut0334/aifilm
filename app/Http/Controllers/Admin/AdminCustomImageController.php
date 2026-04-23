<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
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
            try {
                // Check if URL or local path
                if (filter_var($validated['admin_image_url'], FILTER_VALIDATE_URL)) {
                    // Download from URL and store locally
                    $response = Http::timeout(60)->get($validated['admin_image_url']);

                    if ($response->successful()) {
                        // Get file extension from URL or content type
                        $extension = pathinfo(parse_url($validated['admin_image_url'], PHP_URL_PATH), PATHINFO_EXTENSION);
                        if (empty($extension)) {
                            $contentType = $response->header('Content-Type');
                            $extension = match ($contentType) {
                                'image/jpeg' => 'jpg',
                                'image/png' => 'png',
                                'image/gif' => 'gif',
                                'image/webp' => 'webp',
                                default => 'jpg'
                            };
                        }

                        $filename = 'custom-images/output/'.$image->uuid.'_'.time().'.'.$extension;
                        Storage::disk('public')->put($filename, $response->body());
                        $updateData['admin_image_url'] = asset('storage/'.$filename);
                    } else {
                        // If download fails, keep the URL as-is
                        $updateData['admin_image_url'] = $validated['admin_image_url'];
                    }
                } else {
                    // Local path or direct URL, use as-is
                    $updateData['admin_image_url'] = $validated['admin_image_url'];
                }
            } catch (\Exception $e) {
                // If any error occurs, keep the URL as-is
                $updateData['admin_image_url'] = $validated['admin_image_url'];
            }

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
}
