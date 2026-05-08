<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\GooglePlayService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class GooglePlayWebhookController extends Controller
{
    public function __construct(
        private GooglePlayService $googlePlayService
    ) {}

    public function handle(Request $request): Response
    {
        try {
            $payload = $request->all();

            // Google Pub/Sub mesajı base64 encoded gelir
            $message = $payload['message'] ?? null;
            if (! $message) {
                Log::warning('Google Play webhook: no message in payload');
                return response('', 200);
            }

            $data = $message['data'] ?? null;
            if (! $data) {
                Log::warning('Google Play webhook: no data in message');
                return response('', 200);
            }

            // Base64 decode
            $decoded = json_decode(base64_decode($data), true);
            if (! $decoded) {
                Log::warning('Google Play webhook: failed to decode message data');
                return response('', 200);
            }

            Log::info('Google Play webhook received', ['data' => $decoded]);

            // subscriptionNotification veya oneTimeProductNotification
            if (isset($decoded['subscriptionNotification'])) {
                $this->handleSubscriptionNotification(
                    $decoded['subscriptionNotification'],
                    $decoded['packageName'] ?? ''
                );
            } elseif (isset($decoded['oneTimeProductNotification'])) {
                // Tek seferlik satın alma bildirimi — şu an sadece logluyoruz
                Log::info('Google Play one-time product notification', $decoded['oneTimeProductNotification']);
            } else {
                Log::info('Google Play webhook: unrecognized notification type', $decoded);
            }
        } catch (\Exception $e) {
            Log::error('Google Play webhook error: ' . $e->getMessage());
        }

        // Google Pub/Sub her zaman 200 bekler, aksi hâlde tekrar gönderir
        return response('', 200);
    }

    private function handleSubscriptionNotification(array $notification, string $packageName): void
    {
        $notificationType = $notification['notificationType'] ?? null;
        $purchaseToken    = $notification['purchaseToken'] ?? null;
        $subscriptionId   = $notification['subscriptionId'] ?? null; // productId

        if (! $purchaseToken || ! $subscriptionId) {
            Log::warning('Google Play subscription notification: missing fields', $notification);
            return;
        }

        Log::info('Google Play subscription notification', [
            'type'          => $notificationType,
            'subscriptionId' => $subscriptionId,
            'packageName'   => $packageName,
        ]);

        // Notification type sabitleri:
        // 1  = SUBSCRIPTION_RECOVERED
        // 2  = SUBSCRIPTION_RENEWED
        // 3  = SUBSCRIPTION_CANCELED
        // 4  = SUBSCRIPTION_PURCHASED
        // 5  = SUBSCRIPTION_ON_HOLD
        // 6  = SUBSCRIPTION_IN_GRACE_PERIOD
        // 7  = SUBSCRIPTION_RESTARTED
        // 8  = SUBSCRIPTION_PRICE_CHANGE_CONFIRMED
        // 9  = SUBSCRIPTION_DEFERRED
        // 10 = SUBSCRIPTION_PAUSED
        // 11 = SUBSCRIPTION_PAUSE_SCHEDULE_CHANGED
        // 12 = SUBSCRIPTION_REVOKED
        // 13 = SUBSCRIPTION_EXPIRED

        match ((int) $notificationType) {
            1, 2, 7 => $this->googlePlayService->handleRenewal(
                $purchaseToken,
                $packageName,
                $subscriptionId
            ),
            3, 12 => $this->googlePlayService->handleCancellation($purchaseToken),
            10    => $this->googlePlayService->handlePause($purchaseToken),
            13    => $this->googlePlayService->handleCancellation($purchaseToken),
            4     => Log::info('Google Play: new subscription purchased via webhook', [
                'subscriptionId' => $subscriptionId,
            ]),
            default => Log::info("Google Play: unhandled notification type {$notificationType}"),
        };
    }
}
