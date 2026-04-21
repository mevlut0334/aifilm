<?php

namespace App\Services;

use App\Repositories\PackageRepository;
use App\Repositories\PurchaseRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AppleIAPService
{
    private const SANDBOX_URL = 'https://sandbox.itunes.apple.com/verifyReceipt';

    private const PRODUCTION_URL = 'https://buy.itunes.apple.com/verifyReceipt';

    public function __construct(
        private PurchaseRepository $purchaseRepository,
        private PackageRepository $packageRepository,
        private TokenService $tokenService
    ) {}

    public function verifyPurchase(int $userId, string $productId, string $receiptData): array
    {
        // Get package by product_id to determine token amount
        $package = $this->packageRepository->findByProductId($productId);
        if (! $package) {
            throw new Exception("Invalid product ID: {$productId}");
        }

        $tokenAmount = $package->token_amount;

        // Try production first
        $result = $this->verifyWithApple($receiptData, self::PRODUCTION_URL);

        // If sandbox receipt, retry with sandbox URL
        if (isset($result['status']) && $result['status'] == 21007) {
            $result = $this->verifyWithApple($receiptData, self::SANDBOX_URL);
        }

        if (! isset($result['status']) || $result['status'] != 0) {
            throw new Exception('Apple receipt verification failed: '.($result['status'] ?? 'unknown'));
        }

        // Extract transaction ID
        $transactionId = $result['receipt']['in_app'][0]['transaction_id'] ?? null;
        if (! $transactionId) {
            throw new Exception('Transaction ID not found in receipt');
        }

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
                'platform' => 'ios',
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
                "iOS IAP purchase (Product: {$productId})",
                $transactionId,
                'apple_iap'
            );

            DB::commit();

            return [
                'success' => true,
                'tokens_added' => $tokenAmount,
                'purchase_id' => $purchase->id,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Apple IAP purchase failed: '.$e->getMessage());
            throw $e;
        }
    }

    private function verifyWithApple(string $receiptData, string $url): array
    {
        $password = config('services.apple.shared_secret');

        $response = Http::post($url, [
            'receipt-data' => $receiptData,
            'password' => $password,
            'exclude-old-transactions' => true,
        ]);

        if (! $response->successful()) {
            throw new Exception('Failed to connect to Apple verification server');
        }

        return $response->json();
    }
}
