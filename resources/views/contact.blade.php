@extends('web.layouts.app')

@section('title', __('contact.title') . ' — AIFilm')

@section('content')
<div class="contact-page">
    <div class="contact-container">

        {{-- Header --}}
        <div class="contact-header">
            <div class="contact-badge">{{ __('contact.title') }}</div>
            <h1 class="contact-title">{{ __('contact.intro_title') }}</h1>
            <p class="contact-subtitle">{{ __('contact.intro_text') }}</p>
        </div>

        {{-- Contact Cards --}}
        <div class="contact-grid">

            {{-- Email --}}
            <a href="mailto:{{ __('contact.email_value') }}" class="contact-card">
                <div class="card-icon">✉</div>
                <div class="card-body">
                    <div class="card-title">{{ __('contact.email_title') }}</div>
                    <div class="card-desc">{{ __('contact.email_desc') }}</div>
                    <div class="card-value">{{ __('contact.email_value') }}</div>
                </div>
                <div class="card-arrow">→</div>
            </a>

            {{-- Phone --}}
            <a href="tel:+905314521253" class="contact-card">
                <div class="card-icon">📞</div>
                <div class="card-body">
                    <div class="card-title">{{ __('contact.phone_title') }}</div>
                    <div class="card-desc">{{ __('contact.phone_desc') }}</div>
                    <div class="card-value">{{ __('contact.phone_value') }}</div>
                </div>
                <div class="card-arrow">→</div>
            </a>

            {{-- WhatsApp --}}
            <a href="https://wa.me/905314521253" target="_blank" class="contact-card whatsapp-card">
                <div class="card-icon">💬</div>
                <div class="card-body">
                    <div class="card-title">{{ __('contact.whatsapp_title') }}</div>
                    <div class="card-desc">{{ __('contact.whatsapp_desc') }}</div>
                    <div class="card-value whatsapp-value">{{ __('contact.whatsapp_value') }}</div>
                </div>
                <div class="card-arrow">→</div>
            </a>

            {{-- Address --}}
            <div class="contact-card no-link">
                <div class="card-icon">📍</div>
                <div class="card-body">
                    <div class="card-title">{{ __('contact.address_title') }}</div>
                    <div class="card-desc">{{ __('contact.address_desc') }}</div>
                    <div class="card-value">{{ __('contact.address_value') }}</div>
                </div>
            </div>

        </div>

        {{-- Company Info + Notes --}}
        <div class="contact-bottom">

            {{-- Company Box --}}
            <div class="info-box">
                <h3 class="info-box-title">🏢 {{ __('contact.company_title') }}</h3>
                <div class="info-row">
                    <span class="info-label">{{ __('contact.company_name') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">{{ __('contact.company_domain') }}</span>
                </div>
            </div>

            {{-- Response Time --}}
            <div class="info-box">
                <h3 class="info-box-title">⏱ {{ __('contact.response_title') }}</h3>
                <p class="info-text">{!! __('contact.response_text') !!}</p>
            </div>

            {{-- Refund Note --}}
            <div class="info-box refund-box">
                <h3 class="info-box-title">💳 {{ app()->getLocale() === 'tr' ? 'İade Talepleri' : 'Refund Requests' }}</h3>
                <p class="info-text">{!! __('contact.refund_note') !!}</p>
            </div>

        </div>

    </div>
</div>

<style>
.contact-page {
    --bg-main:      #0B0B0B;
    --bg-content:   #121212;
    --gold:         #D4AF37;
    --gold-hover:   #F5D97A;
    --text-primary: #FFFFFF;
    --text-muted:   #BFBFBF;
    --text-passive: #6B6B6B;
    --green:        #4ade80;

    min-height: 100vh;
    padding: 3.5rem 1rem 6rem;
    background: var(--bg-main);
    color: var(--text-primary);
    font-family: inherit;
}

.contact-container {
    max-width: 860px;
    margin: 0 auto;
}

/* Header */
.contact-header {
    text-align: center;
    margin-bottom: 3.5rem;
}

.contact-badge {
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

.contact-title {
    font-size: clamp(1.9rem, 4vw, 2.8rem);
    font-weight: 700;
    letter-spacing: -0.025em;
    color: var(--text-primary);
    margin-bottom: 0.8rem;
    line-height: 1.2;
}

.contact-subtitle {
    font-size: 1rem;
    color: var(--text-muted);
    line-height: 1.7;
    max-width: 520px;
    margin: 0 auto;
}

/* Grid */
.contact-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-bottom: 2rem;
}

/* Cards */
.contact-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    background: var(--bg-content);
    border: 1px solid rgba(255, 255, 255, 0.07);
    border-radius: 14px;
    padding: 1.4rem 1.3rem;
    text-decoration: none;
    color: inherit;
    transition: border-color 0.2s, transform 0.2s;
    cursor: pointer;
}

.contact-card:hover {
    border-color: rgba(212, 175, 55, 0.4);
    transform: translateY(-2px);
    text-decoration: none;
    color: inherit;
}

.contact-card.no-link { cursor: default; }
.contact-card.no-link:hover { transform: none; border-color: rgba(255,255,255,0.07); }

.whatsapp-card:hover { border-color: rgba(74, 222, 128, 0.4); }

.card-icon {
    font-size: 1.5rem;
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(212, 175, 55, 0.08);
    border-radius: 10px;
    flex-shrink: 0;
}

.whatsapp-card .card-icon { background: rgba(74, 222, 128, 0.08); }

.card-body { flex: 1; min-width: 0; }

.card-title {
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--text-passive);
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-bottom: 0.15rem;
}

.card-desc {
    font-size: 0.78rem;
    color: var(--text-passive);
    margin-bottom: 0.4rem;
}

.card-value {
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--gold);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.whatsapp-value { color: var(--green); }

.card-arrow {
    color: var(--text-passive);
    font-size: 1.1rem;
    flex-shrink: 0;
    transition: color 0.2s;
}

.contact-card:hover .card-arrow { color: var(--gold); }
.whatsapp-card:hover .card-arrow { color: var(--green); }

/* Bottom section */
.contact-bottom {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-top: 1rem;
}

.info-box {
    background: var(--bg-content);
    border: 1px solid rgba(255, 255, 255, 0.07);
    border-radius: 14px;
    padding: 1.4rem 1.6rem;
}

.refund-box {
    border-color: rgba(212, 175, 55, 0.15);
    background: rgba(212, 175, 55, 0.03);
}

.info-box-title {
    font-size: 0.92rem;
    font-weight: 600;
    color: var(--gold);
    margin-bottom: 0.75rem;
}

.info-row {
    font-size: 0.93rem;
    color: var(--text-muted);
    padding: 0.3rem 0;
}

.info-text {
    font-size: 0.93rem;
    color: var(--text-muted);
    line-height: 1.75;
    margin: 0;
}

.info-text strong { color: var(--text-primary); }

/* Responsive */
@media (max-width: 600px) {
    .contact-page { padding: 2rem 0.9rem 4rem; }
    .contact-grid { grid-template-columns: 1fr; }
    .contact-title { font-size: 1.7rem; }
    .card-value { font-size: 0.85rem; }
}
</style>
@endsection
