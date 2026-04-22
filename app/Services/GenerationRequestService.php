<?php

namespace App\Services;

use App\Models\GenerationRequest;
use App\Repositories\GenerationRequestRepository;
use Exception;
use Illuminate\Support\Facades\DB;

class GenerationRequestService
{
    public function __construct(
        private GenerationRequestRepository $requestRepository,
        private TokenService $tokenService,
        private TemplateService $templateService
    ) {}

    /**
     * Create a generation request with automatic token deduction
     *
     * @throws Exception
     */
    public function createRequest(int $userId, array $data): GenerationRequest
    {
        // Validate and calculate token cost
        $tokenCost = $this->calculateTokenCost($data);

        // Check if user has sufficient balance
        $currentBalance = $this->tokenService->getBalance($userId);
        if ($currentBalance < $tokenCost) {
            throw new Exception('Insufficient token balance. Required: '.$tokenCost.', Available: '.$currentBalance);
        }

        try {
            DB::beginTransaction();

            // Create the generation request
            $requestData = array_merge($data, [
                'user_id' => $userId,
                'token_cost' => $tokenCost,
                'status' => 'pending',
            ]);

            $request = $this->requestRepository->create($requestData);

            // Deduct tokens from user balance
            $this->tokenService->deductTokens(
                userId: $userId,
                amount: $tokenCost,
                type: 'generation_request',
                note: 'Token deduction for generation request',
                referenceId: (string) $request->id,
                referenceType: GenerationRequest::class
            );

            DB::commit();

            return $request;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Calculate token cost based on request type and template
     */
    private function calculateTokenCost(array $data): int
    {
        $type = $data['type'];

        // Template-based requests
        if (in_array($type, ['template_image', 'template_video'])) {
            if (empty($data['template_id'])) {
                throw new Exception('Template ID is required for template-based requests');
            }

            $template = $this->templateService->getTemplateByUuid($data['template_id']);
            if (! $template) {
                throw new Exception('Template not found');
            }

            if (! $template->is_active) {
                throw new Exception('Template is not active');
            }

            return $template->token_cost;
        }

        // Custom requests - default token costs
        // These can be configured in settings or hardcoded for now
        return match ($type) {
            'custom_image' => 10,
            'custom_video' => 50,
            default => throw new Exception('Invalid request type')
        };
    }

    public function getUserRequests(int $userId, int $perPage = 15)
    {
        return $this->requestRepository->getUserRequests($userId, $perPage);
    }

    public function getRequestByUuid(string $uuid): ?GenerationRequest
    {
        return $this->requestRepository->findByUuid($uuid);
    }

    public function updateRequest(GenerationRequest $request, array $data): bool
    {
        return $this->requestRepository->update($request, $data);
    }

    public function deleteRequest(GenerationRequest $request): bool
    {
        // Only allow deletion of pending or failed requests
        if (! in_array($request->status, ['pending', 'failed'])) {
            throw new Exception('Cannot delete request with status: '.$request->status);
        }

        // Refund tokens if request was pending
        if ($request->status === 'pending' && $request->token_cost > 0) {
            try {
                DB::beginTransaction();

                $this->tokenService->addTokens(
                    userId: $request->user_id,
                    amount: $request->token_cost,
                    type: 'refund',
                    note: 'Token refund for cancelled generation request',
                    referenceId: (string) $request->id,
                    referenceType: GenerationRequest::class
                );

                $deleted = $this->requestRepository->delete($request);

                DB::commit();

                return $deleted;
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        }

        return $this->requestRepository->delete($request);
    }

    public function markAsProcessing(GenerationRequest $request): bool
    {
        return $this->requestRepository->markAsProcessing($request);
    }

    public function markAsCompleted(GenerationRequest $request, string $outputUrl): bool
    {
        return $this->requestRepository->markAsCompleted($request, $outputUrl);
    }

    public function markAsFailed(GenerationRequest $request, string $reason): bool
    {
        // Refund tokens for failed requests
        if ($request->token_cost > 0) {
            try {
                DB::beginTransaction();

                $this->tokenService->addTokens(
                    userId: $request->user_id,
                    amount: $request->token_cost,
                    type: 'refund',
                    note: 'Token refund for failed generation request',
                    referenceId: (string) $request->id,
                    referenceType: GenerationRequest::class
                );

                $result = $this->requestRepository->markAsFailed($request, $reason);

                DB::commit();

                return $result;
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        }

        return $this->requestRepository->markAsFailed($request, $reason);
    }

    public function getPendingRequests()
    {
        return $this->requestRepository->getPendingRequests();
    }
}
