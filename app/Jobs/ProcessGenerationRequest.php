<?php

namespace App\Jobs;

use App\Models\GenerationRequest;
use App\Services\GenerationRequestService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessGenerationRequest implements ShouldQueue
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
        public readonly GenerationRequest $request
    ) {}

    /**
     * Job çalıştığında yapılacaklar.
     * Token kesme/iade işlemleri GenerationRequestService içinde zaten yapılıyor.
     * Bu Job sadece harici işlemleri (AI API çağrısı vb.) arka planda çalıştırır.
     */
    public function handle(GenerationRequestService $service): void
    {
        Log::info('ProcessGenerationRequest started', [
            'request_id' => $this->request->id,
            'type' => $this->request->type,
        ]);

        try {
            // Zaten pending olarak oluşturuldu, processing'e al


            // TODO: Buraya harici AI API çağrısı gelecek
            // Örnek:
            // $outputUrl = app(ExternalAiService::class)->process($this->request);
            // $service->markAsCompleted($this->request, $outputUrl);

            Log::info('ProcessGenerationRequest completed', [
                'request_id' => $this->request->id,
            ]);

        } catch (\Exception $e) {
            Log::error('ProcessGenerationRequest failed', [
                'request_id' => $this->request->id,
                'error' => $e->getMessage(),
            ]);

            // markAsFailed içinde token iadesi otomatik yapılıyor
            $service->markAsFailed($this->request, $e->getMessage());

            $this->fail($e);
        }
    }

    /**
     * Tüm retry'lar tükenince çağrılır
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessGenerationRequest permanently failed', [
            'request_id' => $this->request->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
