@extends('web.layouts.app')

@section('title', $template->getTranslation('title', app()->getLocale()))

@section('content')

@php
    $displayOrientation = $template->hasVideoForOrientation('landscape')
        ? 'landscape'
        : ($template->hasVideoForOrientation('portrait') ? 'portrait' : 'square');
@endphp

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

/* VIDEO İZOLASYON */
.video-isolation {
    all: unset;
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100%;
    background: var(--bg-primary);
}

.video-box {
    display: inline-block;
    margin: 0 auto;
}

.video-box.landscape {
    max-width: 800px;
    width: 90vw;
}

.video-box.portrait {
    width: 25vw;
    max-width: 360px;
    min-width: 260px;
}

@media (max-width:768px){
    .video-box.portrait {
        width: 60vw;
    }
}

.video-box video {
    width: auto !important;
    height: auto !important;
    max-width: 100% !important;
    max-height: 80vh !important;
    object-fit: contain !important;
    display: block !important;
    margin: 0 auto !important;
    border-radius: 12px;
}

/* Template Show Page Container */
.container.text-center {
    background: var(--bg-primary);
    color: var(--text-primary);
}

.container.text-center h1 {
    color: var(--text-primary);
    font-weight: bold;
}

/* UPLOAD ALANI */
.upload-area {
    max-width: 400px;
    margin: 30px auto;
    padding: 20px;
    border: 2px solid var(--gold);
    border-radius: 16px;
    text-align: center;
    background: var(--bg-secondary);
}

.upload-area .alert-warning {
    background: rgba(212, 175, 55, 0.1);
    border: 1px solid var(--gold);
    color: var(--text-primary);
    border-radius: 8px;
    padding: 15px;
}

.upload-area .text-muted,
.upload-area small {
    color: var(--text-secondary) !important;
}

.upload-area p {
    color: var(--text-primary);
}

.upload-area .btn-primary {
    background: linear-gradient(135deg, var(--gold), var(--gold-hover));
    border: none;
    color: var(--bg-primary);
    font-weight: 600;
    padding: 10px 30px;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.upload-area .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(212, 175, 55, 0.4);
}

#preview-container {
    margin-top: 15px;
}

#preview-image {
    width: 100%;
    border-radius: 10px;
    border: 2px solid var(--gold);
}

.btn-submit {
    width: 100%;
    margin-top: 15px;
    padding: 12px;
    border-radius: 12px;
    border: none;
    background: linear-gradient(135deg, var(--gold), var(--gold-hover));
    color: var(--bg-primary);
    font-weight: 600;
    transition: all 0.3s ease;
    cursor: pointer;
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(212, 175, 55, 0.4);
}

.custom-file-upload {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}

.file-label {
    display: inline-block;
    padding: 10px 20px;
    background: linear-gradient(135deg, var(--gold), var(--gold-hover));
    color: var(--bg-primary);
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
    margin: 0;
}

.file-label:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(212, 175, 55, 0.4);
}

.file-name {
    color: var(--text-secondary);
    font-size: 14px;
    flex: 1;
}
</style>

<div class="container text-center mt-4">

    <h1 class="mb-4">
        {{ $template->getTranslation('title', app()->getLocale()) }}
    </h1>

    <!-- VIDEO -->
    <div class="video-isolation">
        <div class="video-box {{ $displayOrientation }}">
            <video
                controls
                autoplay
                loop
                muted
                playsinline
                style="width:auto; height:auto; max-width:100%; max-height:80vh; object-fit:contain; display:block; margin:auto;">

                <source src="{{ $template->getVideoUrlForOrientation($displayOrientation) }}" type="video/mp4">
            </video>
        </div>
    </div>

    <!-- 🔥 UPLOAD ALANI GERİ GELDİ -->
    @auth
    <div class="upload-area">

        @php
            $userBalance = \App\Services\TokenService::class;
            $userBalance = app($userBalance)->getBalance(auth()->id());
            $hasEnoughTokens = $userBalance >= $template->token_cost;
        @endphp

        @if(!$hasEnoughTokens)
            <div class="alert alert-warning">
                {{ __('templates.Insufficient tokens. This template requires :cost tokens, your current balance is: :balance', [
                    'cost' => $template->token_cost,
                    'balance' => $userBalance
                ]) }}
            </div>
            <a href="{{ route('packages.index') }}" class="btn btn-primary">
                {{ __('templates.Buy Tokens') }}
            </a>
        @else
            <div class="mb-3">
                <small class="text-muted">
                    {{ __('templates.This template uses :cost tokens. Your current balance: :balance', [
                        'cost' => $template->token_cost,
                        'balance' => $userBalance
                    ]) }}
                </small>
            </div>

            <form method="POST" action="{{ route('generation-requests.store') }}" enctype="multipart/form-data" onsubmit="return confirmSubmit(event)">
                @csrf
                <input type="hidden" name="template_id" value="{{ $template->uuid }}">
                <input type="hidden" name="type" value="template_image">
                <input type="hidden" name="orientation" value="{{ $displayOrientation }}">
                
                <div class="custom-file-upload">
                    <input type="file" name="input_image" id="file-input" accept="image/*" required onchange="previewImage(event)" style="display:none;">
                    <label for="file-input" class="file-label">
                        {{ __('templates.Choose File') }}
                    </label>
                    <span id="file-name" class="file-name">{{ __('templates.No file chosen') }}</span>
                </div>

                <div id="preview-container" style="display:none;">
                    <img id="preview-image">
                </div>

                <button type="submit" class="btn-submit">
                    {{ __('templates.Submit Image') }}
                </button>
            </form>
        @endif

    </div>
    @else
    <div class="upload-area">
        <p>{{ __('templates.Login required to use this template') }}</p>
        <a href="{{ route('login') }}" class="btn btn-primary">
            {{ __('templates.Login Now') }}
        </a>
    </div>
    @endauth

</div>

<script>
function previewImage(e){
    var file = e.target.files[0];
    if (!file) return;

    // Update file name display
    document.getElementById('file-name').textContent = file.name;

    var reader = new FileReader();
    reader.onload = function(x){
        document.getElementById('preview-image').src = x.target.result;
        document.getElementById('preview-container').style.display = 'block';
    };
    reader.readAsDataURL(file);
}

function confirmSubmit(e){
    const cost = {{ $template->token_cost }};
    const message = "{{ __('templates.This action will use :cost tokens. Do you want to continue?', ['cost' => ':cost']) }}";
    return confirm(message.replace(':cost', cost));
}
</script>

@endsection
