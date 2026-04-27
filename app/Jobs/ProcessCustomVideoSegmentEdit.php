<?php

namespace App\Jobs;

use App\Models\CustomVideoEditRequest;
use App\Services\CustomVideoEditRequestService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessCustomVideoSegmentEdit implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Kaç kez yeniden denensin
     */
    public int $tries = 3;

    /**
     * Maksimum çalışma süresi (saniye)
     */
    public int $timeout = 300;

    public function __construct(
        public readonly CustomVideoEditRequest $editRequest
    ) {}

    /**
     * Token bu Job'da KESİLMEZ.
     * Token admin tarafından manuel olarak deductEditCost() ile kesilir.
     * Bu Job sadece edit talebinin alındığını loglar ve harici işlemleri arka planda çalıştırır.
     */
    public function handle(CustomVideoEditRequestService $service): void
    {
        Log::info('ProcessCustomVideoSegmentEdit started', [
            'edit_request_id' => $this->editRequest->id,
            'segment_id' => $this->editRequest->custom_video_segment_id,
        ]);

        try {
            // TODO: Buraya harici işlem gelecek (admin bildirim, webhook vb.)
            // Token kesme işlemi admin panelinden deductEditCost() ile yapılır
            // Status güncellemesi de admin panelinden yapılır

            Log::info('ProcessCustomVideoSegmentEdit queued successfully', [
                'edit_request_id' => $this->editRequest->id,
            ]);

        } catch (\Exception $e) {
            Log::error('ProcessCustomVideoSegmentEdit failed', [
                'edit_request_id' => $this->editRequest->id,
                'error' => $e->getMessage(),
            ]);

            $service->rejectEditRequest($this->editRequest, $e->getMessage());

            $this->fail($e);
        }
    }

    /**
     * Tüm retry'lar tükenince çağrılır
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessCustomVideoSegmentEdit permanently failed', [
            'edit_request_id' => $this->editRequest->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
