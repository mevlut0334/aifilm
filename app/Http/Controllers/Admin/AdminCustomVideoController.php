<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomVideoEditRequest;
use App\Models\CustomVideoRequest;
use App\Models\CustomVideoSegment;
use App\Repositories\CustomVideoEditRequestRepository;
use App\Services\CustomVideoEditRequestService;
use App\Services\CustomVideoRequestService;
use App\Services\CustomVideoSegmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminCustomVideoController extends Controller
{
    public function __construct(
        private CustomVideoRequestService $customVideoRequestService,
        private CustomVideoSegmentService $customVideoSegmentService,
        private CustomVideoEditRequestService $customVideoEditRequestService,
        private CustomVideoEditRequestRepository $customVideoEditRequestRepository
    ) {}

    /**
     * List all custom video requests
     */
    public function index(): View
    {
        $requests = CustomVideoRequest::with(['user', 'segments'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.custom-videos.index', compact('requests'));
    }

    /**
     * Show specific custom video request
     */
    public function show(string $uuid): View
    {
        $request = CustomVideoRequest::with(['user', 'segments.editRequests'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        return view('admin.custom-videos.show', compact('request'));
    }

    /**
     * Set token cost and deduct from user
     */
    public function setTokenCost(Request $validateRequest, string $uuid): RedirectResponse
    {
        $request = CustomVideoRequest::where('uuid', $uuid)->firstOrFail();

        $validated = $validateRequest->validate([
            'token_cost' => 'required|integer|min:1',
        ]);

        try {
            $this->customVideoRequestService->setTokenCost($request, $validated['token_cost']);

            return back()->with('success', __('admin.Token cost set and deducted successfully'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Create segments for the request
     */
    public function createSegments(Request $validateRequest, string $uuid): RedirectResponse
    {
        $request = CustomVideoRequest::where('uuid', $uuid)->firstOrFail();

        $validated = $validateRequest->validate([
            'number_of_segments' => 'required|integer|min:1|max:20',
        ]);

        try {
            $this->customVideoSegmentService->createSegments($request, $validated['number_of_segments']);

            return back()->with('success', __('admin.Segments created successfully'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Add single segment
     */
    public function addSegment(string $uuid): RedirectResponse
    {
        $request = CustomVideoRequest::where('uuid', $uuid)->firstOrFail();

        try {
            // Get the next segment number
            $lastSegment = $request->segments()->orderBy('segment_number', 'desc')->first();
            $nextSegmentNumber = $lastSegment ? $lastSegment->segment_number + 1 : 1;

            // Create new segment
            $request->segments()->create([
                'segment_number' => $nextSegmentNumber,
                'status' => 'pending',
                'progress' => 0,
            ]);

            return back()->with('success', __('admin.Segment added successfully'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update segment progress
     */
    public function updateSegmentProgress(Request $validateRequest, int $segmentId): RedirectResponse
    {
        $segment = CustomVideoSegment::findOrFail($segmentId);

        $validated = $validateRequest->validate([
            'progress' => 'required|integer|min:0|max:100',
        ]);

        try {
            $this->customVideoSegmentService->updateSegmentProgress($segment, $validated['progress']);

            return back()->with('success', __('admin.Progress updated successfully'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update segment status
     */
    public function updateSegmentStatus(Request $validateRequest, int $segmentId): RedirectResponse
    {
        $segment = CustomVideoSegment::findOrFail($segmentId);

        $validated = $validateRequest->validate([
            'status' => 'required|in:pending,processing,completed,failed',
        ]);

        try {
            $segment->update(['status' => $validated['status']]);

            return back()->with('success', __('admin.Status updated successfully'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update segment video URL
     */
    public function updateSegmentVideoUrl(Request $validateRequest, int $segmentId): RedirectResponse
    {
        $segment = CustomVideoSegment::findOrFail($segmentId);

        $validated = $validateRequest->validate([
            'video_url' => 'required|string',
        ]);

        try {
            $videoUrl = $validated['video_url'];

            // Check if URL and try to download
            if (filter_var($videoUrl, FILTER_VALIDATE_URL)) {
                try {
                    $response = Http::timeout(120)->get($videoUrl);

                    if ($response->successful()) {
                        // Get file extension
                        $extension = pathinfo(parse_url($videoUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
                        if (empty($extension)) {
                            $extension = 'mp4'; // default
                        }

                        $filename = 'custom-videos/segments/'.$segment->id.'_'.time().'.'.$extension;
                        Storage::disk('public')->put($filename, $response->body());
                        $videoUrl = $filename; // Store local path
                    }
                    // If download fails, keep original URL
                } catch (\Exception $e) {
                    // If any error, keep original URL
                }
            }

            // Store URL or local path
            $this->customVideoSegmentService->updateSegmentVideoUrl($segment, $videoUrl);

            return back()->with('success', __('admin.Video URL updated successfully'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Mark segment as failed
     */
    public function markSegmentAsFailed(Request $validateRequest, int $segmentId): RedirectResponse
    {
        $segment = CustomVideoSegment::findOrFail($segmentId);

        $validated = $validateRequest->validate([
            'failure_reason' => 'required|string',
        ]);

        try {
            $this->customVideoSegmentService->markSegmentAsFailed($segment, $validated['failure_reason']);

            return back()->with('success', __('admin.Segment marked as failed'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update request status
     */
    public function updateRequestStatus(Request $validateRequest, string $uuid): RedirectResponse
    {
        $request = CustomVideoRequest::where('uuid', $uuid)->firstOrFail();

        $validated = $validateRequest->validate([
            'status' => 'required|in:pending,processing,completed,failed',
            'failure_reason' => 'nullable|string',
        ]);

        try {
            $this->customVideoRequestService->updateStatus(
                $request,
                $validated['status'],
                $validated['failure_reason'] ?? null
            );

            return back()->with('success', __('admin.Status updated successfully'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * List all edit requests
     */
    public function editRequests(): View
    {
        $editRequests = $this->customVideoEditRequestRepository->getPendingEditRequests(20);

        return view('admin.custom-videos.edit-requests', compact('editRequests'));
    }

    /**
     * Update edit request status
     */
    public function updateEditRequestStatus(Request $validateRequest, int $editRequestId): RedirectResponse
    {
        $editRequest = CustomVideoEditRequest::with('segment.customVideoRequest.user')->findOrFail($editRequestId);

        $validated = $validateRequest->validate([
            'status' => 'required|in:processing,completed,rejected',
            'edit_cost' => 'nullable|integer|min:0',
            'admin_note' => 'nullable|string',
        ]);

        try {
            if ($validated['status'] === 'processing') {
                $this->customVideoEditRequestService->processEditRequest($editRequest);
            } elseif ($validated['status'] === 'completed') {
                // Deduct tokens if edit cost is set and not already deducted
                if (isset($validated['edit_cost']) && $validated['edit_cost'] > 0 && ! $editRequest->token_deducted) {
                    $userId = $editRequest->segment->customVideoRequest->user_id;

                    $this->customVideoEditRequestService->deductEditCost(
                        $editRequest,
                        $userId,
                        $validated['edit_cost']
                    );
                }

                $this->customVideoEditRequestService->completeEditRequest($editRequest, $validated['admin_note'] ?? null);
            } elseif ($validated['status'] === 'rejected') {
                if (empty($validated['admin_note'])) {
                    return back()->with('error', __('admin.Admin note is required for rejection'));
                }
                $this->customVideoEditRequestService->rejectEditRequest($editRequest, $validated['admin_note']);
            }

            return back()->with('success', __('admin.Edit request updated successfully'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
