<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Repositories\CustomVideoRequestRepository;
use App\Repositories\CustomVideoSegmentRepository;
use App\Services\CustomVideoEditRequestService;
use App\Services\CustomVideoRequestService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomVideoRequestController extends Controller
{
    use ApiResponse;

    public function __construct(
        private CustomVideoRequestService $customVideoRequestService,
        private CustomVideoEditRequestService $customVideoEditRequestService,
        private CustomVideoRequestRepository $customVideoRequestRepository,
        private CustomVideoSegmentRepository $customVideoSegmentRepository
    ) {}

    /**
     * Get user's custom video requests
     */
    public function index(): JsonResponse
    {
        try {
            $requests = $this->customVideoRequestRepository->getUserRequests(
                auth()->id(),
                20
            );

            $data = $requests->map(function ($request) {
                return [
                    'uuid' => $request->uuid,
                    'prompt' => $request->prompt,
                    'input_image_path' => $request->input_image_path ? asset('storage/'.$request->input_image_path) : null,
                    'status' => $request->status,
                    'token_cost' => $request->token_cost,
                    'overall_progress' => $request->getOverallProgress(),
                    'segments_count' => $request->segments->count(),
                    'completed_segments' => $request->segments->where('status', 'completed')->count(),
                    'failure_reason' => $request->failure_reason,
                    'created_at' => $request->created_at->toIso8601String(),
                ];
            });

            return $this->successResponse([
                'requests' => $data,
                'pagination' => [
                    'current_page' => $requests->currentPage(),
                    'last_page' => $requests->lastPage(),
                    'per_page' => $requests->perPage(),
                    'total' => $requests->total(),
                ],
            ], __('custom_videos.Requests retrieved successfully'));
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Create a new custom video request
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prompt' => 'required|string',
            'input_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        try {
            $data = [
                'prompt' => $validated['prompt'],
            ];

            // Handle image upload
            if ($request->hasFile('input_image')) {
                $file = $request->file('input_image');
                $path = $file->store('custom-video-requests', 'public');
                $data['input_image_path'] = $path;
            }

            $videoRequest = $this->customVideoRequestService->createRequest(
                auth()->id(),
                $data
            );

            return $this->successResponse([
                'request' => [
                    'uuid' => $videoRequest->uuid,
                    'prompt' => $videoRequest->prompt,
                    'input_image_path' => $videoRequest->input_image_path ? asset('storage/'.$videoRequest->input_image_path) : null,
                    'status' => $videoRequest->status,
                    'token_cost' => $videoRequest->token_cost,
                    'created_at' => $videoRequest->created_at->toIso8601String(),
                ],
            ], __('custom_videos.Request created successfully'), 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Get a specific custom video request with segments
     */
    public function show(string $uuid): JsonResponse
    {
        try {
            $request = $this->customVideoRequestRepository->findByUuid($uuid);

            if (! $request || $request->user_id !== auth()->id()) {
                return $this->errorResponse(__('custom_videos.Request not found'), 404);
            }

            $segments = $request->segments->map(function ($segment) {
                return [
                    'id' => $segment->id,
                    'segment_number' => $segment->segment_number,
                    'video_url' => $segment->video_url ? asset('storage/'.$segment->video_url) : null,
                    'status' => $segment->status,
                    'progress' => $segment->progress,
                    'failure_reason' => $segment->failure_reason,
                    'has_pending_edit' => $segment->editRequests()->pending()->exists(),
                    'latest_edit_request' => $segment->editRequests()->latest()->first() ? [
                        'id' => $segment->editRequests()->latest()->first()->id,
                        'edit_prompt' => $segment->editRequests()->latest()->first()->edit_prompt,
                        'status' => $segment->editRequests()->latest()->first()->status,
                        'admin_note' => $segment->editRequests()->latest()->first()->admin_note,
                        'created_at' => $segment->editRequests()->latest()->first()->created_at->toIso8601String(),
                    ] : null,
                ];
            });

            return $this->successResponse([
                'request' => [
                    'uuid' => $request->uuid,
                    'prompt' => $request->prompt,
                    'input_image_path' => $request->input_image_path ? asset('storage/'.$request->input_image_path) : null,
                    'status' => $request->status,
                    'token_cost' => $request->token_cost,
                    'overall_progress' => $request->getOverallProgress(),
                    'failure_reason' => $request->failure_reason,
                    'segments' => $segments,
                    'created_at' => $request->created_at->toIso8601String(),
                ],
            ], __('custom_videos.Request retrieved successfully'));
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Delete a custom video request
     */
    public function destroy(string $uuid): JsonResponse
    {
        try {
            $request = $this->customVideoRequestRepository->findByUuid($uuid);

            if (! $request || $request->user_id !== auth()->id()) {
                return $this->errorResponse(__('custom_videos.Request not found'), 404);
            }

            if ($request->status === 'completed') {
                return $this->errorResponse(__('custom_videos.Cannot delete completed request'), 400);
            }

            $this->customVideoRequestService->deleteRequest($request);

            return $this->successResponse([], __('custom_videos.Request deleted successfully'));
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Request edit for a specific segment
     */
    public function requestSegmentEdit(Request $request, string $uuid, int $segmentId): JsonResponse
    {
        $validated = $request->validate([
            'edit_prompt' => 'required|string',
        ]);

        try {
            $videoRequest = $this->customVideoRequestRepository->findByUuid($uuid);

            if (! $videoRequest || $videoRequest->user_id !== auth()->id()) {
                return $this->errorResponse(__('custom_videos.Request not found'), 404);
            }

            $segment = $this->customVideoSegmentRepository->findById($segmentId);

            if (! $segment || $segment->custom_video_request_id !== $videoRequest->id) {
                return $this->errorResponse(__('custom_videos.Segment not found'), 404);
            }

            if ($segment->status !== 'completed') {
                return $this->errorResponse(__('custom_videos.Can only request edits for completed segments'), 400);
            }

            // Check if there's already a pending edit request
            if ($segment->editRequests()->pending()->exists()) {
                return $this->errorResponse(__('custom_videos.Segment already has a pending edit request'), 400);
            }

            $editRequest = $this->customVideoEditRequestService->createEditRequest(
                $segment,
                $validated['edit_prompt']
            );

            return $this->successResponse([
                'edit_request' => [
                    'id' => $editRequest->id,
                    'segment_id' => $editRequest->custom_video_segment_id,
                    'edit_prompt' => $editRequest->edit_prompt,
                    'status' => $editRequest->status,
                    'created_at' => $editRequest->created_at->toIso8601String(),
                ],
            ], __('custom_videos.Edit request submitted successfully'), 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
