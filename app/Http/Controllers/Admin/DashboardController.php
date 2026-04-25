<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomVideoRequest;
use App\Models\CustomVideoEditRequest;
use App\Models\GenerationRequest;
use App\Models\CustomImage;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // 1. Custom Video Requests - pending status
        $pendingCustomVideos = CustomVideoRequest::with(['user', 'segments'])
            ->where('status', 'pending')
            ->latest()
            ->get()
            ->map(function ($request) {
                return [
                    'type' => 'custom_video',
                    'uuid' => $request->uuid,
                    'user' => $request->user,
                    'description' => $request->prompt,
                    'status' => $request->status,
                    'created_at' => $request->created_at,
                    'url' => route('admin.custom-videos.show', $request->uuid),
                    'pending_segments' => $request->segments->where('status', 'pending')->count(),
                ];
            });

        // 2. Custom Video Requests - completed but has pending segments
        $completedWithPendingSegments = CustomVideoRequest::with(['user', 'segments'])
            ->where('status', 'completed')
            ->whereHas('segments', function ($query) {
                $query->where('status', 'pending');
            })
            ->latest()
            ->get()
            ->map(function ($request) {
                return [
                    'type' => 'custom_video',
                    'uuid' => $request->uuid,
                    'user' => $request->user,
                    'description' => $request->prompt,
                    'status' => 'completed_with_pending',
                    'created_at' => $request->created_at,
                    'url' => route('admin.custom-videos.show', $request->uuid),
                    'pending_segments' => $request->segments->where('status', 'pending')->count(),
                ];
            });

        // 3. Custom Video Requests - has pending edit requests
        $videosWithPendingEdits = CustomVideoRequest::with(['user', 'segments.editRequests'])
            ->whereHas('segments.editRequests', function ($query) {
                $query->where('status', 'pending');
            })
            ->latest()
            ->get()
            ->map(function ($request) {
                $pendingEdits = 0;
                foreach ($request->segments as $segment) {
                    $pendingEdits += $segment->editRequests->where('status', 'pending')->count();
                }
                return [
                    'type' => 'custom_video_edit',
                    'uuid' => $request->uuid,
                    'user' => $request->user,
                    'description' => $request->prompt,
                    'status' => 'pending_edits',
                    'created_at' => $request->created_at,
                    'url' => route('admin.custom-videos.show', $request->uuid),
                    'pending_edits' => $pendingEdits,
                ];
            });

        // 4. Template-based Generation Requests - pending status
        $pendingGenerationRequests = GenerationRequest::with(['user', 'template'])
            ->where('status', 'pending')
            ->latest()
            ->get()
            ->map(function ($request) {
                return [
                    'type' => 'generation_request',
                    'uuid' => $request->uuid,
                    'user' => $request->user,
                    'description' => $request->description ?? ($request->template ? $request->template->name : 'Template Request'),
                    'status' => $request->status,
                    'created_at' => $request->created_at,
                    'url' => route('admin.generation-requests.show', $request->uuid),
                    'template' => $request->template ? $request->template->name : null,
                ];
            });

        // 5. Custom Image Requests - pending status
        $pendingCustomImages = CustomImage::with(['user'])
            ->where('status', 'pending')
            ->latest()
            ->get()
            ->map(function ($request) {
                return [
                    'type' => 'custom_image',
                    'uuid' => $request->uuid,
                    'user' => $request->user,
                    'description' => $request->prompt,
                    'status' => $request->status,
                    'created_at' => $request->created_at,
                    'url' => route('admin.custom-images.show', $request->uuid),
                ];
            });

        // Merge all collections
        $allPendingRequests = $pendingCustomVideos
            ->concat($completedWithPendingSegments)
            ->concat($videosWithPendingEdits)
            ->concat($pendingGenerationRequests)
            ->concat($pendingCustomImages)
            ->sortByDesc('created_at')
            ->values();

        // Count total pending items
        $pendingCount = $allPendingRequests->count();

        return view('admin.dashboard', [
            'pendingRequests' => $allPendingRequests,
            'pendingCount' => $pendingCount,
        ]);
    }
}
