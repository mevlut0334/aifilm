@extends('web.layouts.app')

@section('title', 'Templates')

@section('content')

<div class="templates-container">
    <div id="masonry-grid">

        @foreach($templates as $template)
            @php
                $previewOrientation = $selectedOrientation
                    ?? ($template->hasVideoForOrientation('landscape') ? 'landscape'
                    : ($template->hasVideoForOrientation('portrait') ? 'portrait' : 'square'));

                $aspectRatio = match($previewOrientation) {
                    'landscape' => '16 / 9',
                    'portrait'  => '9 / 16',
                    'square'    => '1 / 1',
                    default     => '16 / 9',
                };
            @endphp

            <a href="@guest {{ route('login') }} @else {{ route('templates.show', $template->uuid) }} @endguest">
                <div class="template-card">

                    <video muted loop playsinline preload="metadata"
                           style="aspect-ratio: {{ $aspectRatio }};"
                           onmouseenter="this.play()"
                           onmouseleave="this.pause(); this.currentTime=0;">
                        <source src="{{ $template->getVideoUrlForOrientation($previewOrientation) }}" type="video/mp4">
                    </video>

                </div>
            </a>
        @endforeach

    </div>

    <div id="loading" style="text-align:center; padding:20px; display:none;">
        {{ __('templates.Loading...') }}
    </div>
</div>

{{-- ================= CSS ================= --}}
<style>
.templates-container {
    max-width: 1600px;
    margin: 0 auto;
}

/* Kart */
.template-card {
    width: calc(50% - 6px);
    margin-bottom: 6px;
    border-radius: 10px;
    overflow: hidden;
}

/* Tablet */
@media (min-width: 768px) {
    .template-card {
        width: calc(25% - 8px);
        margin-bottom: 8px;
    }
}

/* Desktop */
@media (min-width: 1280px) {
    .template-card {
        width: calc(16.66% - 10px);
        margin-bottom: 10px;
    }
}

.template-card video {
    width: 100%;
    height: auto;
    display: block;
}
</style>

{{-- ================= JS ================= --}}
<script src="https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js"></script>

<script>
let page = 2;
let loading = false;
let hasMore = true;

document.addEventListener("DOMContentLoaded", function () {

    const grid = document.querySelector('#masonry-grid');

    const msnry = new Masonry(grid, {
        itemSelector: '.template-card',
        percentPosition: true,
        gutter: 10
    });

    // video yüklenince layout fix
    function bindVideoEvents(scope=document) {
        scope.querySelectorAll('video').forEach(video => {
            video.addEventListener('loadeddata', () => {
                msnry.layout();
            });
        });
    }

    bindVideoEvents();

    // 🔥 INFINITE SCROLL
    window.addEventListener('scroll', () => {

        if (loading || !hasMore) return;

        if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 300) {
            loadMore();
        }

    });

    function loadMore() {
        loading = true;
        document.getElementById('loading').style.display = 'block';

        fetch(`?page=${page}`)
            .then(res => res.text())
            .then(html => {

                let temp = document.createElement('div');
                temp.innerHTML = html;

                let items = temp.querySelectorAll('.template-card');

                if (items.length === 0) {
                    hasMore = false;
                    document.getElementById('loading').innerText = "{{ __('templates.Done') }}";
                    return;
                }

                items.forEach(el => grid.appendChild(el));

                msnry.appended(items);
                bindVideoEvents(temp);

                page++;
                loading = false;
                document.getElementById('loading').style.display = 'none';
            })
            .catch(() => {
                loading = false;
            });
    }

});
</script>

@endsection
