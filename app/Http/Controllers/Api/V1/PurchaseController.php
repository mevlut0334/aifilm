<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\AppleIAPService;
use App\Services\GooglePlayService;
use App\Services\SubscriptionService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    use ApiResponse;

    public function __construct(
        private AppleIAPService $appleIAPService,
        private GooglePlayService $googlePlayService,
        private SubscriptionService $subscriptionService
    ) {}

    // Tek seferlik iOS satın alma (mevcut)
    public function verifyIOS(Request $request): JsonResponse
    {
        $request->validate([
            'product_id'   => 'required|string',
            'receipt_data' => 'required|string',
        ]);

        try {
            $result = $this->appleIAPService->verifyPurchase(
                auth()->id(),
                $request->input('product_id'),
                $request->input('receipt_data')
            );

            return $this->successResponse(data: $result);
        } catch (\Exception $e) {
            return $this->errorResponse(message: $e->getMessage(), status: 400);
        }
    }

    // Tek seferlik Android satın alma (mevcut)
    public function verifyAndroid(Request $request): JsonResponse
    {
        $request->validate([
            'product_id'    => 'required|string',
            'purchase_token' => 'required|string',
            'package_name'  => 'required|string',
        ]);

        try {
            $result = $this->googlePlayService->verifyPurchase(
                auth()->id(),
                $request->input('product_id'),
                $request->input('purchase_token'),
                $request->input('package_name')
            );

            return $this->successResponse(data: $result);
        } catch (\Exception $e) {
            return $this->errorResponse(message: $e->getMessage(), status: 400);
        }
    }

    // iOS abonelik doğrulama (yeni)
    public function subscribeIOS(Request $request): JsonResponse
    {
        $request->validate([
            'product_id'   => 'required|string',
            'receipt_data' => 'required|string',
        ]);

        try {
            $result = $this->appleIAPService->verifySubscription(
                auth()->id(),
                $request->input('product_id'),
                $request->input('receipt_data')
            );

            return $this->successResponse(data: $result);
        } catch (\Exception $e) {
            return $this->errorResponse(message: $e->getMessage(), status: 400);
        }
    }

    // Android abonelik doğrulama (yeni)
    public function subscribeAndroid(Request $request): JsonResponse
    {
        $request->validate([
            'product_id'    => 'required|string',
            'purchase_token' => 'required|string',
            'package_name'  => 'required|string',
        ]);

        try {
            $result = $this->googlePlayService->verifySubscription(
                auth()->id(),
                $request->input('product_id'),
                $request->input('purchase_token'),
                $request->input('package_name')
            );

            return $this->successResponse(data: $result);
        } catch (\Exception $e) {
            return $this->errorResponse(message: $e->getMessage(), status: 400);
        }
    }

    // Abonelik durumu sorgulama (yeni)
    public function subscriptionStatus(Request $request): JsonResponse
    {
        $request->validate([
            'platform' => 'required|string|in:ios,android',
        ]);

        try {
            $status = $this->subscriptionService->getSubscriptionStatus(
                auth()->id(),
                $request->input('platform')
            );

            return $this->successResponse(data: $status);
        } catch (\Exception $e) {
            return $this->errorResponse(message: $e->getMessage(), status: 400);
        }
    }
}
