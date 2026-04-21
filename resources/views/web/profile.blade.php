@extends('web.layouts.app')

@section('title', __('auth.Profile'))

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">{{ __('auth.Profile') }}</div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    <form method="POST" action="{{ route('profile') }}">
                        @csrf
                        @method('PUT')
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">{{ __('auth.First Name') }}</label>
                                <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                    id="first_name" name="first_name" value="{{ old('first_name', $user->first_name) }}" required>
                                @error('first_name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">{{ __('auth.Last Name') }}</label>
                                <input type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                    id="last_name" name="last_name" value="{{ old('last_name', $user->last_name) }}" required>
                                @error('last_name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">{{ __('auth.Email') }}</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-5">
                                <label for="country_code" class="form-label">{{ __('auth.Country Code') }}</label>
                                <select class="form-select @error('country_code') is-invalid @enderror" 
                                    id="country_code" name="country_code" required>
                                    @foreach($countryCodes as $country)
                                        <option value="{{ $country['dial_code'] }}" @if($user->country_code == $country['dial_code']) selected @endif>
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
                                    id="phone" name="phone" value="{{ old('phone', $user->phone) }}" required>
                                @error('phone')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">{{ __('auth.Update Profile') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
