<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\TokenService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class TokenController extends Controller
{
    use ApiResponse;

    public function __construct(
        private TokenService $tokenService
    ) {
    }

    public function balance(): JsonResponse
    {
        $balance = $this->tokenService->getBalance(auth()->id());
        $customImageCost = (int) \App\Models\Setting::get('custom_image_token_cost', 50);

        return $this->successResponse(
            data: [
                'balance' => $balance,
                'custom_image_token_cost' => $customImageCost,
            ]
        );
    }

    public function transactions(): JsonResponse
    {
        $transactions = $this->tokenService->getTransactions(auth()->id());

        return $this->successResponse(
            data: $transactions
        );
    }
}
