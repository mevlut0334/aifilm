<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Services\ApiAuthService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(
        private ApiAuthService $authService
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->authService->register($request);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'data' => [
                'user' => $user,
            ],
            'locale' => app()->getLocale(),
        ]);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $token = $this->authService->login($request);

        if (! $token) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
                'locale' => app()->getLocale(),
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'token' => $token->plainTextToken,
            ],
            'locale' => app()->getLocale(),
        ]);
    }

    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return response()->json([
            'success' => true,
            'message' => 'Logged out',
            'locale' => app()->getLocale(),
        ]);
    }

    public function user(): JsonResponse
    {
        $user = $this->authService->getUser();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
            ],
            'locale' => app()->getLocale(),
        ]);
    }
}
