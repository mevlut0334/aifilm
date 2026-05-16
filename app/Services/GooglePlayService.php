<?php

namespace App\Services;

use App\Repositories\MobilePackageRepository;
use App\Repositories\PackageRepository;
use App\Repositories\PurchaseRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GooglePlayService
{
    private const SUBSCRIPTIONS_API = 'https://androidpublisher.googleapis.com/androidpublisher/v3/applications/%s/purchases/subscriptionsv2/tokens/%s';
    private const PRODUCTS_API      = 'https://androidpublisher.googleapis.com/androidpublisher/v3/applications/%s/purchases/products/%s/tokens/%s';
    private const TOKEN_URL         = 'https://oauth2.googleapis.com/token';

    public function __construct(
        private PurchaseRepository $purchaseRepository,
        private PackageRepository $packageRepository,
        private MobilePackageRepository $mobilePackageRepository,
        private TokenService $tokenService
    ) {}

    // Tek seferlik satın alma
    public function verifyPurchase(int $userId, string $productId, string $purchaseToken, string $packageName): array
    {
        $package = $this->packageRepository->findByProductId($productId);
        if (! $package) {
            throw new Exception("Invalid product ID: {$productId}");
        }

        $tokenAmount = $package->token_amount;

        Log::info('Google Play verifyPurchase token_amount', [
            'user_id'      => $userId,
            'product_id'   => $productId,
            'token_amount' => $tokenAmount,
        ]);

        if ($tokenAmount <= 0) {
            throw new Exception("Package token_amount is not set or zero for product: {$productId}");
        }

        $result = $this->verifyProductWithGoogle($packageName, $productId, $purchaseToken);

        if (! isset($result['purchaseState']) || $result['purchaseState'] != 0) {
            throw new Exception('Google Play purchase verification failed');
        }

        $transactionId = $result['orderId'] ?? $purchaseToken;

        if ($this->purchaseRepository->existsByTransactionId($transactionId)) {
            throw new Exception('Purchase already processed');
        }

        // Purchase kaydı oluştur (tek INSERT, kendi içinde atomik)
        $purchase = $this->purchaseRepository->create([
            'user_id'                => $userId,
            'package_id'             => $package->id,
            'platform'               => 'android',
            'amount_paid'            => null,
            'currency'               => null,
            'gateway_transaction_id' => $transactionId,
            'token_amount'           => $tokenAmount,
            'status'                 => 'completed',
            'is_subscription'        => false,
        ]);

        // TokenService kendi transaction'ını yönetir
        $this->tokenService->addTokens(
            $userId,
            $tokenAmount,
            'purchase',
            "Android IAP purchase (Product: {$productId})",
            $transactionId,
            'google_play'
        );

        return [
            'success'      => true,
            'tokens_added' => $tokenAmount,
            'purchase_id'  => $purchase->id,
        ];
    }

    // Abonelik doğrulama
    public function verifySubscription(int $userId, string $productId, string $purchaseToken, string $packageName): array
    {
        $package = $this->mobilePackageRepository->findByAndroidProductId($productId);
        if (! $package) {
            throw new Exception("Invalid Android subscription product ID: {$productId}");
        }

        Log::info('Google Play verifySubscription token_amount', [
            'user_id'      => $userId,
            'product_id'   => $productId,
            'token_amount' => $package->token_amount,
        ]);

        if ($package->token_amount <= 0) {
            throw new Exception("Package token_amount is not set or zero for product: {$productId}");
        }

        $result = $this->verifySubscriptionWithGoogle($packageName, $purchaseToken);

        $subscriptionState = $result['subscriptionState'] ?? null;
        if (! in_array($subscriptionState, ['SUBSCRIPTION_STATE_ACTIVE', 'SUBSCRIPTION_STATE_IN_GRACE_PERIOD'])) {
            throw new Exception("Subscription is not active. State: {$subscriptionState}");
        }

        $lineItem  = $result['lineItems'][0] ?? [];
        $expiryMs  = $lineItem['expiryTime'] ?? null;
        $expiresAt = $expiryMs
            ? now()->setTimestamp((int) strtotime($expiryMs))
            : now()->addMonth();

        $transactionId         = $result['latestOrderId'] ?? $purchaseToken;
        $originalTransactionId = $purchaseToken;

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

        // Purchase kaydı oluştur (tek INSERT, kendi içinde atomik)
        $purchase = $this->purchaseRepository->create([
            'user_id'                 => $userId,
            'package_id'              => $package->id,
            'platform'                => 'android',
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

        // Abonelik başlangıcında: mevcut token sıfırla + paket tokenlarını yükle
        // TokenService kendi transaction'ını yönetir
        $this->tokenService->resetAndAddTokens(
            $userId,
            $package->token_amount,
            'purchase',
            "Android Subscription purchase (Product: {$productId})",
            $transactionId,
            'google_play'
        );

        Log::info('Google Play subscription verified and tokens loaded', [
            'user_id'        => $userId,
            'product_id'     => $productId,
            'token_amount'   => $package->token_amount,
            'transaction_id' => $transactionId,
        ]);

        return [
            'success'      => true,
            'tokens_added' => $package->token_amount,
            'purchase_id'  => $purchase->id,
            'expires_at'   => $expiresAt->toISOString(),
        ];
    }

    // Webhook'tan gelen yeni abonelik bildirimi (type 4)
    // Uygulama tarafından zaten işlendiyse skip edilir.
    // İşlenmemişse (uygulama başarısız olduysa) pending olarak loglanır.
    public function handleNewSubscriptionWebhook(string $purchaseToken, string $packageName, string $productId): void
    {
        $existing = $this->purchaseRepository->findByOriginalTransactionId($purchaseToken);

        if ($existing) {
            Log::info('Google Play type-4 webhook: subscription already processed by app', [
                'purchase_token' => $purchaseToken,
                'purchase_id'    => $existing->id,
                'user_id'        => $existing->user_id,
            ]);
            return;
        }

        // Uygulama bu aboneliği henüz işlemedi (network hatası vb.)
        // userId webhook'tan gelmiyor; bu kaydı pending olarak logla.
        // Kullanıcı uygulamayı tekrar açtığında subscribeAndroid endpoint'i
        // tekrar çağırarak kendi kendine düzelir.
        Log::critical('Google Play type-4 webhook: UNPROCESSED subscription detected', [
            'purchase_token' => $purchaseToken,
            'package_name'   => $packageName,
            'product_id'     => $productId,
            'action_needed'  => 'User will self-heal on next app open via subscribeAndroid. Monitor this token.',
        ]);
    }

    // Webhook'tan gelen yenileme bildirimi
    public function handleRenewal(string $purchaseToken, string $packageName, string $productId): void
    {
        try {
            $result = $this->verifySubscriptionWithGoogle($packageName, $purchaseToken);

            $lineItem   = $result['lineItems'][0] ?? [];
            $expiryTime = $lineItem['expiryTime'] ?? null;
            $expiresAt  = $expiryTime
                ? now()->setTimestamp((int) strtotime($expiryTime))
                : now()->addMonth();

            $newTransactionId = $result['latestOrderId'] ?? null;
            if (! $newTransactionId) {
                Log::warning('Google renewal: no orderId found');
                return;
            }

            if ($this->purchaseRepository->existsByTransactionId($newTransactionId)) {
                return;
            }

            $package = $this->mobilePackageRepository->findByAndroidProductId($productId);
            if (! $package) {
                Log::warning("Google renewal: unknown Android product {$productId}");
                return;
            }

            if ($package->token_amount <= 0) {
                Log::error("Google renewal: token_amount is zero for product {$productId}");
                return;
            }

            $original = $this->purchaseRepository->findByOriginalTransactionId($purchaseToken);
            if (! $original) {
                Log::warning("Google renewal: original subscription not found for token {$purchaseToken}");
                return;
            }

            // Purchase kayıtlarını atomik olarak güncelle
            // (resetAndAddTokens dışarıda, nested transaction çakışması önlenir)
            DB::beginTransaction();

            $this->purchaseRepository->create([
                'user_id'                 => $original->user_id,
                'package_id'              => $package->id,
                'platform'                => 'android',
                'amount_paid'             => null,
                'currency'                => null,
                'gateway_transaction_id'  => $newTransactionId,
                'original_transaction_id' => $purchaseToken,
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

            DB::commit();

            // Token sıfırlama ve yükleme — DB commit sonrası, kendi transaction'ında
            $this->tokenService->resetAndAddTokens(
                $original->user_id,
                $package->token_amount,
                'subscription_renewal',
                "Android Subscription renewal (Product: {$productId})",
                $newTransactionId,
                'google_play'
            );

            Log::info('Google Play renewal processed', [
                'transaction_id' => $newTransactionId,
                'user_id'        => $original->user_id,
                'token_amount'   => $package->token_amount,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Google Play renewal failed: ' . $e->getMessage());
        }
    }

    // Abonelik iptali
    public function handleCancellation(string $purchaseToken): void
    {
        $purchase = $this->purchaseRepository->findByOriginalTransactionId($purchaseToken);
        if (! $purchase) {
            return;
        }

        $this->purchaseRepository->update($purchase, [
            'subscription_status' => 'canceled',
            'auto_renewing'       => false,
        ]);

        Log::info('Google Play subscription canceled', ['purchase_token' => $purchaseToken]);
    }

    // Abonelik askıya alma
    public function handlePause(string $purchaseToken): void
    {
        $purchase = $this->purchaseRepository->findByOriginalTransactionId($purchaseToken);
        if (! $purchase) {
            return;
        }

        $this->purchaseRepository->update($purchase, [
            'subscription_status' => 'paused',
            'auto_renewing'       => false,
        ]);

        Log::info('Google Play subscription paused', ['purchase_token' => $purchaseToken]);
    }

    // Google Play Subscriptions V2 API
    private function verifySubscriptionWithGoogle(string $packageName, string $purchaseToken): array
    {
        $accessToken = $this->getAccessToken();
        $url         = sprintf(self::SUBSCRIPTIONS_API, $packageName, $purchaseToken);
        $response    = Http::withToken($accessToken)->get($url);

        if (! $response->successful()) {
            Log::error('Google Play Subscription API error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new Exception('Failed to verify subscription with Google Play API');
        }

        return $response->json();
    }

    // Google Play Products API (tek seferlik)
    private function verifyProductWithGoogle(string $packageName, string $productId, string $purchaseToken): array
    {
        $accessToken = $this->getAccessToken();
        $url         = sprintf(self::PRODUCTS_API, $packageName, $productId, $purchaseToken);
        $response    = Http::withToken($accessToken)->get($url);

        if (! $response->successful()) {
            Log::error('Google Play Product API error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new Exception('Failed to verify purchase with Google Play API');
        }

        return $response->json();
    }

    // Google OAuth2 Service Account ile access token al
    private function getAccessToken(): string
    {
        $credentialsPath = config('services.google.service_account_json');

        if (! $credentialsPath || ! file_exists($credentialsPath)) {
            throw new Exception('Google service account JSON not configured or not found');
        }

        $credentials = json_decode(file_get_contents($credentialsPath), true);
        if (! $credentials) {
            throw new Exception('Invalid Google service account JSON');
        }

        $now    = time();
        $expiry = $now + 3600;

        $header  = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode([
            'iss'   => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/androidpublisher',
            'aud'   => self::TOKEN_URL,
            'exp'   => $expiry,
            'iat'   => $now,
        ]));

        $header  = str_replace(['+', '/', '='], ['-', '_', ''], $header);
        $payload = str_replace(['+', '/', '='], ['-', '_', ''], $payload);

        $signInput  = "{$header}.{$payload}";
        $privateKey = openssl_pkey_get_private($credentials['private_key']);

        openssl_sign($signInput, $signature, $privateKey, 'SHA256');
        $signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        $jwt = "{$signInput}.{$signature}";

        $response = Http::asForm()->post(self::TOKEN_URL, [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $jwt,
        ]);

        if (! $response->successful()) {
            throw new Exception('Failed to get Google access token: ' . $response->body());
        }

        return $response->json('access_token');
    }
}
