@extends('web.layouts.app')

@section('title', __('custom_videos.title_create'))

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('custom_videos.title_create') }}</h4>
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
                        <strong>{{ __('custom_videos.current_balance') }}:</strong> {{ $userBalance }} {{ __('custom_videos.token') }}<br>
                        <small>{{ __('custom_videos.cost_note') }}</small>
                    </div>

                    <form method="POST" action="{{ route('custom-videos.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="prompt" class="form-label">{{ __('custom_videos.prompt_label') }} <span class="text-danger">*</span></label>
                            <textarea 
                                class="form-control @error('prompt') is-invalid @enderror" 
                                id="prompt" 
                                name="prompt" 
                                rows="6" 
                                required
                                placeholder="{{ __('custom_videos.prompt_placeholder') }}"
                            >{{ old('prompt') }}</textarea>
                            <small class="form-text text-muted">
                                {{ __('custom_videos.prompt_help_unlimited') }}
                            </small>
                            @error('prompt')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="format" class="form-label">{{ __('custom_videos.format_label') }} <span class="text-danger">*</span></label>
                            <select 
                                class="form-select @error('format') is-invalid @enderror" 
                                id="format" 
                                name="format" 
                                required>
                                <option value="vertical" {{ old('format', 'vertical') === 'vertical' ? 'selected' : '' }}>
                                    {{ __('custom_videos.format_vertical') }} (9:16)
                                </option>
                                <option value="horizontal" {{ old('format') === 'horizontal' ? 'selected' : '' }}>
                                    {{ __('custom_videos.format_horizontal') }} (16:9)
                                </option>
                                <option value="square" {{ old('format') === 'square' ? 'selected' : '' }}>
                                    {{ __('custom_videos.format_square') }} (1:1)
                                </option>
                            </select>
                            <small class="form-text text-muted">
                                {{ __('custom_videos.format_help') }}
                            </small>
                            @error('format')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="input_image" class="form-label">{{ __('custom_videos.input_image_label') }} <span class="badge bg-secondary">{{ __('custom_videos.optional') }}</span></label>
                            <input 
                                type="file" 
                                class="form-control @error('input_image') is-invalid @enderror" 
                                id="input_image" 
                                name="input_image"
                                accept="image/*"
                            >
                            <small class="form-text text-muted">
                                {{ __('custom_videos.input_image_help_optional') }}
                            </small>
                            @error('input_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div id="image-preview" class="mt-2"></div>
                        </div>

                        <div class="mb-3">
                            <label for="reference_images" class="form-label">{{ __('custom_videos.reference_images_label') }} <span class="badge bg-secondary">{{ __('custom_videos.optional') }}</span></label>
                            <input 
                                type="file" 
                                class="form-control @error('reference_images.*') is-invalid @enderror" 
                                id="reference_images" 
                                name="reference_images[]"
                                accept="image/*"
                                multiple
                            >
                            <small class="form-text text-muted">
                                {{ __('custom_videos.reference_images_help') }}
                            </small>
                            @error('reference_images.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div id="reference-preview" class="mt-2"></div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('custom-videos.index') }}" class="btn btn-secondary">
                                {{ __('custom_videos.back') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                {{ __('custom_videos.create_button') }}
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
    // Main input image preview
    const fileInput = document.getElementById('input_image');
    const previewDiv = document.getElementById('image-preview');
    
    fileInput.addEventListener('change', function() {
        previewDiv.innerHTML = '';
        
        if (this.files.length > 0) {
            const file = this.files[0];
            const container = document.createElement('div');
            container.className = 'alert alert-success';
            
            const img = document.createElement('img');
            img.className = 'img-fluid';
            img.style.maxWidth = '300px';
            
            const reader = new FileReader();
            reader.onload = function(e) {
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
            
            const fileInfo = document.createElement('div');
            fileInfo.className = 'mt-2';
            fileInfo.innerHTML = '<small class="text-muted">' + file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)</small>';
            
            container.appendChild(img);
            container.appendChild(fileInfo);
            previewDiv.appendChild(container);
        }
    });

    // Multiple reference images preview
    const referenceInput = document.getElementById('reference_images');
    const referencePreview = document.getElementById('reference-preview');
    
    referenceInput.addEventListener('change', function() {
        referencePreview.innerHTML = '';
        
        if (this.files.length > 0) {
            const container = document.createElement('div');
            container.className = 'row g-2 mt-1';
            
            Array.from(this.files).forEach((file, index) => {
                const col = document.createElement('div');
                col.className = 'col-md-3';
                
                const imgContainer = document.createElement('div');
                imgContainer.className = 'border rounded p-2';
                
                const img = document.createElement('img');
                img.className = 'img-fluid rounded';
                img.style.width = '100%';
                img.style.height = '150px';
                img.style.objectFit = 'cover';
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);
                
                const fileInfo = document.createElement('div');
                fileInfo.className = 'mt-1 text-center';
                fileInfo.innerHTML = '<small class="text-muted">' + (index + 1) + '. ' + file.name + '</small>';
                
                imgContainer.appendChild(img);
                imgContainer.appendChild(fileInfo);
                col.appendChild(imgContainer);
                container.appendChild(col);
            });
            
            referencePreview.appendChild(container);
        }
    });
});
</script>
@endsection
