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

    /* Sayfanın kendi scroll'unu kapat */
    body, html {
        overflow: hidden;
    }

    /* Ana kapsayıcı */
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

    /* Telefonda tam ekran, tablet/masaüstünde ortalanmış kutu */
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

    /* Her bir slide */
    .swipe-slide {
        position: absolute;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background: #000;
        transition: transform 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }

    .swipe-slide video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    /* Gradient overlay (alt kısım okunabilir olsun) */
    .swipe-slide::after {
        content: '';
        position: absolute;
        bottom: 0; left: 0;
        width: 100%;
        height: 40%;
        background: linear-gradient(to top, rgba(0,0,0,0.85), transparent);
        pointer-events: none;
        z-index: 2;
    }

    /* Geri butonu */
    .swipe-back-btn {
        position: absolute;
        top: 16px;
        left: 16px;
        z-index: 100;
        background: rgba(0,0,0,0.5);
        border: none;
        border-radius: 50%;
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        transition: background 0.2s;
        text-decoration: none;
    }

    .swipe-back-btn:hover {
        background: rgba(212,175,55,0.3);
    }

    .swipe-back-btn svg {
        width: 22px;
        height: 22px;
        fill: none;
        stroke: #fff;
        stroke-width: 2.5;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    /* Template başlığı */
    .swipe-title {
        position: absolute;
        bottom: 90px;
        left: 16px;
        right: 80px;
        z-index: 10;
        color: var(--text-primary);
        font-size: 1.1rem;
        font-weight: 700;
        text-shadow: 0 2px 8px rgba(0,0,0,0.8);
        line-height: 1.4;
    }

    /* Kullan / Giriş Yap butonu */
    .swipe-use-btn {
        position: absolute;
        bottom: 30px;
        right: 16px;
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

    /* Slide sayacı */
    .swipe-counter {
        position: absolute;
        top: 16px;
        right: 16px;
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

    /* Kaydır ipucu (ilk açılışta göster) */
    .swipe-hint {
        position: absolute;
        bottom: 100px;
        left: 50%;
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
        width: 28px;
        height: 28px;
        stroke: rgba(255,255,255,0.7);
        fill: none;
        stroke-width: 2;
        animation: bounceUp 1.2s infinite;
    }

    .swipe-hint span {
        color: rgba(255,255,255,0.7);
        font-size: 0.75rem;
    }

    @keyframes bounceUp {
        0%, 100% { transform: translateY(0); }
        50%       { transform: translateY(-8px); }
    }
</style>

<div class="swipe-container">
    <div class="swipe-viewport" id="swipeViewport">

        <!-- Geri butonu -->
        <a href="{{ url()->previous() }}" class="swipe-back-btn" id="backBtn" aria-label="{{ __('templates.swipe_back') }}">
            <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
        </a>

        <!-- Sayaç -->
        <div class="swipe-counter" id="swipeCounter">
            <span id="currentIndex">1</span> / {{ count($templates) }}
        </div>

        <!-- Kaydır ipucu -->
        <div class="swipe-hint" id="swipeHint">
            <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="18 15 12 9 6 15"/>
            </svg>
            <span>{{ __('templates.swipe_hint') }}</span>
        </div>

        <!-- Slide'lar -->
        @foreach ($templates as $i => $template)
            @php
                $orientation = $template->hasVideoForOrientation('portrait')
                    ? 'portrait'
                    : ($template->hasVideoForOrientation('landscape')
                        ? 'landscape'
                        : 'square');
                $videoUrl = $template->getVideoUrlForOrientation($orientation);
            @endphp

            <div class="swipe-slide"
                 data-index="{{ $i }}"
                 data-uuid="{{ $template->uuid }}"
                 style="transform: translateY({{ $i === 0 ? '0%' : '100%' }})">

                <video
                    muted
                    loop
                    playsinline
                    preload="{{ $i === 0 ? 'auto' : 'none' }}"
                    data-src="{{ $videoUrl }}"
                    {{ $i === 0 ? 'src='.$videoUrl : '' }}>
                </video>

                <!-- Başlık -->
                <div class="swipe-title">
                    {{ $template->getTranslation('title', app()->getLocale()) }}
                </div>

                <!-- Kullan / Giriş Yap butonu -->
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
    const slides      = Array.from(document.querySelectorAll('.swipe-slide'));
    const counter     = document.getElementById('currentIndex');
    const hint        = document.getElementById('swipeHint');
    const viewport    = document.getElementById('swipeViewport');
    const total       = slides.length;
    let current       = 0;
    let isAnimating   = false;
    let hintDismissed = false;

    // Başlangıç: tıklanan template'e göre index bul
    const startUuid = '{{ $currentUuid }}';
    const startIndex = slides.findIndex(s => s.dataset.uuid === startUuid);
    if (startIndex > 0) goTo(startIndex, false);

    /* ---------- Video yönetimi ---------- */
    function playSlide(index) {
        const video = slides[index]?.querySelector('video');
        if (!video) return;
        if (!video.src && video.dataset.src) {
            video.src = video.dataset.src;
            video.load();
        }
        video.play().catch(() => {});
    }

    function pauseSlide(index) {
        const video = slides[index]?.querySelector('video');
        if (video) { video.pause(); video.currentTime = 0; }
    }

    /* ---------- Geçiş ---------- */
    function goTo(next, animate = true) {
        if (next < 0 || next >= total || next === current) return;

        const direction = next > current ? 1 : -1;

        // Bir sonraki slide'ı hazırla
        slides[next].style.transition = animate ? 'transform 0.4s cubic-bezier(0.25,0.46,0.45,0.94)' : 'none';
        slides[current].style.transition = animate ? 'transform 0.4s cubic-bezier(0.25,0.46,0.45,0.94)' : 'none';

        slides[next].style.transform = `translateY(${direction * 100}%)`;
        // Reflow zorla
        slides[next].getBoundingClientRect();

        slides[next].style.transform = 'translateY(0%)';
        slides[current].style.transform = `translateY(${-direction * 100}%)`;

        pauseSlide(current);

        // Önceden video yükle (bir sonraki)
        const preloadIndex = next + 1;
        if (preloadIndex < total) {
            const preloadVideo = slides[preloadIndex].querySelector('video');
            if (preloadVideo && !preloadVideo.src && preloadVideo.dataset.src) {
                preloadVideo.src = preloadVideo.dataset.src;
                preloadVideo.load();
            }
        }

        current = next;
        counter.textContent = current + 1;
        playSlide(current);

        // Hint'i gizle
        if (!hintDismissed) {
            hintDismissed = true;
            hint.classList.add('hidden');
        }
    }

    /* ---------- Touch / Swipe ---------- */
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
            if (touchDeltaY < 0) goTo(current + 1); // yukarı kaydır → ileri
            else                  goTo(current - 1); // aşağı kaydır → geri
        }
        touchDeltaY = 0;
    });

    /* ---------- Mouse Wheel (desktop) ---------- */
    let wheelLock = false;
    viewport.addEventListener('wheel', e => {
        e.preventDefault();
        if (wheelLock) return;
        wheelLock = true;
        if (e.deltaY > 0) goTo(current + 1);
        else              goTo(current - 1);
        setTimeout(() => { wheelLock = false; }, 700);
    }, { passive: false });

    /* ---------- Klavye ---------- */
    document.addEventListener('keydown', e => {
        if (e.key === 'ArrowUp')   goTo(current - 1);
        if (e.key === 'ArrowDown') goTo(current + 1);
    });

    /* ---------- İlk video oynat ---------- */
    document.addEventListener('DOMContentLoaded', () => {
        playSlide(current);
        // 3 saniye sonra hint kaybolsun
        setTimeout(() => hint.classList.add('hidden'), 3000);
    });

    /* ---------- Geri butonu: önceki sayfa yoksa ana sayfaya ---------- */
    document.getElementById('backBtn').addEventListener('click', function(e) {
        e.preventDefault();
        if (document.referrer) {
            history.back();
        } else {
            window.location.href = '{{ route("home") }}';
        }
    });
</script>
@endsection
