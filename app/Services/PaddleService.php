<?php

namespace App\Services;

use App\Repositories\PackageRepository;
use App\Repositories\PurchaseRepository;
use Exception;
use Illuminate\Support\Facades\DB;
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
        $this->apiKey = config('services.paddle.api_key');
        $environment = config('services.paddle.environment', 'sandbox');
        $this->apiUrl = $environment === 'production'
            ? 'https://api.paddle.com'
            : 'https://sandbox-api.paddle.com';
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
     * Get price details from Paddle API
     *
     * @param  string  $priceId  Paddle Price ID
     * @return array|null Returns array with 'amount' and 'currency' or null on failure
     */
    public function getPriceDetails(string $priceId): ?array
    {
        try {
            // Check if API key is configured
            if (empty($this->apiKey)) {
                Log::warning('Paddle API key not configured');

                return null;
            }

            // Make API request to Paddle
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
                'Content-Type' => 'application/json',
            ])->get("{$this->apiUrl}/prices/{$priceId}");

            // Check if request was successful
            if (! $response->successful()) {
                Log::error('Paddle API request failed', [
                    'price_id' => $priceId,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);

                return null;
            }

            $data = $response->json();

            // Extract price information from response
            if (isset($data['data']['unit_price'])) {
                return [
                    'amount' => number_format($data['data']['unit_price']['amount'] / 100, 2), // Convert cents to dollars
                    'currency' => strtoupper($data['data']['unit_price']['currency_code'] ?? 'USD'),
                ];
            }

            Log::warning('Invalid Paddle API response structure', ['price_id' => $priceId, 'data' => $data]);

            return null;

        } catch (Exception $e) {
            Log::error('Failed to fetch price from Paddle', [
                'price_id' => $priceId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
