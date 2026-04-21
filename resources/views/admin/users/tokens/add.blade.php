@extends('admin.layouts.app')

@section('title', __('tokens.Add Tokens'))

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('tokens.Add Tokens') }}</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>User:</strong> {{ $user->first_name }} {{ $user->last_name }} ({{ $user->email }})
                    </div>
                    <div class="mb-3">
                        <strong>{{ __('tokens.current_balance') }}:</strong>
                        <span class="badge bg-primary">{{ $balance }} {{ __('packages.tokens') }}</span>
                    </div>

                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form method="POST" action="{{ route('admin.users.tokens.add.post', $user->id) }}">
                        @csrf

                        <div class="mb-3">
                            <label for="amount" class="form-label">{{ __('tokens.Amount') }} *</label>
                            <input type="number" 
                                   class="form-control @error('amount') is-invalid @enderror" 
                                   id="amount" 
                                   name="amount" 
                                   min="1" 
                                   required
                                   value="{{ old('amount') }}">
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="note" class="form-label">{{ __('tokens.Note') }}</label>
                            <textarea class="form-control @error('note') is-invalid @enderror" 
                                      id="note" 
                                      name="note" 
                                      rows="3">{{ old('note') }}</textarea>
                            @error('note')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                                {{ __('admin.Cancel') }}
                            </a>
                            <button type="submit" class="btn btn-success">
                                {{ __('tokens.add') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
