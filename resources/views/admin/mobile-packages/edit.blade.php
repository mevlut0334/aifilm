@extends('admin.layouts.app')

@section('title', 'Edit Mobile Package')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Edit Mobile Package</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.mobile-packages.update', $package->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="title_en" class="form-label">Title (EN) *</label>
                            <input type="text"
                                   class="form-control @error('title_en') is-invalid @enderror"
                                   id="title_en"
                                   name="title_en"
                                   required
                                   value="{{ old('title_en', is_array($package->title) ? ($package->title['en'] ?? '') : $package->title) }}">
                            @error('title_en')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="title_tr" class="form-label">Title (TR)</label>
                            <input type="text"
                                   class="form-control @error('title_tr') is-invalid @enderror"
                                   id="title_tr"
                                   name="title_tr"
                                   value="{{ old('title_tr', is_array($package->title) ? ($package->title['tr'] ?? '') : '') }}">
                            @error('title_tr')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description_en" class="form-label">Description (EN)</label>
                            <textarea class="form-control @error('description_en') is-invalid @enderror"
                                      id="description_en"
                                      name="description_en"
                                      rows="3">{{ old('description_en', is_array($package->description) ? ($package->description['en'] ?? '') : $package->description) }}</textarea>
                            @error('description_en')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description_tr" class="form-label">Description (TR)</label>
                            <textarea class="form-control @error('description_tr') is-invalid @enderror"
                                      id="description_tr"
                                      name="description_tr"
                                      rows="3">{{ old('description_tr', is_array($package->description) ? ($package->description['tr'] ?? '') : '') }}</textarea>
                            @error('description_tr')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="token_amount" class="form-label">Token Amount *</label>
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

                        <hr>
                        <p class="text-muted fw-bold">Mobile Store Product IDs</p>

                        <div class="mb-3">
                            <label for="ios_product_id" class="form-label">
                                <i class="bi bi-apple"></i> iOS Product ID
                            </label>
                            <input type="text"
                                   class="form-control @error('ios_product_id') is-invalid @enderror"
                                   id="ios_product_id"
                                   name="ios_product_id"
                                   placeholder="com.aifilm.subscription.monthly"
                                   value="{{ old('ios_product_id', $package->ios_product_id) }}">
                            @error('ios_product_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">App Store Connect → Subscriptions → Product ID</small>
                        </div>

                        <div class="mb-3">
                            <label for="android_product_id" class="form-label">
                                <i class="bi bi-android2"></i> Android Product ID
                            </label>
                            <input type="text"
                                   class="form-control @error('android_product_id') is-invalid @enderror"
                                   id="android_product_id"
                                   name="android_product_id"
                                   placeholder="aifilm_subscription_monthly"
                                   value="{{ old('android_product_id', $package->android_product_id) }}">
                            @error('android_product_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Google Play Console → Subscriptions → Product ID</small>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <label for="order" class="form-label">Order</label>
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
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.mobile-packages.index') }}" class="btn btn-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
