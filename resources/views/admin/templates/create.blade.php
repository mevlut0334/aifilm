@extends('admin.layouts.app')

@section('title', 'Yeni Template Oluştur')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4>Yeni Template Oluştur</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.templates.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="title_en" class="form-label">Başlık (EN) *</label>
                                <input type="text" 
                                       class="form-control @error('title_en') is-invalid @enderror" 
                                       id="title_en" 
                                       name="title_en" 
                                       required
                                       value="{{ old('title_en') }}">
                                @error('title_en')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="title_tr" class="form-label">Başlık (TR)</label>
                                <input type="text" 
                                       class="form-control @error('title_tr') is-invalid @enderror" 
                                       id="title_tr" 
                                       name="title_tr" 
                                       value="{{ old('title_tr') }}">
                                @error('title_tr')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="description_en" class="form-label">Açıklama (EN)</label>
                                <textarea class="form-control @error('description_en') is-invalid @enderror" 
                                          id="description_en" 
                                          name="description_en" 
                                          rows="3">{{ old('description_en') }}</textarea>
                                @error('description_en')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="description_tr" class="form-label">Açıklama (TR)</label>
                                <textarea class="form-control @error('description_tr') is-invalid @enderror" 
                                          id="description_tr" 
                                          name="description_tr" 
                                          rows="3">{{ old('description_tr') }}</textarea>
                                @error('description_tr')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="token_cost" class="form-label">Token Maliyeti *</label>
                                <input type="number" 
                                       class="form-control @error('token_cost') is-invalid @enderror" 
                                       id="token_cost" 
                                       name="token_cost" 
                                       min="0" 
                                       required
                                       value="{{ old('token_cost', 0) }}">
                                @error('token_cost')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="order" class="form-label">Sıra</label>
                                <input type="number" 
                                       class="form-control @error('order') is-invalid @enderror" 
                                       id="order" 
                                       name="order" 
                                       min="0" 
                                       value="{{ old('order') }}">
                                @error('order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <h5>Videolar</h5>
                            <p class="text-muted small">Her orientation için ayrı video yükleyebilirsiniz. (MP4, MOV, AVI - Max 50MB)</p>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="landscape_video" class="form-label">Landscape Video (16:9)</label>
                                <input type="file" 
                                       class="form-control @error('landscape_video') is-invalid @enderror" 
                                       id="landscape_video" 
                                       name="landscape_video"
                                       accept="video/mp4,video/quicktime,video/x-msvideo">
                                @error('landscape_video')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="portrait_video" class="form-label">Portrait Video (9:16)</label>
                                <input type="file" 
                                       class="form-control @error('portrait_video') is-invalid @enderror" 
                                       id="portrait_video" 
                                       name="portrait_video"
                                       accept="video/mp4,video/quicktime,video/x-msvideo">
                                @error('portrait_video')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="square_video" class="form-label">Square Video (1:1)</label>
                                <input type="file" 
                                       class="form-control @error('square_video') is-invalid @enderror" 
                                       id="square_video" 
                                       name="square_video"
                                       accept="video/mp4,video/quicktime,video/x-msvideo">
                                @error('square_video')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" 
                                   class="form-check-input" 
                                   id="is_active" 
                                   name="is_active" 
                                   value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Aktif
                            </label>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.templates.index') }}" class="btn btn-secondary">
                                İptal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Oluştur
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
