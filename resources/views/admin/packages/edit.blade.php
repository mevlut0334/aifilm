@extends('admin.layouts.app')

@section('title', __('packages.Edit Package'))

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('packages.Edit Package') }}</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.packages.update', $package->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="title_en" class="form-label">{{ __('packages.Title (EN)') }} *</label>
                            <input type="text" 
                                   class="form-control @error('title_en') is-invalid @enderror" 
                                   id="title_en" 
                                   name="title_en" 
                                   required
                                   value="{{ old('title_en', $package->title['en'] ?? '') }}">
                            @error('title_en')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="title_tr" class="form-label">{{ __('packages.Title (TR)') }}</label>
                            <input type="text" 
                                   class="form-control @error('title_tr') is-invalid @enderror" 
                                   id="title_tr" 
                                   name="title_tr" 
                                   value="{{ old('title_tr', $package->title['tr'] ?? '') }}">
                            @error('title_tr')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description_en" class="form-label">{{ __('packages.Description (EN)') }} *</label>
                            <textarea class="form-control @error('description_en') is-invalid @enderror" 
                                      id="description_en" 
                                      name="description_en" 
                                      rows="3"
                                      required>{{ old('description_en', $package->description['en'] ?? '') }}</textarea>
                            @error('description_en')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description_tr" class="form-label">{{ __('packages.Description (TR)') }}</label>
                            <textarea class="form-control @error('description_tr') is-invalid @enderror" 
                                      id="description_tr" 
                                      name="description_tr" 
                                      rows="3">{{ old('description_tr', $package->description['tr'] ?? '') }}</textarea>
                            @error('description_tr')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="paddle_price_id" class="form-label">{{ __('packages.Paddle Price ID') }} *</label>
                            <input type="text" 
                                   class="form-control @error('paddle_price_id') is-invalid @enderror" 
                                   id="paddle_price_id" 
                                   name="paddle_price_id" 
                                   required
                                   value="{{ old('paddle_price_id', $package->paddle_price_id) }}">
                            @error('paddle_price_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="token_amount" class="form-label">{{ __('packages.Token Amount') }} *</label>
                            <input type="number" 
                                   class="form-control @error('token_amount') is-invalid @enderror" 
                                   id="token_amount" 
                                   name="token_amount" 
                                   min="1" 
                                   required
                                   value="{{ old('token_amount', $package->token_amount) }}">
                            @error('token_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="order" class="form-label">{{ __('packages.Order') }}</label>
                            <input type="number" 
                                   class="form-control @error('order') is-invalid @enderror" 
                                   id="order" 
                                   name="order" 
                                   min="0" 
                                   value="{{ old('order', $package->order) }}">
                            @error('order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" 
                                   class="form-check-input" 
                                   id="is_active" 
                                   name="is_active" 
                                   value="1"
                                   {{ old('is_active', $package->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                {{ __('packages.Is Active') }}
                            </label>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.packages.index') }}" class="btn btn-secondary">
                                {{ __('admin.Cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                {{ __('admin.Update') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
