<?php

namespace App\Services;

use App\Models\CustomVideoEditRequest;
use App\Models\CustomVideoSegment;
use App\Repositories\CustomVideoEditRequestRepository;
use Exception;
use Illuminate\Support\Facades\DB;

class CustomVideoEditRequestService
{
    public function __construct(
        private CustomVideoEditRequestRepository $customVideoEditRequestRepository,
        private TokenService $tokenService
    ) {}

    public function createEditRequest(CustomVideoSegment $segment, string $editPrompt): CustomVideoEditRequest
    {
        try {
            DB::beginTransaction();

            $editRequest = $this->customVideoEditRequestRepository->create([
                'custom_video_segment_id' => $segment->id,
                'edit_prompt' => $editPrompt,
                'status' => 'pending',
            ]);

            DB::commit();

            return $editRequest;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function processEditRequest(CustomVideoEditRequest $editRequest): bool
    {
        return $this->customVideoEditRequestRepository->markAsProcessing($editRequest);
    }

    public function completeEditRequest(CustomVideoEditRequest $editRequest, ?string $adminNote = null): bool
    {
        return $this->customVideoEditRequestRepository->markAsCompleted($editRequest, $adminNote);
    }

    public function rejectEditRequest(CustomVideoEditRequest $editRequest, string $adminNote): bool
    {
        return $this->customVideoEditRequestRepository->markAsRejected($editRequest, $adminNote);
    }

    public function deductEditCost(CustomVideoEditRequest $editRequest, int $userId, int $editCost): bool
    {
        try {
            DB::beginTransaction();

            // Check if tokens already deducted
            if ($editRequest->token_deducted) {
                throw new Exception('Tokens already deducted for this edit request');
            }

            // Deduct tokens from user (allow negative balance for admin-initiated edit costs)
            $this->tokenService->deductTokens(
                $userId,
                $editCost,
                'custom_video_edit',
                'Custom video segment edit',
                $editRequest->id,
                CustomVideoEditRequest::class,
                true // Allow negative balance
            );

            // Update edit request
            $editRequest->update([
                'edit_cost' => $editCost,
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
