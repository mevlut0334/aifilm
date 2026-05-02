@extends('web.layouts.app')

@section('title', __('templates.swipe_title'))

@section('content')
<style>
    :root {
        --bg-primary: #0B0B0B;
        --gold: #D4AF37;
        --gold-hover: #F5D97A;
        --text-primary: #FFFFFF;
        --text-secondary: #BFBFBF;
    }

    body, html {
        overflow: hidden;
    }

    .swipe-container {
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background: var(--bg-primary);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 999;
    }

    .swipe-viewport {
        position: relative;
        width: 100%;
        height: 100%;
        overflow: hidden;
    }

    @media (min-width: 768px) {
        .swipe-viewport {
            width: 420px;
            height: 80vh;
            max-height: 800px;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 0 60px rgba(0,0,0,0.8);
        }
    }

    .swipe-slide {
        position: absolute;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background: #000;
        transition: transform 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }

    /* Poster img — video hazır olana kadar gösterilir */
    .swipe-slide .slide-poster {
        position: absolute;
        top: 0; left: 0;
        width: 100%; height: 100%;
        object-fit: cover;
        display: block;
        z-index: 1;
        transition: opacity 0.3s ease;
    }

    .swipe-slide video {
        position: absolute;
        top: 0; left: 0;
        width: 100%; height: 100%;
        object-fit: cover;
        display: block;
        z-index: 2;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    /* Video oynatılınca poster gizlenir */
    .swipe-slide.playing video   { opacity: 1; }
    .swipe-slide.playing .slide-poster { opacity: 0; }

    .swipe-slide::after {
        content: '';
        position: absolute;
        bottom: 0; left: 0;
        width: 100%;
        height: 40%;
        background: linear-gradient(to top, rgba(0,0,0,0.85), transparent);
        pointer-events: none;
        z-index: 3;
    }

    /* Skeleton / yükleniyor göstergesi */
    .slide-loading-indicator {
        position: absolute;
        top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        z-index: 4;
        opacity: 0;
        transition: opacity 0.2s ease;
        pointer-events: none;
    }

    .swipe-slide.buffering .slide-loading-indicator { opacity: 1; }

    .loading-spinner {
        width: 40px; height: 40px;
        border: 3px solid rgba(255,255,255,0.2);
        border-top-color: var(--gold);
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin { to { transform: rotate(360deg); } }

    /* Geri butonu */
    .swipe-back-btn {
        position: absolute;
        top: 16px; left: 16px;
        z-index: 100;
        background: rgba(0,0,0,0.5);
        border: none;
        border-radius: 50%;
        width: 44px; height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        transition: background 0.2s;
        text-decoration: none;
    }

    .swipe-back-btn:hover { background: rgba(212,175,55,0.3); }

    .swipe-back-btn svg {
        width: 22px; height: 22px;
        fill: none;
        stroke: #fff;
        stroke-width: 2.5;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .swipe-title {
        position: absolute;
        bottom: 90px;
        left: 16px; right: 80px;
        z-index: 10;
        color: var(--text-primary);
        font-size: 1.1rem;
        font-weight: 700;
        text-shadow: 0 2px 8px rgba(0,0,0,0.8);
        line-height: 1.4;
    }

    .swipe-use-btn {
        position: absolute;
        bottom: 30px; right: 16px;
        z-index: 10;
        padding: 12px 22px;
        background: linear-gradient(135deg, var(--gold), var(--gold-hover));
        color: #0B0B0B;
        font-weight: 700;
        font-size: 0.95rem;
        border-radius: 50px;
        text-decoration: none;
        box-shadow: 0 4px 18px rgba(212,175,55,0.5);
        transition: transform 0.2s, box-shadow 0.2s;
        white-space: nowrap;
    }

    .swipe-use-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 24px rgba(212,175,55,0.7);
        color: #0B0B0B;
    }

    .swipe-counter {
        position: absolute;
        top: 16px; right: 16px;
        z-index: 100;
        background: rgba(0,0,0,0.5);
        color: var(--text-primary);
        font-size: 0.8rem;
        font-weight: 600;
        padding: 5px 12px;
        border-radius: 20px;
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
    }

    .swipe-hint {
        position: absolute;
        bottom: 100px; left: 50%;
        transform: translateX(-50%);
        z-index: 10;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        opacity: 1;
        transition: opacity 0.5s ease;
        pointer-events: none;
    }

    .swipe-hint.hidden { opacity: 0; }

    .swipe-hint svg {
        width: 28px; height: 28px;
        stroke: rgba(255,255,255,0.7);
        fill: none;
        stroke-width: 2;
        animation: bounceUp 1.2s infinite;
    }

    .swipe-hint span { color: rgba(255,255,255,0.7); font-size: 0.75rem; }

    @keyframes bounceUp {
        0%, 100% { transform: translateY(0); }
        50%       { transform: translateY(-8px); }
    }

    /* ── Ses (mute) butonu ── */
    .swipe-mute-btn {
        position: absolute;
        bottom: 30px; left: 16px;
        z-index: 100;
        background: rgba(0,0,0,0.5);
        border: none;
        border-radius: 50%;
        width: 44px; height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        transition: background 0.2s;
        outline: none;
    }

    .swipe-mute-btn:hover { background: rgba(212,175,55,0.3); }

    .swipe-mute-btn svg {
        width: 22px; height: 22px;
        fill: none;
        stroke: #fff;
        stroke-width: 2.5;
        stroke-linecap: round;
        stroke-linejoin: round;
        pointer-events: none;
    }
</style>

<div class="swipe-container">
    <div class="swipe-viewport" id="swipeViewport">

        <a href="{{ url()->previous() }}" class="swipe-back-btn" id="backBtn" aria-label="{{ __('templates.swipe_back') }}">
            <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
        </a>

        <div class="swipe-counter" id="swipeCounter">
            <span id="currentIndex">1</span> / {{ count($templates) }}
        </div>

        {{-- Ses butonu: başta muted (kapalı ikon), tıklanınca açılır --}}
        <button class="swipe-mute-btn" id="muteBtn" aria-label="Sesi aç/kapat">
            {{-- MUTED ikonu (başta görünür) --}}
            <svg id="iconMuted" viewBox="0 0 24 24">
                <line x1="1"  y1="1"  x2="23" y2="23"/>
                <path d="M9 9v3a3 3 0 0 0 5.12 2.12"/>
                <path d="M15 9.34V4a3 3 0 0 0-5.94-.6"/>
                <path d="M17 16.95A7 7 0 0 1 5 12v-2"/>
                <path d="M19 12a7 7 0 0 0-.89-3.45"/>
                <line x1="12" y1="19" x2="12" y2="23"/>
                <line x1="8"  y1="23" x2="16" y2="23"/>
            </svg>
            {{-- UNMUTED ikonu (gizli, sesi açınca gösterilir) --}}
            <svg id="iconUnmuted" viewBox="0 0 24 24" style="display:none;">
                <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/>
                <path d="M19 10v2a7 7 0 0 1-14 0v-2"/>
                <line x1="12" y1="19" x2="12" y2="23"/>
                <line x1="8"  y1="23" x2="16" y2="23"/>
            </svg>
        </button>

        <div class="swipe-hint" id="swipeHint">
            <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="18 15 12 9 6 15"/>
            </svg>
            <span>{{ __('templates.swipe_hint') }}</span>
        </div>

        @foreach ($templates as $i => $template)
            @php
                $orientation = $template->hasVideoForOrientation('portrait')
                    ? 'portrait'
                    : ($template->hasVideoForOrientation('landscape')
                        ? 'landscape'
                        : 'square');
                $videoUrl  = $template->getVideoUrlForOrientation($orientation);
                $posterUrl = $template->poster_url ?? '';
            @endphp

            <div class="swipe-slide"
                 data-index="{{ $i }}"
                 data-uuid="{{ $template->uuid }}"
                 data-video-src="{{ $videoUrl }}"
                 style="transform: translateY({{ $i === 0 ? '0%' : '100%' }})">

                @if ($posterUrl)
                    <img
                        class="slide-poster"
                        src="{{ $posterUrl }}"
                        alt=""
                        loading="{{ $i === 0 ? 'eager' : 'lazy' }}">
                @endif

                <video
                    muted
                    loop
                    playsinline
                    preload="none"
                    @if ($posterUrl) poster="{{ $posterUrl }}" @endif>
                </video>

                <div class="slide-loading-indicator">
                    <div class="loading-spinner"></div>
                </div>

                <div class="swipe-title">
                    {{ $template->getTranslation('title', app()->getLocale()) }}
                </div>

                @auth
                    <a href="{{ route('templates.show', $template->uuid) }}"
                       class="swipe-use-btn">
                        {{ __('templates.swipe_use') }}
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="swipe-use-btn">
                        {{ __('templates.swipe_login') }}
                    </a>
                @endauth

            </div>
        @endforeach

    </div>
</div>

<script>
(function () {
    const slides      = Array.from(document.querySelectorAll('.swipe-slide'));
    const counter     = document.getElementById('currentIndex');
    const hint        = document.getElementById('swipeHint');
    const viewport    = document.getElementById('swipeViewport');
    const total       = slides.length;

    let current       = 0;
    let isAnimating   = false;
    let hintDismissed = false;

    // Başta tüm videolar muted — kullanıcı butona basınca açılır
    let isMuted = true;

    const muteBtn      = document.getElementById('muteBtn');
    const iconMuted    = document.getElementById('iconMuted');
    const iconUnmuted  = document.getElementById('iconUnmuted');

    /* ─────────────────────────────────────────
       Ses toggle
    ───────────────────────────────────────── */
    muteBtn.addEventListener('click', function () {
        isMuted = !isMuted;

        // Şu an oynayan videoyu güncelle
        const activeVideo = slides[current].querySelector('video');
        if (activeVideo) {
            activeVideo.muted = isMuted;
            // iOS Safari: muted=false yaptıktan sonra play() tekrar çağrılmalı
            if (!isMuted) {
                activeVideo.play().catch(() => {
                    // Hâlâ izin vermezse sessiz devam et
                    isMuted = true;
                    activeVideo.muted = true;
                    iconMuted.style.display   = '';
                    iconUnmuted.style.display = 'none';
                });
            }
        }

        // İkon değiştir
        iconMuted.style.display   = isMuted ? ''       : 'none';
        iconUnmuted.style.display = isMuted ? 'none'   : '';
    });

    const PRELOAD_AHEAD  = 2;
    const PRELOAD_BEHIND = 1;
    const UNLOAD_AFTER   = 4;

    /* ─────────────────────────────────────────
       Video src yönetimi
    ───────────────────────────────────────── */
    function loadVideo(index) {
        const slide = slides[index];
        if (!slide) return;
        const video = slide.querySelector('video');
        const src   = slide.dataset.videoSrc;
        if (!video || !src || video.src) return;
        video.src = src;
        video.load();
    }

    function unloadVideo(index) {
        const slide = slides[index];
        if (!slide) return;
        const video = slide.querySelector('video');
        if (!video || !video.src) return;
        video.pause();
        video.removeAttribute('src');
        video.load();
        slide.classList.remove('playing', 'buffering');
    }

    function manageWindow(center) {
        for (let i = 0; i < total; i++) {
            const dist = i - center;
            if (dist >= -PRELOAD_BEHIND && dist <= PRELOAD_AHEAD) {
                loadVideo(i);
            } else if (Math.abs(dist) > UNLOAD_AFTER) {
                unloadVideo(i);
            }
        }
    }

    /* ─────────────────────────────────────────
       Oynatma
    ───────────────────────────────────────── */
    function playSlide(index) {
        const slide = slides[index];
        if (!slide) return;
        const video = slide.querySelector('video');
        if (!video) return;

        loadVideo(index);

        // Geçerli ses durumunu uygula
        video.muted = isMuted;

        const doPlay = () => {
            slide.classList.remove('buffering');
            video.play().then(() => {
                slide.classList.add('playing');
            }).catch(() => {
                // Sesli oynatma reddedildiyse muted'a dön, tekrar dene
                video.muted = true;
                isMuted = true;
                iconMuted.style.display   = '';
                iconUnmuted.style.display = 'none';
                video.play().then(() => {
                    slide.classList.add('playing');
                }).catch(() => {});
            });
        };

        if (video.readyState >= 3) {
            doPlay();
        } else {
            slide.classList.add('buffering');
            video.addEventListener('canplay', doPlay, { once: true });
        }
    }

    function pauseSlide(index) {
        const slide = slides[index];
        if (!slide) return;
        const video = slide.querySelector('video');
        if (video) {
            video.pause();
            video.currentTime = 0;
        }
        slide.classList.remove('playing', 'buffering');
    }

    /* ─────────────────────────────────────────
       Geçiş animasyonu
    ───────────────────────────────────────── */
    function goTo(next, animate = true) {
        if (next < 0 || next >= total || next === current || isAnimating) return;

        isAnimating = true;
        const direction = next > current ? 1 : -1;
        const duration  = animate ? 400 : 0;

        const easing = 'cubic-bezier(0.25, 0.46, 0.45, 0.94)';
        const trans  = animate ? `transform ${duration}ms ${easing}` : 'none';

        slides[next].style.transition    = trans;
        slides[current].style.transition = trans;

        slides[next].style.transform = `translateY(${direction * 100}%)`;
        slides[next].getBoundingClientRect();

        slides[next].style.transform    = 'translateY(0%)';
        slides[current].style.transform = `translateY(${-direction * 100}%)`;

        pauseSlide(current);

        current = next;
        counter.textContent = current + 1;

        setTimeout(() => {
            isAnimating = false;
            playSlide(current);
            manageWindow(current);
        }, duration);

        if (!hintDismissed) {
            hintDismissed = true;
            hint.classList.add('hidden');
        }
    }

    /* ─────────────────────────────────────────
       Başlangıç pozisyonu
    ───────────────────────────────────────── */
    const startUuid  = '{{ $currentUuid }}';
    const startIndex = slides.findIndex(s => s.dataset.uuid === startUuid);
    if (startIndex > 0) {
        slides.forEach((s, i) => {
            s.style.transition = 'none';
            s.style.transform  = `translateY(${(i - startIndex) * 100}%)`;
        });
        current = startIndex;
        counter.textContent = current + 1;
    }

    /* ─────────────────────────────────────────
       İlk video
    ───────────────────────────────────────── */
    document.addEventListener('DOMContentLoaded', () => {
        manageWindow(current);
        playSlide(current);
        setTimeout(() => hint.classList.add('hidden'), 3000);
    });

    /* ─────────────────────────────────────────
       Touch / Swipe
    ───────────────────────────────────────── */
    let touchStartY = 0;
    let touchDeltaY = 0;

    viewport.addEventListener('touchstart', e => {
        touchStartY = e.touches[0].clientY;
        touchDeltaY = 0;
    }, { passive: true });

    viewport.addEventListener('touchmove', e => {
        touchDeltaY = e.touches[0].clientY - touchStartY;
    }, { passive: true });

    viewport.addEventListener('touchend', () => {
        if (Math.abs(touchDeltaY) > 50) {
            goTo(touchDeltaY < 0 ? current + 1 : current - 1);
        }
        touchDeltaY = 0;
    });

    /* ─────────────────────────────────────────
       Mouse wheel
    ───────────────────────────────────────── */
    let wheelLock = false;
    viewport.addEventListener('wheel', e => {
        e.preventDefault();
        if (wheelLock) return;
        wheelLock = true;
        goTo(e.deltaY > 0 ? current + 1 : current - 1);
        setTimeout(() => { wheelLock = false; }, 700);
    }, { passive: false });

    /* ─────────────────────────────────────────
       Klavye
    ───────────────────────────────────────── */
    document.addEventListener('keydown', e => {
        if (e.key === 'ArrowUp')   goTo(current - 1);
        if (e.key === 'ArrowDown') goTo(current + 1);
    });

    /* ─────────────────────────────────────────
       Geri butonu
    ───────────────────────────────────────── */
    document.getElementById('backBtn').addEventListener('click', function (e) {
        e.preventDefault();
        if (document.referrer) history.back();
        else window.location.href = '{{ route("home") }}';
    });
})();
</script>
@endsection
