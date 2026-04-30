@extends('web.layouts.app')

@section('title', __('home.title'))

@section('meta')
    <meta name="description" content="{{ __('home.meta_description') }}">
    <meta name="keywords" content="{{ __('home.meta_keywords') }}">
    <meta name="robots" content="index, follow">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:locale" content="{{ app()->getLocale() == 'tr' ? 'tr_TR' : 'en_US' }}">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:title" content="{{ config('app.name') }} - {{ __('home.meta_og_title') }}">
    <meta property="og:description" content="{{ __('home.meta_og_desc') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ config('app.name') }} - {{ __('home.meta_tw_title') }}">
    <meta name="twitter:description" content="{{ __('home.meta_tw_desc') }}">
@endsection

@section('content')
    <style>
        :root {
            --bg-primary: #0B0B0B;
            --bg-secondary: #121212;
            --gold: #D4AF37;
            --gold-hover: #F5D97A;
            --text-primary: #FFFFFF;
            --text-secondary: #BFBFBF;
        }

        /* Slider */
        .hero-slider {
            position: relative;
            width: 100%;
            max-width: 100%;
            height: auto;
            min-height: 300px;
            overflow: hidden;
            background: var(--bg-secondary);
            margin: 0;
            padding: 0;
        }

        .slider-wrapper {
            position: relative;
            width: 100%;
            max-width: 100%;
            padding-bottom: 35%;
            overflow: hidden;
        }

        .slide {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            opacity: 0;
            transition: opacity 0.8s ease-in-out;
            display: flex;
            align-items: center;
            overflow: hidden;
        }

        .slide.active { opacity: 1; z-index: 1; }

        .slide-bg {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            object-fit: cover;
            filter: brightness(0.5);
        }

        .slide-content {
            position: relative;
            z-index: 2;
            max-width: 800px;
            width: 100%;
            padding: 0 2rem;
        }

        .slide-title {
            font-size: clamp(1.5rem, 4vw, 3.5rem);
            font-weight: bold;
            color: var(--text-primary);
            margin-bottom: clamp(0.8rem, 2vw, 1.5rem);
            text-shadow: 2px 2px 8px rgba(0,0,0,0.8);
            animation: slideInFromLeft 0.8s ease-out;
        }

        .slide-description {
            font-size: clamp(0.9rem, 1.8vw, 1.25rem);
            color: var(--text-secondary);
            margin-bottom: clamp(1rem, 2.5vw, 2rem);
            line-height: 1.6;
            text-shadow: 1px 1px 4px rgba(0,0,0,0.8);
            animation: slideInFromLeft 0.8s ease-out 0.2s both;
        }

        .slide-btn {
            display: inline-block;
            padding: clamp(0.7rem, 1.5vw, 1rem) clamp(1.5rem, 3vw, 2.5rem);
            background: linear-gradient(135deg, var(--gold), var(--gold-hover));
            color: var(--bg-primary);
            text-decoration: none;
            font-weight: 600;
            font-size: clamp(0.9rem, 1.5vw, 1.1rem);
            border-radius: 50px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(212,175,55,0.4);
            animation: slideInFromLeft 0.8s ease-out 0.4s both;
        }

        .slide-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(212,175,55,0.6);
            color: var(--bg-primary);
        }

        @keyframes slideInFromLeft {
            from { opacity: 0; transform: translateX(-50px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        .slider-dots {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
            display: flex;
            gap: 12px;
        }

        .slider-dot {
            width: 12px; height: 12px;
            border-radius: 50%;
            background: rgba(255,255,255,0.4);
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .slider-dot.active {
            background: var(--gold);
            transform: scale(1.3);
            border-color: var(--gold-hover);
        }

        .slider-dot:hover { background: var(--gold-hover); }

        @media (max-width: 768px) {
            .hero-slider { min-height: 250px; }
            .slider-wrapper { padding-bottom: 40%; }
            .slide-content { padding: 0 1.5rem; }
        }

        @media (max-width: 576px) {
            .hero-slider { min-height: 200px; }
            .slider-wrapper { padding-bottom: 45%; }
            .slide-content { padding: 0 1rem; }
        }

        /* Navigation Cards */
        .nav-cards-section { background: var(--bg-primary); padding: 60px 0; }

        .nav-cards-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .nav-card {
            background: var(--bg-secondary);
            border-radius: 12px;
            padding: 40px 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            min-height: 220px;
        }

        .nav-card:hover {
            transform: translateY(-5px);
            border-color: var(--gold);
            box-shadow: 0 8px 24px rgba(212,175,55,0.2);
        }

        .nav-card-title { color: var(--text-primary); font-size: 1.5rem; font-weight: bold; margin: 0; }

        @media (max-width: 992px) {
            .nav-cards-container { gap: 10px; padding: 0 15px; }
            .nav-card { padding: 30px 15px; min-height: 180px; }
            .nav-card-title { font-size: 1.3rem; }
        }

        @media (max-width: 768px) {
            .nav-cards-section { padding: 40px 0; }
            .nav-cards-container { gap: 8px; padding: 0 12px; }
            .nav-card { padding: 25px 12px; min-height: 150px; }
            .nav-card-title { font-size: 1.1rem; }
        }

        @media (max-width: 576px) {
            .nav-cards-section { padding: 30px 0; }
            .nav-cards-container { gap: 6px; padding: 0 8px; }
            .nav-card { padding: 20px 8px; min-height: 120px; }
            .nav-card-title { font-size: 0.9rem; }
        }

        @media (max-width: 375px) {
            .nav-cards-container { gap: 5px; padding: 0 5px; }
            .nav-card { padding: 15px 5px; min-height: 100px; }
            .nav-card-title { font-size: 0.8rem; }
        }

        /* Templates Section */
        .templates-section { background: var(--bg-primary); padding: 60px 0 80px 0; }
        .templates-container { max-width: 1600px; margin: 0 auto; padding: 0 20px; }

        /* Masonry grid sizer */
        .grid-sizer,
        .template-link {
            width: calc(50% - 5px);
        }

        .gutter-sizer { width: 10px; }

        .template-link {
            text-decoration: none;
            display: block;
            margin-bottom: 10px;
        }

        .template-card {
            width: 100%;
            border-radius: 10px;
            overflow: hidden;
            background: var(--bg-secondary);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .template-card:hover {
            transform: translateY(-5px);
            border-color: var(--gold);
            box-shadow: 0 8px 24px rgba(212,175,55,0.2);
        }

        /* Aspect ratio wrapper */
        .video-ratio-box {
            position: relative;
            width: 100%;
            overflow: hidden;
            background: var(--bg-secondary);
        }

        .video-ratio-box video {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            object-fit: cover;
            display: block;
            -webkit-transform: translateZ(0);
            transform: translateZ(0);
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
        }

        @media (min-width: 768px) {
            .templates-section { padding: 70px 0 90px 0; }
            .grid-sizer,
            .template-link { width: calc(25% - 7.5px); }
        }

        @media (min-width: 1280px) {
            .templates-section { padding: 80px 0 100px 0; }
            .grid-sizer,
            .template-link { width: calc(16.66% - 8.33px); }
        }

        @media (max-width: 576px) {
            .templates-section { padding: 40px 0 60px 0; }
            .templates-container { padding: 0 15px; }
        }
    </style>

    <!-- Section 1: Hero Slider -->
    @if ($sliders->count() > 0)
        <section class="hero-slider">
            <div class="slider-wrapper">
                @foreach ($sliders as $index => $slider)
                    <div class="slide {{ $index === 0 ? 'active' : '' }}" data-slide="{{ $index }}">
                        <img src="{{ $slider->image_url }}" alt="Slider Image" class="slide-bg">
                        <div class="container">
                            <div class="slide-content">
                                <h1 class="slide-title">
                                    {{ $slider->title[app()->getLocale()] ?? ($slider->title['en'] ?? '') }}
                                </h1>
                                <p class="slide-description">
                                    {{ $slider->description[app()->getLocale()] ?? ($slider->description['en'] ?? '') }}
                                </p>
                                @if ($slider->button_link && $slider->button_text)
                                    <a href="{{ $slider->button_link }}" class="slide-btn" target="_blank" rel="noopener">
                                        {{ $slider->button_text[app()->getLocale()] ?? ($slider->button_text['en'] ?? __('home.learn_more')) }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($sliders->count() > 1)
                <div class="slider-dots">
                    @foreach ($sliders as $index => $slider)
                        <div class="slider-dot {{ $index === 0 ? 'active' : '' }}" data-slide="{{ $index }}"></div>
                    @endforeach
                </div>
            @endif
        </section>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const slides = document.querySelectorAll('.slide');
                const dots   = document.querySelectorAll('.slider-dot');
                let currentSlide = 0;
                const totalSlides = slides.length;

                if (totalSlides <= 1) return;

                function showSlide(index) {
                    slides.forEach(s => s.classList.remove('active'));
                    dots.forEach(d => d.classList.remove('active'));
                    slides[index].classList.add('active');
                    dots[index].classList.add('active');
                }

                function nextSlide() {
                    currentSlide = (currentSlide + 1) % totalSlides;
                    showSlide(currentSlide);
                }

                let slideInterval = setInterval(nextSlide, 5000);

                dots.forEach((dot, index) => {
                    dot.addEventListener('click', function() {
                        clearInterval(slideInterval);
                        currentSlide = index;
                        showSlide(currentSlide);
                        slideInterval = setInterval(nextSlide, 5000);
                    });
                });
            });
        </script>
    @endif

    <!-- Section 2: Navigation Cards -->
    <section class="nav-cards-section">
        <div class="nav-cards-container">
            <a href="{{ route('custom-images.create') }}" class="nav-card">
                <h3 class="nav-card-title">{{ __('home.nav_images') }}</h3>
            </a>
            <a href="{{ route('custom-videos.create') }}" class="nav-card">
                <h3 class="nav-card-title">{{ __('home.nav_videos') }}</h3>
            </a>
        </div>
    </section>

    <!-- Section 3: Templates Grid -->
    <section class="templates-section">
        <div class="templates-container">
            <div id="masonry-grid">
                {{-- Masonry sizer elementleri --}}
                <div class="grid-sizer"></div>
                <div class="gutter-sizer"></div>

                @foreach ($templates as $template)
                    @php
                        $previewOrientation = $template->hasVideoForOrientation('landscape')
                            ? 'landscape'
                            : ($template->hasVideoForOrientation('portrait') ? 'portrait' : 'square');

                        $paddingBottom = match ($previewOrientation) {
                            'landscape' => '56.25%',
                            'portrait'  => '177.78%',
                            'square'    => '100%',
                            default     => '56.25%',
                        };
                    @endphp

                    <a href="@guest {{ route('login') }} @else {{ route('templates.show', $template->uuid) }} @endguest"
                       class="template-link">
                        <div class="template-card">
                            <div class="video-ratio-box" style="padding-bottom: {{ $paddingBottom }};">
                                <video
                                    muted
                                    loop
                                    playsinline
                                    preload="none"
                                    @if($template->poster_url) poster="{{ $template->poster_url }}" @endif
                                    data-src="{{ $template->getVideoUrlForOrientation($previewOrientation) }}"
                                    onmouseenter="startVideo(this)"
                                    onmouseleave="stopVideo(this)"
                                    ontouchstart="startVideo(this)"
                                    ontouchend="stopVideo(this)">
                                </video>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <script src="https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js"></script>

    <script>
        function startVideo(video) {
            if (!video.dataset.src) return;
            if (!video.src) video.src = video.dataset.src;
            video.play().catch(() => {});
        }

        function stopVideo(video) {
            video.pause();
            video.currentTime = 0;
        }

        document.addEventListener("DOMContentLoaded", function () {
            const grid = document.querySelector('#masonry-grid');
            if (!grid) return;

            const msnry = new Masonry(grid, {
                itemSelector: '.template-link',
                columnWidth: '.grid-sizer',
                gutter: '.gutter-sizer',
                percentPosition: true,
            });

            // Poster yüklenince masonry yeniden hesapla
            grid.querySelectorAll('video').forEach(function (video) {
                if (video.poster) {
                    const img = new Image();
                    img.onload = function () { msnry.layout(); };
                    img.src = video.poster;
                }
            });

            // IntersectionObserver: görünüme girince lazy load
            const observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    const video = entry.target;
                    if (entry.isIntersecting && !video.src && video.dataset.src) {
                        video.src = video.dataset.src;
                        video.addEventListener('loadeddata', function () {
                            msnry.layout();
                        }, { once: true });
                    }
                });
            }, { rootMargin: '200px 0px' });

            grid.querySelectorAll('video').forEach(function (v) {
                observer.observe(v);
            });

            requestAnimationFrame(function () {
                requestAnimationFrame(function () { msnry.layout(); });
            });
        });
    </script>
@endsection
