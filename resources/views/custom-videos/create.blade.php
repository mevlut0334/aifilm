@extends('web.layouts.app')

@section('title', __('custom_videos.title_create'))

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

        .form-control,
        .form-select {
            background: var(--bg-primary);
            border: 2px solid var(--text-passive);
            color: var(--text-primary);
            border-radius: 8px;
            padding: 10px 15px;
        }

        .form-control:focus,
        .form-select:focus {
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

        .text-muted,
        .form-text {
            color: var(--text-secondary) !important;
        }

        .text-danger {
            color: #ef4444 !important;
        }

        .badge.bg-secondary {
            background: var(--text-passive) !important;
            color: var(--text-primary);
            padding: 4px 10px;
            border-radius: 6px;
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

        #image-preview .img-fluid,
        #reference-preview .img-fluid {
            border-radius: 8px;
            border: 2px solid var(--gold);
        }

        #reference-preview .border {
            border-color: var(--text-passive) !important;
            background: var(--bg-primary);
        }
    </style>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4>{{ __('custom_videos.title_create') }}</h4>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="alert alert-info">
                            <strong>{{ __('custom_videos.current_balance') }}:</strong> {{ $userBalance }}
                            {{ __('custom_videos.token') }}<br>
                            <small>{{ __('custom_videos.cost_note') }}</small>
                        </div>

                        <form method="POST" action="{{ route('custom-videos.store') }}" enctype="multipart/form-data">
                            @csrf

                            <div class="mb-3">
                                <label for="prompt" class="form-label">{{ __('custom_videos.prompt_label') }} <span
                                        class="text-danger">*</span></label>
                                <textarea class="form-control @error('prompt') is-invalid @enderror" id="prompt" name="prompt" rows="6"
                                    required placeholder="{{ __('custom_videos.prompt_placeholder') }}">{{ old('prompt') }}</textarea>
                                <small class="form-text text-muted">
                                    {{ __('custom_videos.prompt_help_unlimited') }}
                                </small>
                                @error('prompt')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="format" class="form-label">{{ __('custom_videos.format_label') }} <span
                                        class="text-danger">*</span></label>
                                <select class="form-select @error('format') is-invalid @enderror" id="format"
                                    name="format" required>
                                    <option value="vertical"
                                        {{ old('format', 'vertical') === 'vertical' ? 'selected' : '' }}>
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
                                <label for="input_image" class="form-label">{{ __('custom_videos.input_image_label') }}
                                    <span class="badge bg-secondary">{{ __('custom_videos.optional') }}</span></label>
                                <div class="custom-file-wrapper">
                                    <input type="file"
                                        class="custom-file-input @error('input_image') is-invalid @enderror"
                                        id="input_image" name="input_image" accept="image/*">
                                    <label for="input_image" class="custom-file-label">
                                        <span class="custom-file-button">{{ __('custom_videos.choose_file') }}</span>
                                        <span class="custom-file-text"
                                            id="input-file-label">{{ __('custom_videos.no_file_chosen') }}</span>
                                    </label>
                                </div>
                                <small class="form-text text-muted">
                                    {{ __('custom_videos.input_image_help_optional') }}
                                </small>
                                @error('input_image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div id="image-preview" class="mt-2"></div>
                            </div>

                            <div class="mb-3">
                                <label for="reference_images"
                                    class="form-label">{{ __('custom_videos.reference_images_label') }} <span
                                        class="badge bg-secondary">{{ __('custom_videos.optional') }}</span></label>
                                <div class="custom-file-wrapper">
                                    <input type="file"
                                        class="custom-file-input @error('reference_images.*') is-invalid @enderror"
                                        id="reference_images" name="reference_images[]" accept="image/*" multiple>
                                    <label for="reference_images" class="custom-file-label">
                                        <span class="custom-file-button">{{ __('custom_videos.choose_files') }}</span>
                                        <span class="custom-file-text"
                                            id="reference-file-label">{{ __('custom_videos.no_file_chosen') }}</span>
                                    </label>
                                </div>
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/browser-image-compression/2.0.2/browser-image-compression.min.js">
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const noFileChosen = "{{ __('custom_videos.no_file_chosen') }}";
            const compressionOptions = {
                maxSizeMB: 0.5,
                maxWidthOrHeight: 1920,
                useWebWorker: true,
                fileType: 'image/jpeg',
            };

            // Ana görsel
            const fileInput = document.getElementById('input_image');
            const previewDiv = document.getElementById('image-preview');
            const inputFileLabel = document.getElementById('input-file-label');

            fileInput.addEventListener('change', async function() {
                previewDiv.innerHTML = '';
                if (this.files.length === 0) {
                    inputFileLabel.textContent = noFileChosen;
                    return;
                }

                inputFileLabel.textContent = '⏳ Hazırlanıyor...';
                const file = this.files[0];

                try {
                    const compressed = await imageCompression(file, compressionOptions);
                    const renamedFile = new File([compressed], file.name, {
                        type: 'image/jpeg'
                    });
                    const dt = new DataTransfer();
                    dt.items.add(renamedFile);
                    fileInput.files = dt.files;

                    inputFileLabel.textContent = file.name;
                    const container = document.createElement('div');
                    container.className = 'alert alert-success';
                    const img = document.createElement('img');
                    img.className = 'img-fluid';
                    img.style.maxWidth = '300px';
                    img.src = await imageCompression.getDataUrlFromFile(renamedFile);
                    const fileInfo = document.createElement('div');
                    fileInfo.className = 'mt-2';
                    fileInfo.innerHTML = '<small class="text-muted">' + file.name + ' (' + (renamedFile
                        .size / 1024 / 1024).toFixed(2) + ' MB)</small>';
                    container.appendChild(img);
                    container.appendChild(fileInfo);
                    previewDiv.appendChild(container);
                } catch (err) {
                    inputFileLabel.textContent = file.name;
                }
            });

            // Referans görseller
            const referenceInput = document.getElementById('reference_images');
            const referencePreview = document.getElementById('reference-preview');
            const referenceFileLabel = document.getElementById('reference-file-label');

            referenceInput.addEventListener('change', async function() {
                referencePreview.innerHTML = '';
                if (this.files.length === 0) {
                    referenceFileLabel.textContent = noFileChosen;
                    return;
                }

                referenceFileLabel.textContent = '⏳ Hazırlanıyor...';
                const dt = new DataTransfer();
                const container = document.createElement('div');
                container.className = 'row g-2 mt-1';

                for (let i = 0; i < this.files.length; i++) {
                    const file = this.files[i];
                    try {
                        const compressed = await imageCompression(file, compressionOptions);
                        const renamedFile = new File([compressed], file.name, {
                            type: 'image/jpeg'
                        });
                        dt.items.add(renamedFile);

                        const col = document.createElement('div');
                        col.className = 'col-md-3';
                        const imgContainer = document.createElement('div');
                        imgContainer.className = 'border rounded p-2';
                        const img = document.createElement('img');
                        img.className = 'img-fluid rounded';
                        img.style.width = '100%';
                        img.style.height = '150px';
                        img.style.objectFit = 'cover';
                        img.src = await imageCompression.getDataUrlFromFile(renamedFile);
                        const fileInfo = document.createElement('div');
                        fileInfo.className = 'mt-1 text-center';
                        fileInfo.innerHTML = '<small class="text-muted">' + (i + 1) + '. ' + file.name +
                            '</small>';
                        imgContainer.appendChild(img);
                        imgContainer.appendChild(fileInfo);
                        col.appendChild(imgContainer);
                        container.appendChild(col);
                    } catch (err) {
                        dt.items.add(file);
                    }
                }

                referenceInput.files = dt.files;
                referenceFileLabel.textContent = dt.files.length +
                    ' {{ __('custom_videos.reference_images_label') }}';
                referencePreview.appendChild(container);
            });
        });
    </script>
@endsection
