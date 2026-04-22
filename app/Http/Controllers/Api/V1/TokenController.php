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
    ) {}

    public function balance(): JsonResponse
    {
        $balance = $this->tokenService->getBalance(auth()->id());

        return $this->successResponse(
            data: ['balance' => $balance]
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
