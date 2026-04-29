<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Services\ApiAuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        private ApiAuthService $authService
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->authService->register($request);

        return $this->successResponse(
            data: ['user' => $user],
            message: 'Registration successful'
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $token = $this->authService->login($request);

        if (! $token) {
            return $this->errorResponse(
                message: 'Invalid credentials',
                status: 401
            );
        }

        return $this->successResponse(
            data: ['token' => $token->plainTextToken],
            message: 'Login successful'
        );
    }

    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return $this->successResponse(
            message: 'Logged out'
        );
    }

    public function user(): JsonResponse
    {
        $user = $this->authService->getUser();

        return $this->successResponse(
            data: ['user' => $user]
        );
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $status = $this->authService->sendResetLink($request->email);

        return $status === Password::RESET_LINK_SENT
            ? $this->successResponse(message: __($status))
            : $this->errorResponse(message: __($status), status: 422);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = $this->authService->resetPassword($request->only(
            'token', 'email', 'password', 'password_confirmation'
        ));

        return $status === Password::PASSWORD_RESET
            ? $this->successResponse(message: __($status))
            : $this->errorResponse(message: __($status), status: 422);
    }
}
