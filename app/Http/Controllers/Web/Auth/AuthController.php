<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Auth\LoginRequest;
use App\Http\Requests\Web\Auth\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
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
        $user = $this->authService->register($request);

        Auth::login($user);

        return redirect()->route('home');
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

    public function showForgotPassword(): View
    {
        return view('web.auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => 'required|email']);

        $status = $this->authService->sendResetLink($request->email);

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', __('auth.reset_link_sent'))
            : back()->withErrors(['email' => __('auth.reset_link_failed')]);
    }

    public function showResetPassword(string $token): View
    {
        return view('web.auth.reset-password', ['token' => $token]);
    }

    public function resetPassword(Request $request): RedirectResponse
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
            ? redirect()->route('login')->with('success', __('auth.password_reset_success'))
            : back()->withErrors(['email' => __('auth.' . $status)]);
    }
}
