<?php

namespace App\Repositories;

use App\Models\CustomVideoRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomVideoRequestRepository
{
    public function findByUuid(string $uuid): ?CustomVideoRequest
    {
        return CustomVideoRequest::where('uuid', $uuid)->with('segments.editRequests')->first();
    }

    public function getUserRequests(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return CustomVideoRequest::byUser($userId)
            ->with('segments')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getAllRequests(int $perPage = 20): LengthAwarePaginator
    {
        return CustomVideoRequest::with(['user', 'segments'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function create(array $data): CustomVideoRequest
    {
        return CustomVideoRequest::create($data);
    }

    public function update(CustomVideoRequest $request, array $data): bool
    {
        return $request->update($data);
    }

    public function delete(CustomVideoRequest $request): bool
    {
        return $request->delete();
    }

    public function markAsProcessing(CustomVideoRequest $request): bool
    {
        return $request->update(['status' => 'processing']);
    }

    public function markAsCompleted(CustomVideoRequest $request): bool
    {
        return $request->update(['status' => 'completed']);
    }

    public function markAsFailed(CustomVideoRequest $request, string $reason): bool
    {
        return $request->update([
            'status' => 'failed',
            'failure_reason' => $reason,
        ]);
    }
}
