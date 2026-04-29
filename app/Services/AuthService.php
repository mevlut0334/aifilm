<?php

namespace App\Services;

use App\Http\Requests\Web\Auth\LoginRequest;
use App\Http\Requests\Web\Auth\RegisterRequest;
use App\Models\User;
use App\Repositories\SettingRepository;
use App\Repositories\UserRepository;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthService
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

    public function login(LoginRequest $request): bool
    {
        return Auth::attempt($request->only('email', 'password'));
    }

    public function logout(): void
    {
        Auth::guard('web')->logout();
    }

    public function getUser(): ?User
    {
        return Auth::guard('web')->user();
    }

    public function sendResetLink(string $email): string
    {
        return Password::sendResetLink(['email' => $email]);
    }

    public function resetPassword(array $data): string
    {
        return Password::reset(
            $data,
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => bcrypt($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );
    }
}
