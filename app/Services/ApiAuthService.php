<?php

namespace App\Services;

use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Models\User;
use App\Repositories\SettingRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\NewAccessToken;

class ApiAuthService
{
    public function __construct(
        private UserRepository $userRepository,
        private TokenService $tokenService,
        private SettingRepository $settingRepository
    ) {}

    public function register(RegisterRequest $request): User
    {
        $user = $this->userRepository->create($request->validated());

        // Grant registration tokens
        $tokenAmount = $this->settingRepository->get('registration_token_grant', 100);
        if ($tokenAmount > 0) {
            $this->tokenService->addTokens(
                $user->id,
                $tokenAmount,
                'registration',
                'Registration bonus'
            );
        }

        return $user;
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
