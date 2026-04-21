<?php

namespace App\Services;

use App\Http\Requests\Admin\Auth\LoginRequest;
use App\Models\Admin;
use App\Repositories\AdminRepository;
use Illuminate\Support\Facades\Auth;

class AdminAuthService
{
    public function __construct(
        private AdminRepository $adminRepository
    ) {}

    public function login(LoginRequest $request): bool
    {
        return Auth::guard('admin')->attempt($request->only('email', 'password'));
    }

    public function logout(): void
    {
        Auth::guard('admin')->logout();
    }

    public function getAdmin(): ?Admin
    {
        return Auth::guard('admin')->user();
    }
}
