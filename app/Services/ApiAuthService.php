<?php

namespace App\Services;

use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\NewAccessToken;

class ApiAuthService
{
    public function __construct(
        private UserRepository $userRepository
    ) {}

    public function register(RegisterRequest $request): User
    {
        return $this->userRepository->create($request->validated());
    }

    public function login(LoginRequest $request): ?NewAccessToken
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            return null;
        }

        return Auth::guard('web')->user()->createToken('api-token');
    }

    public function logout(): void
    {
        Auth::user()->currentAccessToken()?->delete();
    }

    public function getUser(): ?User
    {
        return Auth::user();
    }
}
