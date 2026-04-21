@extends('web.layouts.app')

@section('title', __('auth.Register'))

@section('content')
<div class="container">
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
                                    @foreach($countryCodes as $country)
                                        <option value="{{ $country['dial_code'] }}" @if(old('country_code') == $country['dial_code']) selected @endif>
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
                            <label for="password_confirmation" class="form-label">{{ __('auth.Confirm Password') }}</label>
                            <input type="password" class="form-control" 
                                id="password_confirmation" name="password_confirmation" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">{{ __('auth.Register') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
