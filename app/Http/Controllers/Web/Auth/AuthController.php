<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Auth\LoginRequest;
use App\Http\Requests\Web\Auth\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    public function showRegister(): View
    {
        $countryCodes = json_decode(file_get_contents(resource_path('data/country_codes.json')), true);

        return view('web.auth.register', compact('countryCodes'));
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $this->authService->register($request);

        return redirect()->route('login')->with('success', __('auth.registered_successfully'));
    }

    public function showLogin(): View
    {
        return view('web.auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        if (! $this->authService->login($request)) {
            return back()->withErrors(['email' => 'Invalid credentials']);
        }

        return redirect()->intended(route('home'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->authService->logout();
        $request->session()->invalidate();

        return redirect()->route('home');
    }
}
