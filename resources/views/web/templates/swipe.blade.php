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
        /* FIX 3: GPU katmanı — iOS'ta composite layer siyah flash önler */
        will-change: transform;
        -webkit-transform: translateZ(0);
        transform: translateZ(0);
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
        /* FIX 1: Poster artık CSS background — anında render edilir, img lazy'yi beklemez */
        background-color: #111;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        /* FIX 5: 400ms → 280ms — iOS'ta flash fark edilmez hale gelir */
        transition: transform 280ms cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }

    /* FIX 7: GPU katmanı artık TÜM slaytlara sabit değil, sadece aktif
       pencere içindekilere (JS ile eklenen .gpu-active class'ı üzerinden)
       uygulanıyor. */
    .swipe-slide.gpu-active {
        will-change: transform;
        -webkit-transform: translateZ(0);
        transform: translateZ(0);
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

    /* Video oynatılınca görünür olur, CSS bg (poster) zaten altta kalır */
    .swipe-slide.playing video { opacity: 1; }

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

        @php
            /*
             * FIX 8: Poster (background-image) artık HER slayt için değil,
             * sadece başlangıç penceresindeki (aktif ± 1) slaytlar için
             * sunucu tarafında set ediliyor. Diğer template'lerin poster
             * görselleri sayfa ilk açıldığında hiç indirilip decode
             * edilmiyor — JS, kullanıcı kaydırdıkça bunları devreye sokuyor.
             */
            $currentIndex = 0;
            foreach ($templates as $idx => $tmpl) {
                if ($tmpl->uuid === $currentUuid) {
                    $currentIndex = $idx;
                    break;
                }
            }
        @endphp

        @foreach ($templates as $i => $template)
            @php
                $orientation = $template->hasVideoForOrientation('portrait')
                    ? 'portrait'
                    : ($template->hasVideoForOrientation('landscape')
                        ? 'landscape'
                        : 'square');
                $videoUrl  = $template->getVideoUrlForOrientation($orientation);
                $posterUrl = $template->poster_url ?? '';
                $inInitialWindow = abs($i - $currentIndex) <= 1;
                $isHls = str_ends_with(strtolower((string) $videoUrl), '.m3u8');
            @endphp

            {{--
                FIX 1: Poster artık CSS background-image olarak set ediliyor.
                FIX 8: Ama sadece başlangıç penceresindeki slaytlara — diğerleri
                       data-poster-url'de tutulur, JS ihtiyaç anında yükler.
            --}}
            <div class="swipe-slide"
                 data-index="{{ $i }}"
                 data-uuid="{{ $template->uuid }}"
                 data-video-src="{{ $videoUrl }}"
                 data-is-hls="{{ $isHls ? '1' : '0' }}"
                 data-poster-url="{{ $posterUrl }}"
                 @if ($posterUrl && $inInitialWindow)
                     style="transform: translateY({{ $i === $currentIndex ? '0%' : ($i < $currentIndex ? '-100%' : '100%') }}); background-image: url('{{ $posterUrl }}');"
                 @else
                     style="transform: translateY({{ $i === $currentIndex ? '0%' : ($i < $currentIndex ? '-100%' : '100%') }});"
                 @endif>

                {{--
                    FIX 2: preload="none" → preload="metadata"
                    "metadata" ile browser ilk kareyi buffer'a alır,
                    video hazır olmadan önce siyah kare görünmez.
                    İlk slide "auto" preload alır, daha hızlı açılır.
                --}}
                <video
                    muted
                    loop
                    playsinline
                    preload="{{ $i === $currentIndex ? 'auto' : 'metadata' }}"
                    @if ($posterUrl && $inInitialWindow) poster="{{ $posterUrl }}" @endif>
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

{{-- HLS.js: Chrome/Android/Desktop tarayıcılar için gerekli.
     Safari native HLS destekler, bu kütüphaneyi kullanmaz. --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/hls.js/1.5.15/hls.min.js"></script>

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

    let isMuted = true;

    const muteBtn      = document.getElementById('muteBtn');
    const iconMuted    = document.getElementById('iconMuted');
    const iconUnmuted  = document.getElementById('iconUnmuted');

    /* Safari native HLS desteğini bir kere kontrol et */
    const nativeHlsSupport = (function () {
        const testVideo = document.createElement('video');
        return testVideo.canPlayType('application/vnd.apple.mpegurl') !== '';
    })();

    /* Her slide için ayrı "generation" sayacı — race condition önlemi.
       Bir video için loadVideo çağrıldığında sayaç artar; canplay/doPlay
       callback'i tetiklendiğinde sayaç hâlâ aynıysa işleme devam edilir,
       değilse (araya unload/başka load girmişse) sessizce iptal edilir. */
    const generations = new Array(total).fill(0);

    /* Her slide'ın hls.js instance'ını saklamak için */
    const hlsInstances = new Array(total).fill(null);

    /* ─────────────────────────────────────────
       Ses toggle
    ───────────────────────────────────────── */
    muteBtn.addEventListener('click', function () {
        isMuted = !isMuted;

        const activeVideo = slides[current].querySelector('video');
        if (activeVideo) {
            activeVideo.muted = isMuted;
            if (!isMuted) {
                activeVideo.play().catch(() => {
                    isMuted = true;
                    activeVideo.muted = true;
                    iconMuted.style.display   = '';
                    iconUnmuted.style.display = 'none';
                });
            }
        }

        iconMuted.style.display   = isMuted ? ''       : 'none';
        iconUnmuted.style.display = isMuted ? 'none'   : '';
    });

    /* ─────────────────────────────────────────
       Pencere yönetimi (video + poster + GPU katmanı)
       ─────────────────────────────────────────
       Aktif pencere dışındaki her slayt hem videosunu hem poster
       görselini boşaltıyor, hem de GPU compositing katmanını
       (.gpu-active) kaybediyor. Template sayısı ne olursa olsun,
       her an en fazla 3 slayt (önceki, aktif, sonraki) bellek
       tüketiyor — geri kalanlar sıradan, hafif DOM elemanı.
    ───────────────────────────────────────── */
    const PRELOAD_AHEAD  = 1;
    const PRELOAD_BEHIND = 1;

    /* ─────────────────────────────────────────
       Video src yönetimi (HLS destekli)
    ───────────────────────────────────────── */
    function loadVideo(index) {
        const slide = slides[index];
        if (!slide) return;
        const video = slide.querySelector('video');
        const src   = slide.dataset.videoSrc;
        const isHls = slide.dataset.isHls === '1';

        // getAttribute kullanıyoruz: video.src property'si WebKit'te
        // src kaldırılsa bile boş string yerine sayfa URL'sine resolve
        // olabiliyor, bu yüzden attribute kontrolü daha güvenilir.
        if (!video || !src || video.getAttribute('src') || hlsInstances[index]) return;

        // Bu slot için yeni bir "nesil" başlat — eski canplay/doPlay
        // callback'leri artık geçersiz sayılacak.
        generations[index]++;

        if (isHls && !nativeHlsSupport) {
            if (window.Hls && window.Hls.isSupported()) {
                const hls = new Hls({
                    // Sadece aktif pencere videoları için, agresif ama makul buffer
                    maxBufferLength: 15,
                    maxMaxBufferLength: 30,
                });
                hls.loadSource(src);
                hls.attachMedia(video);
                hlsInstances[index] = hls;
            } else {
                // hls.js yüklenemediyse veya desteklenmiyorsa: son çare
                // olarak direkt src atamayı dene (bazı tarayıcılar yine de
                // segment segment çekebilir, olmazsa video hata verir ama
                // sayfa çökmez).
                video.src = src;
                video.load();
            }
        } else {
            // Safari native HLS veya düz MP4
            video.src = src;
            video.load();
        }
    }

    function unloadVideo(index) {
        const slide = slides[index];
        if (!slide) return;
        const video = slide.querySelector('video');
        if (!video) return;

        // Nesli ilerlet: bu slota ait bekleyen callback'leri geçersiz kıl
        generations[index]++;

        if (hlsInstances[index]) {
            try { hlsInstances[index].destroy(); } catch (e) {}
            hlsInstances[index] = null;
        }

        if (!video.getAttribute('src')) return;

        video.pause();
        video.removeAttribute('src');
        video.load();
        slide.classList.remove('playing', 'buffering');
    }

    /* ─────────────────────────────────────────
       FIX 8: Poster (background-image) yönetimi
    ───────────────────────────────────────── */
    function loadPoster(index) {
        const slide = slides[index];
        if (!slide) return;
        const poster = slide.dataset.posterUrl;
        if (!poster) return;
        slide.style.backgroundImage = `url('${poster}')`;
    }

    function unloadPoster(index) {
        const slide = slides[index];
        if (!slide) return;
        slide.style.backgroundImage = 'none';
    }

    function manageWindow(center) {
        for (let i = 0; i < total; i++) {
            const dist = i - center;
            if (dist >= -PRELOAD_BEHIND && dist <= PRELOAD_AHEAD) {
                loadVideo(i);
                loadPoster(i);
                slides[i].classList.add('gpu-active');
            } else {
                unloadVideo(i);
                unloadPoster(i);
                slides[i].classList.remove('gpu-active');
            }
        }
    }

    /* ─────────────────────────────────────────
       Oynatma (race-condition korumalı)
    ───────────────────────────────────────── */
    function playSlide(index) {
        const slide = slides[index];
        if (!slide) return;
        const video = slide.querySelector('video');
        if (!video) return;

        loadVideo(index);
        video.muted = isMuted;

        // Bu playSlide çağrısının ait olduğu nesli sabitle.
        const myGeneration = generations[index];

        const doPlay = () => {
            // Aradan başka bir load/unload geçtiyse (kullanıcı hızlı
            // kaydırdıysa) bu callback artık geçersiz, hiçbir şey yapma.
            if (generations[index] !== myGeneration) return;

            slide.classList.remove('buffering');
            video.play().then(() => {
                if (generations[index] !== myGeneration) return;
                slide.classList.add('playing');
            }).catch(() => {
                if (generations[index] !== myGeneration) return;
                video.muted = true;
                isMuted = true;
                iconMuted.style.display   = '';
                iconUnmuted.style.display = 'none';
                video.play().then(() => {
                    if (generations[index] !== myGeneration) return;
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
            // readyState 0 iken currentTime ataması Safari'de
            // InvalidStateError fırlatabiliyor, bu yüzden guard ekliyoruz.
            if (video.readyState > 0) {
                try { video.currentTime = 0; } catch (e) {}
            }
        }
        slide.classList.remove('playing', 'buffering');
    }

    /* ─────────────────────────────────────────
       FIX 4 + FIX 5: Geçiş animasyonu
       ─────────────────────────────────────────
       TikTok yöntemi:
       - Çıkan slide yerinde KALIR (z-index düşürülür)
       - Giren slide üstten/alttan kayar
       - Hiçbir anda siyah zemin görünmez
       - Süre 280ms (400ms'den hızlı, flash fark edilmez)
    ───────────────────────────────────────── */
    const DURATION = 280; // FIX 5: 400 → 280ms
    const EASING   = 'cubic-bezier(0.25, 0.46, 0.45, 0.94)';

    function goTo(next, animate = true) {
        if (next < 0 || next >= total || next === current || isAnimating) return;

        isAnimating = true;
        const direction    = next > current ? 1 : -1;
        const currentSlide = slides[current];
        const nextSlide    = slides[next];

        // Giren videoyu HEMEN yükletmeye başla (animasyon bitmesini bekleme).
        // Böylece 280ms'lik geçiş süresi boyunca video zaten buffer'a alınıyor
        // olur; siyah ekran/boş kare ihtimali büyük ölçüde azalır.
        loadVideo(next);
        loadPoster(next);

        // 1) Tüm slide'ların CSS transition'ını dondur.
        //    Aksi hâlde sadece iki slide'a transition:none yazmak yetmez;
        //    diğer slide'lar CSS kuralındaki transition ile yerinden oynayabilir.
        slides.forEach(s => { s.style.transition = 'none'; });

        // 2) Çıkan slide yerinde dursun (z-index:1)
        currentSlide.style.zIndex    = '1';
        currentSlide.style.transform = 'translateY(0%)';

        // 3) Giren slide ekran dışına konumlan (z-index:2 — üstte)
        nextSlide.style.zIndex    = '2';
        nextSlide.style.transform = `translateY(${direction * 100}%)`;

        // 4) Reflow — iOS'ta transition:none'ın ve yeni transform'un
        //    hemen işlenmesi için zorunlu.
        nextSlide.getBoundingClientRect();

        // 5) Sadece giren slide'a transition ver ve ekrana taşı
        if (animate) {
            nextSlide.style.transition = `transform ${DURATION}ms ${EASING}`;
        }
        nextSlide.style.transform = 'translateY(0%)';

        pauseSlide(current);
        current = next;
        counter.textContent = current + 1;

        setTimeout(() => {
            // 6) Animasyon bitti — tüm slide'ları yeni current'a göre
            //    anında (transition:none ile) konumlandır.
            //    '' yerine 'none' yazıyoruz: '' CSS transition'ı geri getirir
            //    ve reposition sırasında slide'lar görünür şekilde kayar.
            slides.forEach((s, i) => {
                s.style.transition = 'none';
                s.style.zIndex     = '';
                s.style.transform  = `translateY(${(i - current) * 100}%)`;
            });

            isAnimating = false;
            playSlide(current);
            manageWindow(current);
        }, animate ? DURATION + 16 : 0); // +16ms: son frame'in ekrana basılmasını bekle

        if (!hintDismissed) {
            hintDismissed = true;
            hint.classList.add('hidden');
        }
    }

    /* ─────────────────────────────────────────
       Başlangıç pozisyonu
       ─────────────────────────────────────────
       Kullanıcı ana sayfada N. videoya tıkladıysa, swipe sayfası
       doğrudan o videodan (index N) başlar — 1'e dönmez.

       ÖNEMLİ: index, JS tarafında uuid string eşleştirmesiyle YENİDEN
       bulunmuyor. Bunun yerine sunucunun (Blade/PHP) zaten hesapladığı
       $currentIndex değeri doğrudan buraya basılıyor. Önceki sürümde
       JS kendi içinde slides.findIndex(...) ile uuid'yi tekrar arıyordu;
       bu eşleşme herhangi bir sebeple başarısız olduğunda (örn. -1 dönüp
       sessizce yutulduğunda) JS'in bildiği "current" 0'da kalıyor ama
       ekranda hâlâ doğru slayt (örn. 5.) görünüyordu — ilk swipe'ta kod
       "current"ı (0) esas alıp slides[0]'ı zorla öne getiriyor, bu da
       kullanıcının gördüğü "1'e zıplama" hatasına yol açıyordu.
       $currentIndex'i doğrudan kullanmak bu senkron dışı kalma
       ihtimalini tamamen ortadan kaldırır: ekranda görünen ile JS'in
       "current" olarak bildiği değer her zaman birebir aynı olur.
    ───────────────────────────────────────── */
    current = {{ $currentIndex }};
    counter.textContent = current + 1;

    if (current > 0) {
        slides.forEach((s, i) => {
            s.style.transition = 'none';
            s.style.transform  = `translateY(${(i - current) * 100}%)`;
        });
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
