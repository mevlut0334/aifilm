<?php

namespace App\Repositories;

use App\Models\CustomVideoSegment;
use Illuminate\Support\Collection;

class CustomVideoSegmentRepository
{
    public function findById(int $id): ?CustomVideoSegment
    {
        return CustomVideoSegment::with('editRequests')->find($id);
    }

    public function getSegmentsByRequestId(int $requestId): Collection
    {
        return CustomVideoSegment::where('custom_video_request_id', $requestId)
            ->with('editRequests')
            ->orderBy('segment_number')
            ->get();
    }

    public function create(array $data): CustomVideoSegment
    {
        return CustomVideoSegment::create($data);
    }

    public function update(CustomVideoSegment $segment, array $data): bool
    {
        return $segment->update($data);
    }

    public function delete(CustomVideoSegment $segment): bool
    {
        return $segment->delete();
    }

    public function deleteByRequestId(int $requestId): int
    {
        return CustomVideoSegment::where('custom_video_request_id', $requestId)->delete();
    }

    public function updateProgress(CustomVideoSegment $segment, int $progress): bool
    {
        return $segment->update(['progress' => $progress]);
    }

    public function updateVideoUrl(CustomVideoSegment $segment, string $url): bool
    {
        return $segment->update([
            'video_url' => $url,
            'status' => 'completed',
            'progress' => 100,
        ]);
    }

    public function markAsProcessing(CustomVideoSegment $segment): bool
    {
        return $segment->update(['status' => 'processing']);
    }

    public function markAsFailed(CustomVideoSegment $segment, string $reason): bool
    {
        return $segment->update([
            'status' => 'failed',
            'failure_reason' => $reason,
        ]);
    }
}
