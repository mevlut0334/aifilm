@extends('web.layouts.app')

@section('title', __('auth.Login'))

@section('content')
<style>
    .login-wrapper .card {
        background-color: #121212;
        border: 1px solid #2a2a2a;
        color: #FFFFFF;
    }
    .login-wrapper .card-header {
        background-color: #1a1a1a;
        border-bottom: 1px solid #2a2a2a;
        color: #D4AF37;
        font-weight: 600;
    }
    .login-wrapper .form-label {
        color: #BFBFBF;
    }
    .login-wrapper .form-control {
        background-color: #1a1a1a;
        border: 1px solid #2e2e2e;
        color: #FFFFFF;
    }
    .login-wrapper .form-control:focus {
        background-color: #1a1a1a;
        border-color: #D4AF37;
        color: #FFFFFF;
        box-shadow: 0 0 0 0.2rem rgba(212, 175, 55, 0.15);
    }
    .login-wrapper .form-control::placeholder {
        color: #6B6B6B;
    }
    .login-wrapper .form-control.is-invalid {
        border-color: #ef4444;
    }
    .login-wrapper .invalid-feedback {
        color: #ef4444;
    }
    .login-wrapper .btn-primary {
        background-color: #D4AF37;
        border-color: #D4AF37;
        color: #0B0B0B;
        font-weight: 600;
        transition: background-color 0.2s, border-color 0.2s;
    }
    .login-wrapper .btn-primary:hover,
    .login-wrapper .btn-primary:focus {
        background-color: #F5D97A;
        border-color: #F5D97A;
        color: #0B0B0B;
    }
</style>

<div class="container login-wrapper" style="background-color: #0B0B0B; min-height: 100vh; padding-top: 2rem; padding-bottom: 2rem;">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">{{ __('auth.Login') }}</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label">{{ __('auth.Email') }}</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                id="email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">{{ __('auth.Password') }}</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                id="password" name="password" required>
                            @error('password')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary w-100">{{ __('auth.Login') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
