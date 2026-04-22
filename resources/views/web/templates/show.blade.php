@extends('web.layouts.app')

@section('title', $template->getTranslation('title', app()->getLocale()))

@section('content')

@php
    $displayOrientation = $template->hasVideoForOrientation('landscape')
        ? 'landscape'
        : ($template->hasVideoForOrientation('portrait') ? 'portrait' : 'square');
@endphp

<style>
/* VIDEO İZOLASYON */
.video-isolation {
    all: unset;
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100%;
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
}

/* UPLOAD ALANI */
.upload-area {
    max-width: 400px;
    margin: 30px auto;
    padding: 20px;
    border: 2px solid var(--bs-border-color);
    border-radius: 16px;
    text-align: center;
}

#preview-container {
    margin-top: 15px;
}

#preview-image {
    width: 100%;
    border-radius: 10px;
}

.btn-submit {
    width: 100%;
    margin-top: 15px;
    padding: 12px;
    border-radius: 12px;
    border: none;
    background: var(--bs-primary);
    color: #fff;
    font-weight: 600;
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

        <form onsubmit="handleUploadSubmit(event)">
            <input type="file" class="form-control" accept="image/*" required onchange="previewImage(event)">

            <div id="preview-container" style="display:none;">
                <img id="preview-image">
            </div>

            <button type="submit" class="btn-submit">
                {{ __('templates.Submit Image') }}
            </button>
        </form>

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

    var reader = new FileReader();
    reader.onload = function(x){
        document.getElementById('preview-image').src = x.target.result;
        document.getElementById('preview-container').style.display = 'block';
    };
    reader.readAsDataURL(file);
}

function handleUploadSubmit(e){
    e.preventDefault();
    alert("{{ __('templates.Template processing will be implemented here') }}");
}
</script>

@endsection
