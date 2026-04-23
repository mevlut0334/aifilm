<?php

namespace App\Services;

use App\Models\CustomVideoRequest;
use App\Models\CustomVideoSegment;
use App\Repositories\CustomVideoRequestRepository;
use App\Repositories\CustomVideoSegmentRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CustomVideoSegmentService
{
    public function __construct(
        private CustomVideoSegmentRepository $customVideoSegmentRepository,
        private CustomVideoRequestRepository $customVideoRequestRepository
    ) {}

    public function createSegments(CustomVideoRequest $request, int $numberOfSegments): bool
    {
        try {
            DB::beginTransaction();

            // Delete existing segments if any
            $this->customVideoSegmentRepository->deleteByRequestId($request->id);

            // Create new segments
            for ($i = 1; $i <= $numberOfSegments; $i++) {
                $this->customVideoSegmentRepository->create([
                    'custom_video_request_id' => $request->id,
                    'segment_number' => $i,
                    'status' => 'pending',
                    'progress' => 0,
                ]);
            }

            // Update request status to processing
            $this->customVideoRequestRepository->update($request, ['status' => 'processing']);

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateSegmentProgress(CustomVideoSegment $segment, int $progress): bool
    {
        return $this->customVideoSegmentRepository->updateProgress($segment, $progress);
    }

    public function updateSegmentVideoUrl(CustomVideoSegment $segment, string $videoUrl): bool
    {
        try {
            DB::beginTransaction();

            // Delete old video if exists
            if ($segment->video_url) {
                Storage::disk('public')->delete($segment->video_url);
            }

            // Update segment with new video URL
            $this->customVideoSegmentRepository->updateVideoUrl($segment, $videoUrl);

            // Check if all segments are completed
            $request = $segment->customVideoRequest;
            $allSegmentsCompleted = $request->segments()
                ->where('status', '!=', 'completed')
                ->count() === 0;

            if ($allSegmentsCompleted) {
                $this->customVideoRequestRepository->update($request, ['status' => 'completed']);
            }

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function markSegmentAsFailed(CustomVideoSegment $segment, string $reason): bool
    {
        return $this->customVideoSegmentRepository->markAsFailed($segment, $reason);
    }

    public function markSegmentAsProcessing(CustomVideoSegment $segment): bool
    {
        return $this->customVideoSegmentRepository->markAsProcessing($segment);
    }

    public function deleteSegment(CustomVideoSegment $segment): bool
    {
        try {
            DB::beginTransaction();

            // Delete video file if exists
            if ($segment->video_url) {
                Storage::disk('public')->delete($segment->video_url);
            }

            $this->customVideoSegmentRepository->delete($segment);

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
