<?php

namespace App\Services;

use App\Repositories\PackageRepository;
use App\Repositories\PurchaseRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GooglePlayService
{
    public function __construct(
        private PurchaseRepository $purchaseRepository,
        private PackageRepository $packageRepository,
        private TokenService $tokenService
    ) {}

    public function verifyPurchase(int $userId, string $productId, string $purchaseToken, string $packageName): array
    {
        // Get package by product_id to determine token amount
        $package = $this->packageRepository->findByProductId($productId);
        if (! $package) {
            throw new Exception("Invalid product ID: {$productId}");
        }

        $tokenAmount = $package->token_amount;

        // Verify with Google Play Developer API
        $result = $this->verifyWithGoogle($packageName, $productId, $purchaseToken);

        if (! isset($result['purchaseState']) || $result['purchaseState'] != 0) {
            throw new Exception('Google Play purchase verification failed');
        }

        // Extract transaction ID (orderId)
        $transactionId = $result['orderId'] ?? $purchaseToken;

        // Check duplicate
        if ($this->purchaseRepository->existsByTransactionId($transactionId)) {
            throw new Exception('Purchase already processed');
        }

        DB::beginTransaction();
        try {
            // Create purchase record
            $purchase = $this->purchaseRepository->create([
                'user_id' => $userId,
                'package_id' => $package->id,
                'platform' => 'android',
                'amount_paid' => null,
                'currency' => null,
                'gateway_transaction_id' => $transactionId,
                'token_amount' => $tokenAmount,
                'status' => 'completed',
            ]);

            // Add tokens to user
            $this->tokenService->addTokens(
                $userId,
                $tokenAmount,
                'purchase',
                "Android IAP purchase (Product: {$productId})",
                $transactionId,
                'google_play'
            );

            DB::commit();

            return [
                'success' => true,
                'tokens_added' => $tokenAmount,
                'purchase_id' => $purchase->id,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Google Play purchase failed: '.$e->getMessage());
            throw $e;
        }
    }

    private function verifyWithGoogle(string $packageName, string $productId, string $purchaseToken): array
    {
        $accessToken = $this->getAccessToken();

        $url = sprintf(
            'https://androidpublisher.googleapis.com/androidpublisher/v3/applications/%s/purchases/products/%s/tokens/%s',
            $packageName,
            $productId,
            $purchaseToken
        );

        $response = Http::withToken($accessToken)->get($url);

        if (! $response->successful()) {
            Log::error('Google Play API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new Exception('Failed to verify purchase with Google Play API');
        }

        return $response->json();
    }

    private function getAccessToken(): string
    {
        // This should use Google OAuth2 service account to get access token
        // For now, return from config - implement proper OAuth2 flow
        $token = config('services.google.play_access_token');

        if (! $token) {
            throw new Exception('Google Play access token not configured');
        }

        return $token;
    }
}
