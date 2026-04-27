<?php

namespace App\Jobs;

use App\Models\CustomVideoRequest;
use App\Services\CustomVideoRequestService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessCustomVideoRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Kaç kez yeniden denensin
     */
    public int $tries = 3;

    /**
     * Maksimum çalışma süresi (saniye)
     * Video işleme uzun sürebilir
     */
    public int $timeout = 600;

    public function __construct(
        public readonly CustomVideoRequest $request
    ) {}

    /**
     * Token bu Job'da KESİLMEZ.
     * Token admin tarafından manuel olarak setTokenCost() ile kesilir.
     * Bu Job sadece talebin alındığını loglar ve harici işlemleri arka planda çalıştırır.
     */
    public function handle(CustomVideoRequestService $service): void
    {
        Log::info('ProcessCustomVideoRequest started', [
            'request_id' => $this->request->id,
            'format' => $this->request->format,
        ]);

        try {
            // TODO: Buraya harici işlem gelecek (admin bildirim, webhook vb.)
            // Token kesme işlemi admin panelinden setTokenCost() ile yapılır
            // Status güncellemesi de admin panelinden yapılır

            Log::info('ProcessCustomVideoRequest queued successfully', [
                'request_id' => $this->request->id,
            ]);

        } catch (\Exception $e) {
            Log::error('ProcessCustomVideoRequest failed', [
                'request_id' => $this->request->id,
                'error' => $e->getMessage(),
            ]);

            $service->updateStatus($this->request, 'failed', $e->getMessage());

            $this->fail($e);
        }
    }

    /**
     * Tüm retry'lar tükenince çağrılır
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessCustomVideoRequest permanently failed', [
            'request_id' => $this->request->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
