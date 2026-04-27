<?php

namespace App\Jobs;

use App\Models\CustomImage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessCustomImageRequest implements ShouldQueue
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
        public readonly CustomImage $customImage
    ) {}

    /**
     * Token bu Job çalışmadan ÖNCE controller'da kesilir.
     * Bu Job sadece harici AI işlemini arka planda çalıştırır.
     * Başarısız olursa token iadesi failed() içinde yapılmalıdır.
     */
    public function handle(): void
    {
        Log::info('ProcessCustomImageRequest started', [
            'custom_image_id' => $this->customImage->id,
            'format' => $this->customImage->format,
        ]);

        try {
            // TODO: Buraya harici AI API çağrısı gelecek
            // Örnek:
            // $outputUrl = app(ExternalAiService::class)->processImage($this->customImage);
            // $this->customImage->update(['status' => 'completed', 'output_url' => $outputUrl]);

            Log::info('ProcessCustomImageRequest queued successfully', [
                'custom_image_id' => $this->customImage->id,
            ]);

        } catch (\Exception $e) {
            Log::error('ProcessCustomImageRequest failed', [
                'custom_image_id' => $this->customImage->id,
                'error' => $e->getMessage(),
            ]);

            $this->customImage->update(['status' => 'failed']);

            $this->fail($e);
        }
    }

    /**
     * Tüm retry'lar tükenince çağrılır.
     * Token iadesi burada yapılır çünkü controller'da kesilmişti.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessCustomImageRequest permanently failed', [
            'custom_image_id' => $this->customImage->id,
            'error' => $exception->getMessage(),
        ]);

        try {
            // Token iadesi - controller'da kesilmişti
            if ($this->customImage->token_cost > 0) {
                app(\App\Services\TokenService::class)->addTokens(
                    userId: $this->customImage->user_id,
                    amount: $this->customImage->token_cost,
                    type: 'admin_grant',
                    note: 'Token refund for failed custom image request',
                    referenceId: (string) $this->customImage->id,
                    referenceType: CustomImage::class
                );
            }

            $this->customImage->update(['status' => 'failed']);

        } catch (\Exception $e) {
            Log::error('ProcessCustomImageRequest token refund failed', [
                'custom_image_id' => $this->customImage->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
