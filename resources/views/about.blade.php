@extends('web.layouts.app')

@section('title', __('about.title') . ' — AIFilm')

@section('content')
<div class="about-page">
    <div class="about-container">

        {{-- Hero --}}
        <div class="about-hero">
            <div class="about-badge">{{ __('about.title') }}</div>
            <h1 class="about-title">{{ __('about.intro_title') }}</h1>
            <p class="about-subtitle">{{ __('about.intro_text') }}</p>
        </div>

        {{-- Mission & Platform --}}
        <div class="about-text-grid">
            <div class="about-text-card">
                <div class="text-card-icon">🎯</div>
                <h2>{{ __('about.mission_title') }}</h2>
                <p>{{ __('about.mission_text') }}</p>
            </div>
            <div class="about-text-card">
                <div class="text-card-icon">🚀</div>
                <h2>{{ __('about.platform_title') }}</h2>
                <p>{{ __('about.platform_text') }}</p>
            </div>
        </div>

        {{-- What We Offer --}}
        <div class="about-offer">
            <h2 class="offer-title">{{ __('about.offer_title') }}</h2>
            <div class="offer-grid">
                @foreach(__('about.offer_items') as $item)
                <div class="offer-card">
                    <div class="offer-icon">{{ $item['icon'] }}</div>
                    <h3>{{ $item['title'] }}</h3>
                    <p>{{ $item['text'] }}</p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Company Info --}}
        <div class="about-company">
            <h2 class="company-title">🏢 {{ __('about.company_title') }}</h2>
            <div class="company-details">
                <div class="company-row">
                    <span class="company-label">{{ __('about.company_name') }}</span>
                </div>
                <div class="company-row">
                    <span class="company-icon">🌐</span>
                    <a href="https://asilov.com" class="company-link">{{ __('about.company_domain') }}</a>
                </div>
                <div class="company-row">
                    <span class="company-icon">✉</span>
                    <a href="mailto:{{ __('about.company_email') }}" class="company-link">{{ __('about.company_email') }}</a>
                </div>
                <div class="company-row">
                    <span class="company-icon">📍</span>
                    <span>{{ __('about.company_country') }}</span>
                </div>
            </div>
        </div>

        {{-- CTA --}}
        <div class="about-cta">
            <h2>{{ __('about.cta_title') }}</h2>
            <p>{{ __('about.cta_text') }}</p>
            <a href="{{ route('packages.index') }}" class="cta-btn">
                {{ __('about.cta_button') }} →
            </a>
        </div>

    </div>
</div>

<style>
.about-page {
    --bg-main:      #0B0B0B;
    --bg-content:   #121212;
    --gold:         #D4AF37;
    --gold-hover:   #F5D97A;
    --purple:       #7C3AED;
    --text-primary: #FFFFFF;
    --text-muted:   #BFBFBF;
    --text-passive: #6B6B6B;

    min-height: 100vh;
    padding: 3.5rem 1rem 6rem;
    background: var(--bg-main);
    color: var(--text-primary);
    font-family: inherit;
}

.about-container {
    max-width: 900px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 3.5rem;
}

/* Hero */
.about-hero { text-align: center; }

.about-badge {
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

.about-title {
    font-size: clamp(2rem, 5vw, 3rem);
    font-weight: 700;
    letter-spacing: -0.03em;
    line-height: 1.15;
    margin-bottom: 1rem;
    background: linear-gradient(135deg, #fff 40%, var(--gold));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.about-subtitle {
    font-size: 1.05rem;
    color: var(--text-muted);
    line-height: 1.7;
    max-width: 540px;
    margin: 0 auto;
}

/* Mission & Platform */
.about-text-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.2rem;
}

.about-text-card {
    background: var(--bg-content);
    border: 1px solid rgba(255, 255, 255, 0.07);
    border-radius: 16px;
    padding: 1.8rem;
}

.text-card-icon {
    font-size: 1.6rem;
    margin-bottom: 0.9rem;
}

.about-text-card h2 {
    font-size: 1.05rem;
    font-weight: 600;
    color: var(--gold);
    margin-bottom: 0.75rem;
}

.about-text-card p {
    font-size: 0.93rem;
    line-height: 1.8;
    color: var(--text-muted);
    margin: 0;
}

/* Offer */
.offer-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 1.4rem;
    text-align: center;
}

.offer-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.offer-card {
    background: var(--bg-content);
    border: 1px solid rgba(255, 255, 255, 0.07);
    border-radius: 14px;
    padding: 1.5rem;
    transition: border-color 0.2s, transform 0.2s;
}

.offer-card:hover {
    border-color: rgba(212, 175, 55, 0.3);
    transform: translateY(-2px);
}

.offer-icon {
    font-size: 1.6rem;
    margin-bottom: 0.75rem;
}

.offer-card h3 {
    font-size: 0.97rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

.offer-card p {
    font-size: 0.88rem;
    line-height: 1.7;
    color: var(--text-muted);
    margin: 0;
}

/* Company */
.about-company {
    background: var(--bg-content);
    border: 1px solid rgba(212, 175, 55, 0.15);
    border-radius: 16px;
    padding: 1.8rem 2rem;
}

.company-title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--gold);
    margin-bottom: 1.1rem;
}

.company-details {
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
}

.company-row {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    font-size: 0.93rem;
    color: var(--text-muted);
}

.company-icon { font-size: 0.95rem; width: 20px; text-align: center; }

.company-label { font-weight: 600; color: var(--text-primary); }

.company-link {
    color: var(--gold);
    text-decoration: underline;
    text-underline-offset: 3px;
    text-decoration-color: rgba(212, 175, 55, 0.4);
    transition: color 0.2s;
}

.company-link:hover { color: var(--gold-hover); }

/* CTA */
.about-cta {
    text-align: center;
    background: linear-gradient(135deg, rgba(212,175,55,0.08), rgba(124,58,237,0.08));
    border: 1px solid rgba(212, 175, 55, 0.2);
    border-radius: 20px;
    padding: 3rem 2rem;
}

.about-cta h2 {
    font-size: 1.6rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 0.6rem;
}

.about-cta p {
    font-size: 0.97rem;
    color: var(--text-muted);
    margin-bottom: 1.6rem;
}

.cta-btn {
    display: inline-block;
    padding: 0.8rem 2rem;
    background: var(--gold);
    color: #0B0B0B;
    font-weight: 700;
    font-size: 0.95rem;
    border-radius: 10px;
    text-decoration: none;
    transition: background 0.2s, transform 0.2s;
}

.cta-btn:hover {
    background: var(--gold-hover);
    transform: translateY(-2px);
    text-decoration: none;
    color: #0B0B0B;
}

/* Responsive */
@media (max-width: 640px) {
    .about-text-grid,
    .offer-grid { grid-template-columns: 1fr; }
    .about-title { font-size: 1.8rem; }
    .about-page { padding: 2rem 0.9rem 4rem; }
}
</style>
@endsection
