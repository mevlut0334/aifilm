<?php

namespace App\Services;

use App\Repositories\PackageRepository;
use App\Repositories\PurchaseRepository;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaddleService
{
    private ?string $apiKey;

    private string $apiUrl;

    public function __construct(
        private PurchaseRepository $purchaseRepository,
        private PackageRepository $packageRepository,
        private TokenService $tokenService
    ) {
        $this->apiKey = config('cashier.api_key');
        $this->apiUrl = config('cashier.sandbox')
            ? 'https://sandbox-api.paddle.com'
            : 'https://api.paddle.com';
    }

    public function handleWebhook(array $data): bool
    {
        try {
            $alertName = $data['alert_name'] ?? null;

            if ($alertName === 'payment_succeeded') {
                return $this->handlePaymentSucceeded($data);
            }

            if ($alertName === 'payment_refunded') {
                return $this->handlePaymentRefunded($data);
            }

            return false;
        } catch (Exception $e) {
            Log::error('Paddle webhook error: '.$e->getMessage(), ['data' => $data]);
            throw $e;
        }
    }

    private function handlePaymentSucceeded(array $data): bool
    {
        $transactionId = $data['order_id'] ?? $data['subscription_payment_id'] ?? null;
        $priceId = $data['passthrough'] ?? null;
        $userId = $data['user_id'] ?? null;
        $amount = $data['sale_gross'] ?? null;
        $currency = $data['currency'] ?? null;

        if (! $transactionId || ! $priceId || ! $userId) {
            Log::warning('Invalid payment webhook data', ['data' => $data]);

            return false;
        }

        // Check duplicate
        if ($this->purchaseRepository->existsByTransactionId($transactionId)) {
            Log::info('Duplicate purchase detected', ['transaction_id' => $transactionId]);

            return true;
        }

        // Find package by Paddle Price ID
        $package = $this->packageRepository->findByPaddlePriceId($priceId);
        if (! $package) {
            Log::error('Package not found for paddle_price_id', ['paddle_price_id' => $priceId]);

            return false;
        }

        DB::beginTransaction();
        try {
            // Create purchase record
            $purchase = $this->purchaseRepository->create([
                'user_id' => $userId,
                'package_id' => $package->id,
                'platform' => 'web',
                'amount_paid' => $amount,
                'currency' => $currency,
                'gateway_transaction_id' => $transactionId,
                'token_amount' => $package->token_amount,
                'status' => 'completed',
            ]);

            // Add tokens to user
            $this->tokenService->addTokens(
                $userId,
                $package->token_amount,
                'purchase',
                'Package purchase',
                $transactionId,
                'paddle'
            );

            DB::commit();
            Log::info('Purchase completed successfully', ['purchase_id' => $purchase->id]);

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to process purchase: '.$e->getMessage());
            throw $e;
        }
    }

    private function handlePaymentRefunded(array $data): bool
    {
        $transactionId = $data['order_id'] ?? null;

        if (! $transactionId) {
            return false;
        }

        $purchase = $this->purchaseRepository->findByTransactionId($transactionId);
        if (! $purchase) {
            return false;
        }

        $this->purchaseRepository->update($purchase, ['status' => 'refunded']);
        Log::info('Purchase refunded', ['purchase_id' => $purchase->id]);

        return true;
    }

    /**
     * Get price details from Paddle API with caching (5 minutes)
     *
     * @param  string  $priceId  Paddle Price ID
     * @return array|null Returns array with 'amount' and 'currency' or null on failure
     */
    public function getPrice(string $priceId): ?array
    {
        return Cache::remember("paddle_price_{$priceId}", now()->addMinutes(5), function () use ($priceId) {
            return $this->fetchFromApi($priceId);
        });
    }

    /**
     * Clear price cache for a specific price ID
     */
    public function clearPriceCache(string $priceId): void
    {
        Cache::forget("paddle_price_{$priceId}");
    }

    /**
     * Fetch price details from Paddle API
     */
    private function fetchFromApi(string $priceId): ?array
    {
        try {
            if (empty($this->apiKey)) {
                Log::warning('Paddle API key not configured');

                return null;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
            ])->get("{$this->apiUrl}/prices/{$priceId}");

            if ($response->successful()) {
                $data = $response->json('data');

                return [
                    'amount' => (float) $data['unit_price']['amount'] / 100, // 999 → 9.99
                    'currency' => $data['unit_price']['currency_code'],         // "USD"
                    'is_recurring' => isset($data['billing_cycle']),           // recurring varsa subscription
                ];
            }

            Log::warning('Paddle fiyat alınamadı', [
                'price_id' => $priceId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;

        } catch (Exception $e) {
            Log::error('Paddle API hatası: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Check if a price is subscription (recurring)
     */
    public function isSubscriptionPrice(string $priceId): bool
    {
        $priceDetails = $this->getPrice($priceId);

        return $priceDetails['is_recurring'] ?? false;
    }

    /**
     * Legacy method for backward compatibility
     */
    public function getPriceDetails(string $priceId): ?array
    {
        return $this->getPrice($priceId);
    }

    /**
     * Fetch multiple prices from Paddle API
     */
    public function fetchPaddlePrices(array $priceIds): array
    {
        if (empty($priceIds)) {
            return [];
        }

        $cacheKey = 'paddle_prices_'.md5(implode(',', $priceIds));

        return Cache::remember($cacheKey, now()->addMinutes(60), function () use ($priceIds) {
            try {
                $response = Http::withToken($this->apiKey)
                    ->get("{$this->apiUrl}/prices", [
                        'id' => $priceIds,
                    ]);

                if (! $response->successful()) {
                    Log::error('Paddle API fiyat çekme hatası', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);

                    return [];
                }

                $prices = [];
                foreach ($response->json('data', []) as $price) {
                    $priceId = $price['id'] ?? null;
                    if (! $priceId) {
                        continue;
                    }

                    $amount = $price['unit_price']['amount'] ?? 0;
                    $currency = $price['unit_price']['currency_code'] ?? 'USD';

                    $prices[$priceId] = [
                        'amount' => $amount / 100,
                        'currency' => $currency,
                    ];
                }

                return $prices;

            } catch (Exception $e) {
                Log::error('Paddle API bağlantı hatası: '.$e->getMessage());

                return [];
            }
        });
    }
}
