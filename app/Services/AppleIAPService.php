<?php

namespace App\Services;

use App\Repositories\MobilePackageRepository;
use App\Repositories\PackageRepository;
use App\Repositories\PurchaseRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AppleIAPService
{
    private const SANDBOX_URL    = 'https://sandbox.itunes.apple.com/verifyReceipt';
    private const PRODUCTION_URL = 'https://buy.itunes.apple.com/verifyReceipt';

    public function __construct(
        private PurchaseRepository $purchaseRepository,
        private PackageRepository $packageRepository,
        private MobilePackageRepository $mobilePackageRepository,
        private TokenService $tokenService
    ) {}

    // Tek seferlik satın alma (mevcut, dokunulmadı)
    public function verifyPurchase(int $userId, string $productId, string $receiptData): array
    {
        $package = $this->packageRepository->findByProductId($productId);
        if (! $package) {
            throw new Exception("Invalid product ID: {$productId}");
        }

        $tokenAmount = $package->token_amount;

        $result = $this->verifyWithApple($receiptData, self::PRODUCTION_URL);

        if (isset($result['status']) && $result['status'] == 21007) {
            $result = $this->verifyWithApple($receiptData, self::SANDBOX_URL);
        }

        if (! isset($result['status']) || $result['status'] != 0) {
            throw new Exception('Apple receipt verification failed: ' . ($result['status'] ?? 'unknown'));
        }

        $transactionId = $result['receipt']['in_app'][0]['transaction_id'] ?? null;
        if (! $transactionId) {
            throw new Exception('Transaction ID not found in receipt');
        }

        if ($this->purchaseRepository->existsByTransactionId($transactionId)) {
            throw new Exception('Purchase already processed');
        }

        DB::beginTransaction();
        try {
            $purchase = $this->purchaseRepository->create([
                'user_id'                => $userId,
                'package_id'             => $package->id,
                'platform'               => 'ios',
                'amount_paid'            => null,
                'currency'               => null,
                'gateway_transaction_id' => $transactionId,
                'token_amount'           => $tokenAmount,
                'status'                 => 'completed',
                'is_subscription'        => false,
            ]);

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
                'success'      => true,
                'tokens_added' => $tokenAmount,
                'purchase_id'  => $purchase->id,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Apple IAP purchase failed: ' . $e->getMessage());
            throw $e;
        }
    }

    // Abonelik doğrulama — MobilePackageRepository kullanır
    public function verifySubscription(int $userId, string $productId, string $receiptData): array
    {
        $package = $this->mobilePackageRepository->findByIosProductId($productId);
        if (! $package) {
            throw new Exception("Invalid iOS subscription product ID: {$productId}");
        }

        $result = $this->verifyWithApple($receiptData, self::PRODUCTION_URL);

        if (isset($result['status']) && $result['status'] == 21007) {
            $result = $this->verifyWithApple($receiptData, self::SANDBOX_URL);
        }

        if (! isset($result['status']) || $result['status'] != 0) {
            throw new Exception('Apple subscription verification failed: ' . ($result['status'] ?? 'unknown'));
        }

        $latestReceiptInfo = $this->getLatestSubscriptionInfo($result);
        if (! $latestReceiptInfo) {
            throw new Exception('No subscription info found in receipt');
        }

        $transactionId         = $latestReceiptInfo['transaction_id'];
        $originalTransactionId = $latestReceiptInfo['original_transaction_id'];
        $expiresAtMs           = $latestReceiptInfo['expires_date_ms'] ?? null;
        $expiresAt             = $expiresAtMs
            ? now()->setTimestamp((int) ($expiresAtMs / 1000))
            : now()->addMonth();

        if ($this->purchaseRepository->existsByTransactionId($transactionId)) {
            $existing = $this->purchaseRepository->findByTransactionId($transactionId);
            return [
                'success'      => true,
                'tokens_added' => 0,
                'purchase_id'  => $existing->id,
                'expires_at'   => $expiresAt->toISOString(),
                'message'      => 'Subscription already active',
            ];
        }

        DB::beginTransaction();
        try {
            $purchase = $this->purchaseRepository->create([
                'user_id'                 => $userId,
                'package_id'              => $package->id,
                'platform'                => 'ios',
                'amount_paid'             => null,
                'currency'                => null,
                'gateway_transaction_id'  => $transactionId,
                'original_transaction_id' => $originalTransactionId,
                'token_amount'            => $package->token_amount,
                'status'                  => 'completed',
                'is_subscription'         => true,
                'subscription_status'     => 'active',
                'expires_at'              => $expiresAt,
                'auto_renewing'           => true,
            ]);

            $this->tokenService->addTokens(
                $userId,
                $package->token_amount,
                'purchase',
                "iOS Subscription purchase (Product: {$productId})",
                $transactionId,
                'apple_iap'
            );

            DB::commit();

            return [
                'success'      => true,
                'tokens_added' => $package->token_amount,
                'purchase_id'  => $purchase->id,
                'expires_at'   => $expiresAt->toISOString(),
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Apple subscription failed: ' . $e->getMessage());
            throw $e;
        }
    }

    // Webhook'tan gelen bildirime göre aboneliği yenile — MobilePackageRepository kullanır
    public function handleRenewal(string $originalTransactionId, string $newTransactionId, string $productId, int $expiresAtMs): void
    {
        if ($this->purchaseRepository->existsByTransactionId($newTransactionId)) {
            return;
        }

        $package = $this->mobilePackageRepository->findByIosProductId($productId);
        if (! $package) {
            Log::warning("Apple renewal: unknown iOS product {$productId}");
            return;
        }

        $original = $this->purchaseRepository->findByOriginalTransactionId($originalTransactionId);
        if (! $original) {
            Log::warning("Apple renewal: original subscription not found {$originalTransactionId}");
            return;
        }

        $expiresAt = now()->setTimestamp((int) ($expiresAtMs / 1000));

        DB::beginTransaction();
        try {
            $this->purchaseRepository->create([
                'user_id'                 => $original->user_id,
                'package_id'              => $package->id,
                'platform'                => 'ios',
                'amount_paid'             => null,
                'currency'                => null,
                'gateway_transaction_id'  => $newTransactionId,
                'original_transaction_id' => $originalTransactionId,
                'token_amount'            => $package->token_amount,
                'status'                  => 'completed',
                'is_subscription'         => true,
                'subscription_status'     => 'active',
                'expires_at'              => $expiresAt,
                'auto_renewing'           => true,
            ]);

            $this->purchaseRepository->update($original, [
                'subscription_status' => 'renewed',
                'auto_renewing'       => true,
            ]);

            $this->tokenService->resetAndAddTokens(
                $original->user_id,
                $package->token_amount,
                'subscription_renewal',
                "iOS Subscription renewal (Product: {$productId})",
                $newTransactionId,
                'apple_iap'
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Apple renewal failed: ' . $e->getMessage());
            throw $e;
        }
    }

    // Aboneliği iptal et
    public function handleCancellation(string $originalTransactionId): void
    {
        $purchase = $this->purchaseRepository->findByOriginalTransactionId($originalTransactionId);
        if (! $purchase) {
            return;
        }

        $this->purchaseRepository->update($purchase, [
            'subscription_status' => 'canceled',
            'auto_renewing'       => false,
        ]);

        Log::info("Apple subscription canceled: {$originalTransactionId}");
    }

    // Receipt içinden en güncel abonelik bilgisini çek
    private function getLatestSubscriptionInfo(array $result): ?array
    {
        $latestReceiptInfo = $result['latest_receipt_info'] ?? [];

        if (empty($latestReceiptInfo)) {
            return null;
        }

        usort($latestReceiptInfo, fn ($a, $b) =>
            ($b['expires_date_ms'] ?? 0) <=> ($a['expires_date_ms'] ?? 0)
        );

        return $latestReceiptInfo[0];
    }

    private function verifyWithApple(string $receiptData, string $url): array
    {
        $password = config('services.apple.shared_secret');

        $response = Http::post($url, [
            'receipt-data'             => $receiptData,
            'password'                 => $password,
            'exclude-old-transactions' => true,
        ]);

        if (! $response->successful()) {
            throw new Exception('Failed to connect to Apple verification server');
        }

        return $response->json();
    }
}
