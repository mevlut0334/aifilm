<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\GenerationRequestService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GenerationRequestController extends Controller
{
    use ApiResponse;

    public function __construct(
        private GenerationRequestService $generationRequestService
    ) {}

    /**
     * Get user's generation requests
     */
    public function index(): JsonResponse
    {
        try {
            $requests = $this->generationRequestService->getUserRequests(
                auth()->id(),
                20
            );

            return $this->successResponse([
                'requests' => $requests->items(),
                'pagination' => [
                    'current_page' => $requests->currentPage(),
                    'last_page' => $requests->lastPage(),
                    'per_page' => $requests->perPage(),
                    'total' => $requests->total(),
                ],
            ], __('api.Requests retrieved successfully'));
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Create a new generation request
     */
    public function store(Request $request): JsonResponse
    {
        $type = $request->input('type');

        // Base validation rules
        $rules = [
            'template_id' => 'nullable|exists:templates,uuid',
            'type' => 'required|in:custom_image,custom_video,template_image,template_video',
            'orientation' => 'nullable|in:landscape,portrait,square',
            'description' => 'nullable|string|max:1000',
        ];

        // Template-based requests require input_image
        if (in_array($type, ['template_image', 'template_video'])) {
            $rules['input_image'] = 'required|image|mimes:jpeg,png,jpg|max:10240';
        } else {
            // Custom requests have input_image optional
            $rules['input_image'] = 'nullable|image|mimes:jpeg,png,jpg|max:10240';
        }

        $validated = $request->validate($rules);

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

            return $this->successResponse([
                'request' => [
                    'uuid' => $generationRequest->uuid,
                    'type' => $generationRequest->type,
                    'status' => $generationRequest->status,
                    'progress' => $generationRequest->progress,
                    'token_cost' => $generationRequest->token_cost,
                    'created_at' => $generationRequest->created_at->toIso8601String(),
                ],
            ], __('api.Request created successfully'), 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Get a specific generation request
     */
    public function show(string $uuid): JsonResponse
    {
        try {
            $request = $this->generationRequestService->getRequestByUuid($uuid);

            if (! $request || $request->user_id !== auth()->id()) {
                return $this->errorResponse(__('api.Request not found'), 404);
            }

            return $this->successResponse([
                'request' => [
                    'uuid' => $request->uuid,
                    'type' => $request->type,
                    'status' => $request->status,
                    'progress' => $request->progress,
                    'orientation' => $request->orientation,
                    'description' => $request->description,
                    'token_cost' => $request->token_cost,
                    'input_image_url' => $request->input_image_path
                        ? asset('storage/'.$request->input_image_path)
                        : null,
                    'output_url' => $request->output_url,
                    'failure_reason' => $request->failure_reason,
                    'template' => $request->template ? [
                        'uuid' => $request->template->uuid,
                        'title' => $request->template->title,
                        'token_cost' => $request->template->token_cost,
                    ] : null,
                    'created_at' => $request->created_at->toIso8601String(),
                    'updated_at' => $request->updated_at->toIso8601String(),
                ],
            ], __('api.Request retrieved successfully'));
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Cancel a generation request
     */
    public function destroy(string $uuid): JsonResponse
    {
        try {
            $request = $this->generationRequestService->getRequestByUuid($uuid);

            if (! $request || $request->user_id !== auth()->id()) {
                return $this->errorResponse(__('api.Request not found'), 404);
            }

            $this->generationRequestService->deleteRequest($request);

            return $this->successResponse(
                null,
                __('api.Request cancelled successfully and tokens refunded')
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}
