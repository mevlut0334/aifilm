@extends('admin.layouts.app')

@section('title', __('admin.Settings'))

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('admin.General Settings') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form method="POST" action="{{ route('admin.settings.update') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="registration_token_grant" class="form-label">
                                {{ __('admin.Registration Token Grant') }}
                            </label>
                            <input type="number" 
                                   class="form-control @error('registration_token_grant') is-invalid @enderror" 
                                   id="registration_token_grant" 
                                   name="registration_token_grant" 
                                   min="0" 
                                   required
                                   value="{{ old('registration_token_grant', $registrationTokenGrant) }}">
                            <small class="form-text text-muted">
                                Yeni kayıt olan kullanıcılara otomatik olarak verilecek token miktarı.
                            </small>
                            @error('registration_token_grant')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                {{ __('admin.Save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
