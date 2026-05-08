<?php

namespace App\Services;

use App\Repositories\PurchaseRepository;
use Illuminate\Support\Facades\Log;

class SubscriptionService
{
    public function __construct(
        private PurchaseRepository $purchaseRepository,
        private TokenService $tokenService
    ) {}

    // Kullanıcının belirli platformda aktif aboneliği var mı?
    public function hasActiveSubscription(int $userId, string $platform): bool
    {
        return $this->purchaseRepository->findActiveSubscription($userId, $platform) !== null;
    }

    // Kullanıcının abonelik durumunu getir
    public function getSubscriptionStatus(int $userId, string $platform): array
    {
        $purchase = $this->purchaseRepository->findActiveSubscription($userId, $platform);

        if (! $purchase) {
            return [
                'is_active'    => false,
                'platform'     => $platform,
                'expires_at'   => null,
                'auto_renewing' => false,
                'status'       => 'none',
            ];
        }

        return [
            'is_active'    => true,
            'platform'     => $platform,
            'expires_at'   => $purchase->expires_at?->toISOString(),
            'auto_renewing' => $purchase->auto_renewing,
            'status'       => $purchase->subscription_status,
            'package_id'   => $purchase->package_id,
            'token_amount' => $purchase->token_amount,
        ];
    }

    // Süresi dolmuş abonelikleri expire et (scheduler ile çalışır)
    public function expireOldSubscriptions(): int
    {
        $expired = $this->purchaseRepository->getExpiredSubscriptions();
        $count   = 0;

        foreach ($expired as $purchase) {
            $this->purchaseRepository->update($purchase, [
                'subscription_status' => 'expired',
                'auto_renewing'       => false,
            ]);

            Log::info('Subscription expired', [
                'purchase_id' => $purchase->id,
                'user_id'     => $purchase->user_id,
                'platform'    => $purchase->platform,
                'expired_at'  => $purchase->expires_at,
            ]);

            $count++;
        }

        return $count;
    }
}
