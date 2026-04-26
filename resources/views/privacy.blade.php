@extends('web.layouts.app')

@section('title', __('privacy.title') . ' — AIFilm')

@section('content')
<div class="policy-page">
    <div class="policy-container">

        {{-- Header --}}
        <div class="policy-header">
            <div class="policy-badge">{{ __('privacy.title') }}</div>
            <h1 class="policy-title">{{ __('privacy.intro_title') }}</h1>
            <p class="policy-updated">{{ __('privacy.last_updated') }}</p>
        </div>

        {{-- Intro --}}
        <div class="policy-intro">
            <p>{{ __('privacy.intro_text') }}</p>
        </div>

        {{-- Sections --}}
        <div class="policy-body">

            {{-- Section 1 --}}
            <section class="policy-section">
                <h2>{{ __('privacy.section1_title') }}</h2>
                <p>{{ __('privacy.section1_text') }}</p>
                <ul>
                    @foreach(__('privacy.section1_items') as $item)
                        <li>{!! $item !!}</li>
                    @endforeach
                </ul>
            </section>

            {{-- Section 2 --}}
            <section class="policy-section">
                <h2>{{ __('privacy.section2_title') }}</h2>
                <ul>
                    @foreach(__('privacy.section2_items') as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            </section>

            {{-- Section 3 --}}
            <section class="policy-section">
                <h2>{{ __('privacy.section3_title') }}</h2>
                <p>{{ __('privacy.section3_text') }}</p>
                <ul>
                    @foreach(__('privacy.section3_items') as $item)
                        <li>{!! $item !!}</li>
                    @endforeach
                </ul>
                <div class="policy-note">
                    <span class="policy-note-icon">⚠</span>
                    {{ __('privacy.section3_note') }}
                </div>
            </section>

            {{-- Section 4 --}}
            <section class="policy-section">
                <h2>{{ __('privacy.section4_title') }}</h2>
                <p>{{ __('privacy.section4_text') }}</p>
                <ul>
                    @foreach(__('privacy.section4_items') as $item)
                        <li>{!! $item !!}</li>
                    @endforeach
                </ul>
                <div class="policy-note">
                    <span class="policy-note-icon">ℹ</span>
                    {{ __('privacy.section4_note') }}
                </div>
            </section>

            {{-- Section 5 --}}
            <section class="policy-section">
                <h2>{{ __('privacy.section5_title') }}</h2>
                <p>{{ __('privacy.section5_text') }}</p>
            </section>

            {{-- Section 6 --}}
            <section class="policy-section">
                <h2>{{ __('privacy.section6_title') }}</h2>
                <p>{{ __('privacy.section6_text') }}</p>
                <ul>
                    @foreach(__('privacy.section6_items') as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
                <p class="policy-contact-note">{!! __('privacy.section6_contact') !!}</p>
            </section>

            {{-- Section 7 --}}
            <section class="policy-section">
                <h2>{{ __('privacy.section7_title') }}</h2>
                <p>{{ __('privacy.section7_text') }}</p>
            </section>

            {{-- Section 8 --}}
            <section class="policy-section">
                <h2>{{ __('privacy.section8_title') }}</h2>
                <p>{{ __('privacy.section8_text') }}</p>
            </section>

        </div>

        {{-- Contact Box --}}
        <div class="policy-contact-box">
            <h3>{{ __('privacy.contact_title') }}</h3>
            <p>{{ __('privacy.contact_text') }}</p>
            <div class="policy-contact-details">
                <div class="contact-item">
                    <span class="contact-icon">🏢</span>
                    <span>{{ __('privacy.contact_company') }}</span>
                </div>
                <div class="contact-item">
                    <span class="contact-icon">📍</span>
                    <span>{{ __('privacy.contact_address') }}</span>
                </div>
                <div class="contact-item">
                    <span class="contact-icon">✉</span>
                    <a href="mailto:{{ __('privacy.contact_email') }}" class="policy-link">
                        {{ __('privacy.contact_email') }}
                    </a>
                </div>
                <div class="contact-item">
                    <span class="contact-icon">📞</span>
                    <a href="tel:+905314521253" class="policy-link">{{ __('privacy.contact_phone') }}</a>
                </div>
                <div class="contact-item">
                    <span class="contact-icon">💬</span>
                    <a href="https://wa.me/905314521253" target="_blank" class="policy-link whatsapp-link">
                        WhatsApp
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
/* ─── Renk Paleti ───────────────────────────────────────────────── */
.policy-page {
    --bg-main:      #0B0B0B;
    --bg-content:   #121212;
    --gold:         #D4AF37;
    --gold-hover:   #F5D97A;
    --purple:       #7C3AED;
    --blue:         #3B82F6;
    --text-primary: #FFFFFF;
    --text-muted:   #BFBFBF;
    --text-passive: #6B6B6B;

    min-height: 100vh;
    padding: 3.5rem 1rem 6rem;
    background: var(--bg-main);
    color: var(--text-primary);
    font-family: inherit;
}

/* ─── Container ─────────────────────────────────────────────────── */
.policy-container {
    max-width: 840px;
    margin: 0 auto;
}

/* ─── Header ────────────────────────────────────────────────────── */
.policy-header {
    text-align: center;
    margin-bottom: 3rem;
}

.policy-badge {
    display: inline-block;
    padding: 0.3rem 1.1rem;
    border: 1px solid rgba(212, 175, 55, 0.35);
    border-radius: 20px;
    font-size: 0.72rem;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--gold);
    margin-bottom: 1.2rem;
    background: rgba(212, 175, 55, 0.06);
}

.policy-title {
    font-size: clamp(1.9rem, 4vw, 2.8rem);
    font-weight: 700;
    letter-spacing: -0.025em;
    color: var(--text-primary);
    margin-bottom: 0.6rem;
    line-height: 1.2;
}

.policy-updated {
    font-size: 0.83rem;
    color: var(--text-passive);
    letter-spacing: 0.02em;
}

/* ─── Intro block ───────────────────────────────────────────────── */
.policy-intro {
    background: var(--bg-content);
    border-left: 3px solid var(--gold);
    border-radius: 0 10px 10px 0;
    padding: 1.3rem 1.6rem;
    margin-bottom: 3rem;
}

.policy-intro p {
    font-size: 0.97rem;
    line-height: 1.8;
    color: var(--text-muted);
    margin: 0;
}

/* ─── Sections ──────────────────────────────────────────────────── */
.policy-section {
    margin-bottom: 2.8rem;
    padding-bottom: 2.8rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}

.policy-section:last-child {
    border-bottom: none;
    margin-bottom: 2rem;
}

.policy-section h2 {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--gold);
    margin-bottom: 1rem;
    letter-spacing: -0.01em;
}

.policy-section p {
    font-size: 0.95rem;
    line-height: 1.8;
    color: var(--text-muted);
    margin-bottom: 0.9rem;
}

.policy-section ul {
    padding-left: 1.3rem;
    margin-top: 0.6rem;
    display: flex;
    flex-direction: column;
    gap: 0.55rem;
}

.policy-section ul li {
    font-size: 0.95rem;
    line-height: 1.75;
    color: var(--text-muted);
}

.policy-section ul li strong {
    color: var(--text-primary);
    font-weight: 600;
}

/* ─── Note boxes ────────────────────────────────────────────────── */
.policy-note {
    display: flex;
    align-items: flex-start;
    gap: 0.7rem;
    background: rgba(212, 175, 55, 0.05);
    border: 1px solid rgba(212, 175, 55, 0.18);
    border-radius: 8px;
    padding: 0.9rem 1.2rem;
    margin-top: 1.1rem;
    font-size: 0.88rem;
    color: var(--text-passive);
    line-height: 1.65;
}

.policy-note-icon {
    flex-shrink: 0;
    font-size: 1rem;
    margin-top: 0.05rem;
}

.policy-contact-note {
    margin-top: 1.1rem;
    font-size: 0.95rem;
    color: var(--text-muted);
}

/* ─── Links ─────────────────────────────────────────────────────── */
.policy-link {
    color: var(--gold);
    text-decoration: underline;
    text-underline-offset: 3px;
    text-decoration-color: rgba(212, 175, 55, 0.4);
    transition: color 0.2s, text-decoration-color 0.2s;
}

.policy-link:hover {
    color: var(--gold-hover);
    text-decoration-color: var(--gold-hover);
}

.whatsapp-link {
    color: #4ade80;
    text-decoration-color: rgba(74, 222, 128, 0.4);
}

.whatsapp-link:hover {
    color: #86efac;
    text-decoration-color: #86efac;
}

/* ─── Contact box ───────────────────────────────────────────────── */
.policy-contact-box {
    background: var(--bg-content);
    border: 1px solid rgba(212, 175, 55, 0.2);
    border-radius: 14px;
    padding: 2rem 2.2rem;
    margin-top: 0.5rem;
    position: relative;
    overflow: hidden;
}

.policy-contact-box::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 2px;
    background: linear-gradient(90deg, var(--gold), transparent);
}

.policy-contact-box h3 {
    font-size: 1.05rem;
    font-weight: 600;
    color: var(--gold);
    margin-bottom: 0.35rem;
}

.policy-contact-box > p {
    font-size: 0.88rem;
    color: var(--text-passive);
    margin-bottom: 1.4rem;
}

.policy-contact-details {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.contact-item {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    font-size: 0.93rem;
    color: var(--text-muted);
}

.contact-icon {
    font-size: 1rem;
    width: 22px;
    text-align: center;
    flex-shrink: 0;
}

/* ─── Responsive ────────────────────────────────────────────────── */
@media (max-width: 600px) {
    .policy-page {
        padding: 2rem 0.9rem 4rem;
    }
    .policy-contact-box {
        padding: 1.4rem 1.2rem;
    }
    .policy-title {
        font-size: 1.7rem;
    }
}
</style>
@endsection
