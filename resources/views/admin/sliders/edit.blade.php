@extends('admin.layouts.app')

@section('title', 'Slider Düzenle')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex align-items-center">
            <a href="{{ route('admin.sliders.index') }}" class="btn btn-outline-secondary me-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
                </svg>
                Geri
            </a>
            <h1 class="h2 mb-0">Slider Düzenle</h1>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Hata!</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.sliders.update', $slider) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="title_tr" class="form-label">Başlık (Türkçe) <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('title_tr') is-invalid @enderror" 
                               id="title_tr" 
                               name="title_tr" 
                               value="{{ old('title_tr', $slider->title['tr'] ?? '') }}" 
                               required>
                        @error('title_tr')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="title_en" class="form-label">Başlık (İngilizce) <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('title_en') is-invalid @enderror" 
                               id="title_en" 
                               name="title_en" 
                               value="{{ old('title_en', $slider->title['en'] ?? '') }}" 
                               required>
                        @error('title_en')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="description_tr" class="form-label">Açıklama (Türkçe) <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('description_tr') is-invalid @enderror" 
                                  id="description_tr" 
                                  name="description_tr" 
                                  rows="4" 
                                  required>{{ old('description_tr', $slider->description['tr'] ?? '') }}</textarea>
                        @error('description_tr')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="description_en" class="form-label">Açıklama (İngilizce) <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('description_en') is-invalid @enderror" 
                                  id="description_en" 
                                  name="description_en" 
                                  rows="4" 
                                  required>{{ old('description_en', $slider->description['en'] ?? '') }}</textarea>
                        @error('description_en')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="button_text_tr" class="form-label">Buton Metni (Türkçe)</label>
                        <input type="text" 
                               class="form-control @error('button_text_tr') is-invalid @enderror" 
                               id="button_text_tr" 
                               name="button_text_tr" 
                               value="{{ old('button_text_tr', $slider->button_text['tr'] ?? '') }}">
                        @error('button_text_tr')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="button_text_en" class="form-label">Buton Metni (İngilizce)</label>
                        <input type="text" 
                               class="form-control @error('button_text_en') is-invalid @enderror" 
                               id="button_text_en" 
                               name="button_text_en" 
                               value="{{ old('button_text_en', $slider->button_text['en'] ?? '') }}">
                        @error('button_text_en')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label for="button_link" class="form-label">Buton Linki</label>
                    <input type="url" 
                           class="form-control @error('button_link') is-invalid @enderror" 
                           id="button_link" 
                           name="button_link" 
                           value="{{ old('button_link', $slider->button_link) }}"
                           placeholder="https://example.com">
                    <small class="form-text text-muted">
                        Dış link ekleyebilirsiniz (ör: https://example.com)
                    </small>
                    @error('button_link')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="image" class="form-label">Slider Görseli</label>
                    @if($slider->image_url)
                        <div class="mb-2">
                            <img src="{{ $slider->image_url }}" 
                                 alt="Mevcut Görsel" 
                                 class="img-thumbnail"
                                 style="max-width: 300px; max-height: 200px; object-fit: cover;">
                        </div>
                    @endif
                    <input type="file" 
                           class="form-control @error('image') is-invalid @enderror" 
                           id="image" 
                           name="image" 
                           accept="image/jpeg,image/jpg,image/png,image/webp">
                    <small class="form-text text-muted">
                        Yeni görsel yüklemek istemiyorsanız boş bırakabilirsiniz. Maksimum dosya boyutu: 5MB
                    </small>
                    @error('image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="order" class="form-label">Sıra</label>
                        <input type="number" 
                               class="form-control @error('order') is-invalid @enderror" 
                               id="order" 
                               name="order" 
                               value="{{ old('order', $slider->order) }}"
                               min="0">
                        <small class="form-text text-muted">
                            Slider'ın gösterim sırası (0'dan başlar)
                        </small>
                        @error('order')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label d-block">Durum</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="is_active" 
                                   name="is_active" 
                                   {{ old('is_active', $slider->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Aktif
                            </label>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-lg" viewBox="0 0 16 16">
                            <path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z"/>
                        </svg>
                        Güncelle
                    </button>
                    <a href="{{ route('admin.sliders.index') }}" class="btn btn-secondary">İptal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
