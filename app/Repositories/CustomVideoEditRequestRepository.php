<?php

namespace App\Repositories;

use App\Models\CustomVideoEditRequest;
use Illuminate\Support\Collection;

class CustomVideoEditRequestRepository
{
    public function findById(int $id): ?CustomVideoEditRequest
    {
        return CustomVideoEditRequest::with('segment.customVideoRequest')->find($id);
    }

    public function getBySegmentId(int $segmentId): Collection
    {
        return CustomVideoEditRequest::where('custom_video_segment_id', $segmentId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getPendingEditRequests(int $perPage = 20)
    {
        return CustomVideoEditRequest::with('segment.customVideoRequest.user')
            ->pending()
            ->orderBy('created_at', 'asc')
            ->paginate($perPage);
    }

    public function create(array $data): CustomVideoEditRequest
    {
        return CustomVideoEditRequest::create($data);
    }

    public function update(CustomVideoEditRequest $editRequest, array $data): bool
    {
        return $editRequest->update($data);
    }

    public function markAsProcessing(CustomVideoEditRequest $editRequest): bool
    {
        return $editRequest->update(['status' => 'processing']);
    }

    public function markAsCompleted(CustomVideoEditRequest $editRequest, ?string $adminNote = null): bool
    {
        return $editRequest->update([
            'status' => 'completed',
            'admin_note' => $adminNote,
        ]);
    }

    public function markAsRejected(CustomVideoEditRequest $editRequest, string $adminNote): bool
    {
        return $editRequest->update([
            'status' => 'rejected',
            'admin_note' => $adminNote,
        ]);
    }
}
