<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\AppleIAPService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class AppStoreWebhookController extends Controller
{
    public function __construct(
        private AppleIAPService $appleIAPService
    ) {}

    public function handle(Request $request): Response
    {
        $payload = $request->all();

        Log::info('App Store webhook received', ['payload' => $payload]);

        // Apple V1 Server Notifications (signedPayload yoksa legacy)
        if (isset($payload['signedPayload'])) {
            return $this->handleV2($payload['signedPayload']);
        }

        // Legacy V1 notification
        if (isset($payload['notification_type'])) {
            return $this->handleV1($payload);
        }

        Log::warning('App Store webhook: unrecognized payload format');
        return response('', 200);
    }

    // App Store Server Notifications V2 (JWT tabanlı)
    private function handleV2(string $signedPayload): Response
    {
        try {
            $decoded = $this->decodeJWT($signedPayload);
            if (! $decoded) {
                Log::error('App Store V2: JWT decode failed');
                return response('', 200);
            }

            $notificationType = $decoded['notificationType'] ?? null;
            $subtype           = $decoded['subtype'] ?? null;
            $data              = $decoded['data'] ?? [];

            // İç içe signed transaction bilgisi
            $transactionInfo = [];
            if (isset($data['signedTransactionInfo'])) {
                $transactionInfo = $this->decodeJWT($data['signedTransactionInfo']) ?? [];
            }

            $renewalInfo = [];
            if (isset($data['signedRenewalInfo'])) {
                $renewalInfo = $this->decodeJWT($data['signedRenewalInfo']) ?? [];
            }

            Log::info('App Store V2 notification', [
                'type'    => $notificationType,
                'subtype' => $subtype,
            ]);

            match ($notificationType) {
                'SUBSCRIBED'       => $this->onSubscribed($transactionInfo),
                'DID_RENEW'        => $this->onRenewed($transactionInfo),
                'EXPIRED'          => $this->onExpired($transactionInfo, $renewalInfo),
                'DID_CHANGE_RENEWAL_STATUS' => $this->onRenewalStatusChanged($transactionInfo, $renewalInfo),
                'REFUND'           => $this->onRefund($transactionInfo),
                default            => Log::info("App Store V2: unhandled type {$notificationType}"),
            };
        } catch (\Exception $e) {
            Log::error('App Store V2 webhook error: ' . $e->getMessage());
        }

        return response('', 200);
    }

    // Legacy V1 notification
    private function handleV1(array $payload): Response
    {
        try {
            $notificationType  = $payload['notification_type'] ?? null;
            $latestReceiptInfo = $payload['latest_receipt_info'] ?? null;
            $unifiedReceipt    = $payload['unified_receipt'] ?? [];
            $latestInfo        = $unifiedReceipt['latest_receipt_info'][0] ?? $latestReceiptInfo ?? [];

            Log::info('App Store V1 notification', ['type' => $notificationType]);

            match ($notificationType) {
                'INITIAL_BUY', 'INTERACTIVE_RENEWAL' => $this->onSubscribed($latestInfo),
                'DID_RENEW'                           => $this->onRenewed($latestInfo),
                'CANCEL', 'REVOKE'                    => $this->onExpired($latestInfo, []),
                'DID_CHANGE_RENEWAL_STATUS'           => $this->onRenewalStatusChanged($latestInfo, []),
                default => Log::info("App Store V1: unhandled type {$notificationType}"),
            };
        } catch (\Exception $e) {
            Log::error('App Store V1 webhook error: ' . $e->getMessage());
        }

        return response('', 200);
    }

    private function onSubscribed(array $transactionInfo): void
    {
        $originalTransactionId = $transactionInfo['originalTransactionId']
            ?? $transactionInfo['original_transaction_id']
            ?? null;

        Log::info('App Store: new subscription', [
            'original_transaction_id' => $originalTransactionId,
        ]);

        // İlk abonelik verifySubscription endpoint'inden işleniyor
        // Webhook burada sadece loglama yapar
    }

    private function onRenewed(array $transactionInfo): void
    {
        $originalTransactionId = $transactionInfo['originalTransactionId']
            ?? $transactionInfo['original_transaction_id']
            ?? null;

        $newTransactionId = $transactionInfo['transactionId']
            ?? $transactionInfo['transaction_id']
            ?? null;

        $productId = $transactionInfo['productId']
            ?? $transactionInfo['product_id']
            ?? null;

        $expiresAtMs = $transactionInfo['expiresDate']
            ?? $transactionInfo['expires_date_ms']
            ?? null;

        if (! $originalTransactionId || ! $newTransactionId || ! $productId || ! $expiresAtMs) {
            Log::warning('App Store renewal: missing required fields', $transactionInfo);
            return;
        }

        $this->appleIAPService->handleRenewal(
            $originalTransactionId,
            $newTransactionId,
            $productId,
            (int) $expiresAtMs
        );
    }

    private function onExpired(array $transactionInfo, array $renewalInfo): void
    {
        $originalTransactionId = $transactionInfo['originalTransactionId']
            ?? $transactionInfo['original_transaction_id']
            ?? null;

        if (! $originalTransactionId) {
            return;
        }

        $this->appleIAPService->handleCancellation($originalTransactionId);
    }

    private function onRenewalStatusChanged(array $transactionInfo, array $renewalInfo): void
    {
        $autoRenewStatus = $renewalInfo['autoRenewStatus']
            ?? $renewalInfo['auto_renew_status']
            ?? null;

        $originalTransactionId = $transactionInfo['originalTransactionId']
            ?? $transactionInfo['original_transaction_id']
            ?? null;

        if ($autoRenewStatus === 0 || $autoRenewStatus === '0') {
            // Kullanıcı otomatik yenilemeyi kapattı
            if ($originalTransactionId) {
                $this->appleIAPService->handleCancellation($originalTransactionId);
            }
        }

        Log::info('App Store renewal status changed', [
            'auto_renew_status'       => $autoRenewStatus,
            'original_transaction_id' => $originalTransactionId,
        ]);
    }

    private function onRefund(array $transactionInfo): void
    {
        $originalTransactionId = $transactionInfo['originalTransactionId'] ?? null;

        if ($originalTransactionId) {
            $this->appleIAPService->handleCancellation($originalTransactionId);
        }

        Log::info('App Store refund processed', [
            'original_transaction_id' => $originalTransactionId,
        ]);
    }

    // JWT'nin payload kısmını decode et (imza doğrulaması production'da eklenebilir)
    private function decodeJWT(string $jwt): ?array
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            return null;
        }

        $payload = $parts[1];
        // Base64url decode
        $payload = str_replace(['-', '_'], ['+', '/'], $payload);
        $payload = base64_decode(str_pad($payload, strlen($payload) + (4 - strlen($payload) % 4) % 4, '='));

        if (! $payload) {
            return null;
        }

        return json_decode($payload, true);
    }
}
