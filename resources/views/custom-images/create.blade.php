@extends('web.layouts.app')

@section('title', __('custom_images.title_create'))

@section('content')

<style>
/* 🎨 Renk Paleti */
:root {
    --bg-primary: #0B0B0B;
    --bg-secondary: #121212;
    --gold: #D4AF37;
    --gold-hover: #F5D97A;
    --purple: #7C3AED;
    --blue: #3B82F6;
    --text-primary: #FFFFFF;
    --text-secondary: #BFBFBF;
    --text-passive: #6B6B6B;
}

.container {
    background: var(--bg-primary);
    padding: 40px 0;
}

.card {
    background: var(--bg-secondary);
    border: 2px solid var(--gold);
    border-radius: 16px;
    color: var(--text-primary);
}

.card-header {
    background: var(--bg-secondary);
    border-bottom: 2px solid var(--gold);
    padding: 20px;
}

.card-header h4 {
    color: var(--text-primary);
    margin: 0;
    font-weight: bold;
}

.card-body {
    background: var(--bg-secondary);
    padding: 25px;
}

.alert-info {
    background: rgba(59, 130, 246, 0.1);
    border: 1px solid var(--blue);
    color: var(--text-primary);
    border-radius: 8px;
}

.alert-danger {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid #ef4444;
    color: var(--text-primary);
    border-radius: 8px;
}

.alert-success {
    background: rgba(34, 197, 94, 0.1);
    border: 1px solid #22c55e;
    color: var(--text-primary);
    border-radius: 8px;
}

.form-label {
    color: var(--text-primary);
    font-weight: 600;
    margin-bottom: 8px;
}

.form-control, .form-select {
    background: var(--bg-primary);
    border: 2px solid var(--text-passive);
    color: var(--text-primary);
    border-radius: 8px;
    padding: 10px 15px;
}

.form-control:focus, .form-select:focus {
    background: var(--bg-primary);
    border-color: var(--gold);
    color: var(--text-primary);
    box-shadow: 0 0 0 0.2rem rgba(212, 175, 55, 0.25);
}

.form-control::placeholder {
    color: var(--text-passive);
}

.form-select option {
    background: var(--bg-secondary);
    color: var(--text-primary);
}

.text-muted, .form-text {
    color: var(--text-secondary) !important;
}

.text-danger {
    color: #ef4444 !important;
}

.btn-primary {
    background: linear-gradient(135deg, var(--gold), var(--gold-hover));
    border: none;
    color: var(--bg-primary);
    font-weight: 600;
    padding: 12px 30px;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(212, 175, 55, 0.4);
    color: var(--bg-primary);
}

.btn-secondary {
    background: var(--bg-primary);
    border: 2px solid var(--text-passive);
    color: var(--text-primary);
    font-weight: 600;
    padding: 12px 30px;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn-secondary:hover {
    border-color: var(--gold);
    color: var(--text-primary);
    transform: translateY(-2px);
}

.custom-file-wrapper {
    position: relative;
}

.custom-file-input {
    position: absolute;
    opacity: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
}

.custom-file-label {
    display: flex;
    align-items: center;
    gap: 10px;
    background: var(--bg-primary);
    border: 2px solid var(--text-passive);
    color: var(--text-primary);
    border-radius: 8px;
    padding: 10px 15px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.custom-file-label:hover {
    border-color: var(--gold);
}

.custom-file-button {
    background: linear-gradient(135deg, var(--gold), var(--gold-hover));
    color: var(--bg-primary);
    padding: 8px 20px;
    border-radius: 6px;
    font-weight: 600;
    white-space: nowrap;
}

.custom-file-text {
    color: var(--text-secondary);
    flex: 1;
}

.card-img-top {
    border-radius: 8px 8px 0 0;
}

#selected-files .card {
    background: var(--bg-primary);
    border: 1px solid var(--text-passive);
}

#selected-files .card-body {
    background: var(--bg-primary);
    padding: 10px;
}
</style>

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
                            <div class="custom-file-wrapper">
                                <input 
                                    type="file" 
                                    class="custom-file-input @error('reference_images.*') is-invalid @enderror" 
                                    id="reference_images" 
                                    name="reference_images[]"
                                    accept="image/*"
                                    multiple
                                >
                                <label for="reference_images" class="custom-file-label">
                                    <span class="custom-file-button">{{ __('custom_images.choose_files') }}</span>
                                    <span class="custom-file-text" id="file-label-text">{{ __('custom_images.no_file_chosen') }}</span>
                                </label>
                            </div>
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
    const fileLabelText = document.getElementById('file-label-text');
    const selectedImagesText = "{{ __('custom_images.selected_images', ['count' => '']) }}".replace(':count', '{COUNT}');
    const noFileChosen = "{{ __('custom_images.no_file_chosen') }}";
    
    fileInput.addEventListener('change', function() {
        selectedFilesDiv.innerHTML = '';
        
        // Update label text
        if (this.files.length > 0) {
            fileLabelText.textContent = this.files.length + ' {{ __('custom_images.reference_images_label') }}';
        } else {
            fileLabelText.textContent = noFileChosen;
        }
        
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
