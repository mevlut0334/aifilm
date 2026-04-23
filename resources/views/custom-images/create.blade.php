@extends('web.layouts.app')

@section('title', __('custom_images.title_create'))

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('custom_images.title_create') }}</h4>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="alert alert-info">
                        <strong>{{ __('custom_images.cost') }}:</strong> {{ $tokenCost }} {{ __('custom_images.token') }}<br>
                        <strong>{{ __('custom_images.current_balance') }}:</strong> {{ $userBalance }} {{ __('custom_images.token') }}
                    </div>

                    <form method="POST" action="{{ route('custom-images.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="prompt" class="form-label">{{ __('custom_images.prompt_label') }} <span class="text-danger">*</span></label>
                            <textarea 
                                class="form-control @error('prompt') is-invalid @enderror" 
                                id="prompt" 
                                name="prompt" 
                                rows="4" 
                                required
                                placeholder="{{ __('custom_images.prompt_placeholder') }}"
                            >{{ old('prompt') }}</textarea>
                            <small class="form-text text-muted">
                                {{ __('custom_images.prompt_help') }}
                            </small>
                            @error('prompt')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="format" class="form-label">{{ __('custom_images.format_label') }} <span class="text-danger">*</span></label>
                            <select 
                                class="form-select @error('format') is-invalid @enderror" 
                                id="format" 
                                name="format"
                                required
                            >
                                <option value="vertical" {{ old('format', 'vertical') == 'vertical' ? 'selected' : '' }}>{{ __('custom_images.format_vertical') }}</option>
                                <option value="horizontal" {{ old('format') == 'horizontal' ? 'selected' : '' }}>{{ __('custom_images.format_horizontal') }}</option>
                                <option value="square" {{ old('format') == 'square' ? 'selected' : '' }}>{{ __('custom_images.format_square') }}</option>
                            </select>
                            <small class="form-text text-muted">
                                {{ __('custom_images.format_help') }}
                            </small>
                            @error('format')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="reference_images" class="form-label">{{ __('custom_images.reference_images_label') }}</label>
                            <input 
                                type="file" 
                                class="form-control @error('reference_images.*') is-invalid @enderror" 
                                id="reference_images" 
                                name="reference_images[]"
                                accept="image/*"
                                multiple
                            >
                            <small class="form-text text-muted">
                                {{ __('custom_images.reference_images_help') }}
                            </small>
                            @error('reference_images.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div id="selected-files" class="mt-2"></div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('custom-images.index') }}" class="btn btn-secondary">
                                {{ __('custom_images.back') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                {{ __('custom_images.create_button') }} ({{ $tokenCost }} {{ __('custom_images.token') }})
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('reference_images');
    const selectedFilesDiv = document.getElementById('selected-files');
    const selectedImagesText = "{{ __('custom_images.selected_images', ['count' => '']) }}".replace(':count', '{COUNT}');
    
    fileInput.addEventListener('change', function() {
        selectedFilesDiv.innerHTML = '';
        
        if (this.files.length > 0) {
            const container = document.createElement('div');
            container.className = 'alert alert-success';
            container.innerHTML = '<strong>' + selectedImagesText.replace('{COUNT}', this.files.length) + '</strong>';
            
            const row = document.createElement('div');
            row.className = 'row g-2 mt-2';
            
            for (let i = 0; i < this.files.length; i++) {
                const col = document.createElement('div');
                col.className = 'col-md-3 col-sm-4 col-6';
                
                const card = document.createElement('div');
                card.className = 'card';
                
                const img = document.createElement('img');
                img.className = 'card-img-top';
                img.style.height = '150px';
                img.style.objectFit = 'cover';
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    img.src = e.target.result;
                };
                reader.readAsDataURL(this.files[i]);
                
                const cardBody = document.createElement('div');
                cardBody.className = 'card-body p-2';
                cardBody.innerHTML = '<small class="text-muted">' + this.files[i].name + '</small><br><small class="text-muted">' + (this.files[i].size / 1024 / 1024).toFixed(2) + ' MB</small>';
                
                card.appendChild(img);
                card.appendChild(cardBody);
                col.appendChild(card);
                row.appendChild(col);
            }
            
            container.appendChild(row);
            selectedFilesDiv.appendChild(container);
        }
    });
});
</script>
@endsection
