<?php

namespace App\Repositories;

use App\Models\GenerationRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GenerationRequestRepository
{
    public function findByUuid(string $uuid): ?GenerationRequest
    {
        return GenerationRequest::where('uuid', $uuid)->first();
    }

    public function getUserRequests(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return GenerationRequest::byUser($userId)
            ->with('template')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getPendingRequests(int $perPage = 20): LengthAwarePaginator
    {
        return GenerationRequest::with(['user', 'template'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function create(array $data): GenerationRequest
    {
        return GenerationRequest::create($data);
    }

    public function update(GenerationRequest $request, array $data): bool
    {
        return $request->update($data);
    }

    public function delete(GenerationRequest $request): bool
    {
        return $request->delete();
    }

    public function markAsProcessing(GenerationRequest $request): bool
    {
        return $request->update(['status' => 'processing']);
    }

    public function markAsCompleted(GenerationRequest $request, string $outputUrl): bool
    {
        return $request->update([
            'status' => 'completed',
            'output_url' => $outputUrl,
        ]);
    }

    public function markAsFailed(GenerationRequest $request, string $reason): bool
    {
        return $request->update([
            'status' => 'failed',
            'failure_reason' => $reason,
        ]);
    }
}
