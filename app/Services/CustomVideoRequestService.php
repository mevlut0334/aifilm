<?php

namespace App\Services;

use App\Models\CustomVideoRequest;
use App\Repositories\CustomVideoRequestRepository;
use App\Repositories\CustomVideoSegmentRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CustomVideoRequestService
{
    public function __construct(
        private CustomVideoRequestRepository $customVideoRequestRepository,
        private CustomVideoSegmentRepository $customVideoSegmentRepository,
        private TokenService $tokenService
    ) {}

    public function createRequest(int $userId, array $data): CustomVideoRequest
    {
        try {
            DB::beginTransaction();

            $requestData = [
                'user_id' => $userId,
                'prompt' => $data['prompt'],
                'input_image_path' => $data['input_image_path'] ?? null,
                'status' => 'pending',
                'token_cost' => 0, // Admin will set token cost manually
            ];

            $request = $this->customVideoRequestRepository->create($requestData);

            DB::commit();

            return $request;
        } catch (Exception $e) {
            DB::rollBack();

            // Delete uploaded file if exists
            if (isset($data['input_image_path'])) {
                Storage::disk('public')->delete($data['input_image_path']);
            }

            throw $e;
        }
    }

    public function getUserRequest(string $uuid, int $userId): ?CustomVideoRequest
    {
        $request = $this->customVideoRequestRepository->findByUuid($uuid);

        if (! $request || $request->user_id !== $userId) {
            return null;
        }

        return $request;
    }

    public function deleteRequest(CustomVideoRequest $request): bool
    {
        try {
            DB::beginTransaction();

            // Delete input image if exists
            if ($request->input_image_path) {
                Storage::disk('public')->delete($request->input_image_path);
            }

            // Delete segments and their videos
            foreach ($request->segments as $segment) {
                if ($segment->video_url) {
                    Storage::disk('public')->delete($segment->video_url);
                }
            }

            // Refund tokens if they were deducted
            if ($request->token_deducted && $request->token_cost > 0 && in_array($request->status, ['pending', 'processing', 'failed'])) {
                $this->tokenService->addTokens(
                    $request->user_id,
                    $request->token_cost,
                    'refund',
                    'Custom video request cancelled',
                    $request->id,
                    CustomVideoRequest::class
                );
            }

            $this->customVideoRequestRepository->delete($request);

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateStatus(CustomVideoRequest $request, string $status, ?string $failureReason = null): bool
    {
        $data = ['status' => $status];

        if ($status === 'failed' && $failureReason) {
            $data['failure_reason'] = $failureReason;
        }

        return $this->customVideoRequestRepository->update($request, $data);
    }

    public function setTokenCost(CustomVideoRequest $request, int $tokenCost): bool
    {
        try {
            DB::beginTransaction();

            // Check if tokens already deducted
            if ($request->token_deducted) {
                throw new Exception('Tokens already deducted for this request');
            }

            // Deduct tokens from user (allow negative balance for admin-initiated costs)
            $this->tokenService->deductTokens(
                $request->user_id,
                $tokenCost,
                'custom_video_request',
                'Custom video request',
                $request->id,
                CustomVideoRequest::class,
                true // Allow negative balance
            );

            // Update token cost and mark as deducted
            $this->customVideoRequestRepository->update($request, [
                'token_cost' => $tokenCost,
                'token_deducted' => true,
            ]);

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
