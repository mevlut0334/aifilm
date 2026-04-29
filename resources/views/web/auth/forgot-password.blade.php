@extends('web.layouts.app')

@section('title', __('auth.Forgot Password'))

@section('content')
<style>
    .forgot-wrapper .card {
        background-color: #121212;
        border: 1px solid #2a2a2a;
        color: #FFFFFF;
    }
    .forgot-wrapper .card-header {
        background-color: #1a1a1a;
        border-bottom: 1px solid #2a2a2a;
        color: #D4AF37;
        font-weight: 600;
    }
    .forgot-wrapper .form-label { color: #BFBFBF; }
    .forgot-wrapper .form-control {
        background-color: #1a1a1a;
        border: 1px solid #2e2e2e;
        color: #FFFFFF;
    }
    .forgot-wrapper .form-control:focus {
        background-color: #1a1a1a;
        border-color: #D4AF37;
        color: #FFFFFF;
        box-shadow: 0 0 0 0.2rem rgba(212, 175, 55, 0.15);
    }
    .forgot-wrapper .form-control::placeholder { color: #6B6B6B; }
    .forgot-wrapper .form-control.is-invalid { border-color: #ef4444; }
    .forgot-wrapper .invalid-feedback { color: #ef4444; }
    .forgot-wrapper .btn-primary {
        background-color: #D4AF37;
        border-color: #D4AF37;
        color: #0B0B0B;
        font-weight: 600;
        transition: background-color 0.2s, border-color 0.2s;
    }
    .forgot-wrapper .btn-primary:hover,
    .forgot-wrapper .btn-primary:focus {
        background-color: #F5D97A;
        border-color: #F5D97A;
        color: #0B0B0B;
    }
    .forgot-wrapper .auth-link {
        color: #D4AF37;
        text-decoration: none;
        font-size: 0.85rem;
    }
    .forgot-wrapper .auth-link:hover {
        color: #F5D97A;
        text-decoration: underline;
    }
</style>

<div class="container forgot-wrapper" style="background-color: #0B0B0B; min-height: 100vh; padding-top: 2rem; padding-bottom: 2rem;">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">{{ __('auth.Forgot Password') }}</div>
                <div class="card-body">

                    @if(session('success'))
                        <div class="alert" style="background-color:#1a2e1a; border:1px solid #2d5a2d; color:#4ade80; border-radius:6px; padding:0.75rem 1rem; margin-bottom:1rem;">
                            {{ session('success') }}
                        </div>
                    @endif

                    <p style="font-size:0.85rem; color:#6B6B6B; margin-bottom:1.25rem;">
                        {{ __('auth.forgot_password_info') }}
                    </p>

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label">{{ __('auth.Email') }}</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                id="email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary w-100">{{ __('auth.Send Reset Link') }}</button>
                    </form>

                    <p class="text-center mt-3 mb-0" style="font-size:0.85rem; color:#6B6B6B;">
                        <a href="{{ route('login') }}" class="auth-link">{{ __('auth.Back to Login') }}</a>
                    </p>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
