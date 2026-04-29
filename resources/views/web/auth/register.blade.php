@extends('web.layouts.app')

@section('title', __('auth.Register'))

@section('content')
    <style>
        .register-wrapper .card {
            background-color: #121212;
            border: 1px solid #2a2a2a;
            color: #FFFFFF;
        }

        .register-wrapper .card-header {
            background-color: #1a1a1a;
            border-bottom: 1px solid #2a2a2a;
            color: #D4AF37;
            font-weight: 600;
        }

        .register-wrapper .form-label {
            color: #BFBFBF;
        }

        .register-wrapper .form-control,
        .register-wrapper .form-select {
            background-color: #1a1a1a;
            border: 1px solid #2e2e2e;
            color: #FFFFFF;
        }

        .register-wrapper .form-control:focus,
        .register-wrapper .form-select:focus {
            background-color: #1a1a1a;
            border-color: #D4AF37;
            color: #FFFFFF;
            box-shadow: 0 0 0 0.2rem rgba(212, 175, 55, 0.15);
        }

        .register-wrapper .form-control::placeholder {
            color: #6B6B6B;
        }

        .register-wrapper .form-select option {
            background-color: #1a1a1a;
            color: #FFFFFF;
        }

        .register-wrapper .form-control.is-invalid,
        .register-wrapper .form-select.is-invalid {
            border-color: #ef4444;
        }

        .register-wrapper .invalid-feedback {
            color: #ef4444;
        }

        .register-wrapper .btn-primary {
            background-color: #D4AF37;
            border-color: #D4AF37;
            color: #0B0B0B;
            font-weight: 600;
            transition: background-color 0.2s, border-color 0.2s;
        }

        .register-wrapper .btn-primary:hover,
        .register-wrapper .btn-primary:focus {
            background-color: #F5D97A;
            border-color: #F5D97A;
            color: #0B0B0B;
        }

        .register-wrapper .container {
            background-color: transparent;
        }
    </style>

    <div class="container register-wrapper"
        style="background-color: #0B0B0B; min-height: 100vh; padding-top: 2rem; padding-bottom: 2rem;">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">{{ __('auth.Create Account') }}</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('register') }}">
                            @csrf
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="first_name" class="form-label">{{ __('auth.First Name') }}</label>
                                    <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                        id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                                    @error('first_name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="last_name" class="form-label">{{ __('auth.Last Name') }}</label>
                                    <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                                        id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                                    @error('last_name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">{{ __('auth.Email') }}</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                    id="email" name="email" value="{{ old('email') }}" required>
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-5">
                                    <label for="country_code" class="form-label">{{ __('auth.Country Code') }}</label>
                                    <select class="form-select @error('country_code') is-invalid @enderror"
                                        id="country_code" name="country_code" required>
                                        <option value="">{{ __('auth.Select') }}</option>
                                        @foreach ($countryCodes as $country)
                                            <option value="{{ $country['dial_code'] }}"
                                                @if (old('country_code') == $country['dial_code']) selected @endif>
                                                {{ $country['name'] }} ({{ $country['dial_code'] }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('country_code')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-7">
                                    <label for="phone" class="form-label">{{ __('auth.Phone') }}</label>
                                    <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                        id="phone" name="phone" value="{{ old('phone') }}" required>
                                    @error('phone')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">{{ __('auth.Password') }}</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                    id="password" name="password" required>
                                @error('password')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="password_confirmation"
                                    class="form-label">{{ __('auth.Confirm Password') }}</label>
                                <input type="password" class="form-control" id="password_confirmation"
                                    name="password_confirmation" required>
                            </div>

                            {{-- Consent Note --}}
                            <p
                                style="font-size:0.82rem; color:#6B6B6B; text-align:center; margin-bottom:1rem; line-height:1.6;">
                                {{ __('auth.consent_register') }}
                                <a href="{{ LaravelLocalization::getLocalizedURL(null, route('terms', [], false)) }}"
                                    style="color:#D4AF37;" target="_blank">{{ __('auth.consent_terms') }}</a>
                                {{ __('auth.consent_and') }}
                                <a href="{{ LaravelLocalization::getLocalizedURL(null, route('privacy', [], false)) }}"
                                    style="color:#D4AF37;"
                                    target="_blank">{{ __('auth.consent_privacy') }}</a>{{ __('auth.consent_end') }}
                            </p>

                            <button type="submit" class="btn btn-primary w-100">{{ __('auth.Register') }}</button>
                        </form>
                        <p class="text-center mt-3 mb-0" style="font-size:0.85rem; color:#6B6B6B;">
                            {{ __('auth.Have Account') }}
                            <a href="{{ route('login') }}"
                                style="color:#D4AF37; text-decoration:none; font-size:0.85rem;">{{ __('auth.Login') }}</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
