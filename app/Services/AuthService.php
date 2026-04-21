<?php

namespace App\Services;

use App\Http\Requests\Web\Auth\LoginRequest;
use App\Http\Requests\Web\Auth\RegisterRequest;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    public function __construct(
        private UserRepository $userRepository
    ) {}

    public function register(RegisterRequest $request): User
    {
        return $this->userRepository->create($request->validated());
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
}
