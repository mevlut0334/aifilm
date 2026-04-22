<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\AppleIAPService;
use App\Services\GooglePlayService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    use ApiResponse;

    public function __construct(
        private AppleIAPService $appleIAPService,
        private GooglePlayService $googlePlayService
    ) {}

    public function verifyIOS(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|string',
            'receipt_data' => 'required|string',
        ]);

        try {
            $result = $this->appleIAPService->verifyPurchase(
                auth()->id(),
                $request->input('product_id'),
                $request->input('receipt_data')
            );

            return $this->successResponse(
                data: $result
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                message: $e->getMessage(),
                status: 400
            );
        }
    }

    public function verifyAndroid(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|string',
            'purchase_token' => 'required|string',
            'package_name' => 'required|string',
        ]);

        try {
            $result = $this->googlePlayService->verifyPurchase(
                auth()->id(),
                $request->input('product_id'),
                $request->input('purchase_token'),
                $request->input('package_name')
            );

            return $this->successResponse(
                data: $result
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                message: $e->getMessage(),
                status: 400
            );
        }
    }
}
