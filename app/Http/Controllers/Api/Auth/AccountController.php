<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Services\AccountDeletionService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    use ApiResponse;

    public function __construct(
        private AccountDeletionService $accountDeletionService
    ) {}

    /**
     * Giriş yapmış kullanıcı kendi hesabını siler.
     * Hiçbir parametre (id) almaz; her zaman Auth::user() kullanılır,
     * bu sayede bir kullanıcı başka birinin hesabını silemez.
     */
    public function destroy(): JsonResponse
    {
        $user = Auth::user();

        $this->accountDeletionService->deleteAccount($user);

        return $this->successResponse(
            message: 'Account deleted successfully'
        );
    }
}
