@extends('web.layouts.app')

@section('title', trans('packages.Buy Tokens'))

@section('content')

<style>
.container h1 {
    color: #FFFFFF !important;
    font-weight: bold;
}
.container .lead {
    color: #FFFFFF !important;
}
.balance-badge {
    background: rgba(212, 175, 55, 0.15) !important;
    color: #D4AF37 !important;
    border: 1px solid rgba(212, 175, 55, 0.3);
    font-size: 1rem !important;
}

/* Token yükle butonu */
.load-tokens-wrap {
    text-align: center;
    margin-top: 1.2rem;
}
.btn-load-tokens {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: #D4AF37 !important;
    color: #0B0B0B !important;
    border: none !important;
    font-weight: 700 !important;
    font-size: 1.05rem;
    border-radius: 8px !important;
    padding: 0.8rem 2.2rem !important;
    text-decoration: none;
    transition: background 0.2s !important;
}
.btn-load-tokens:hover {
    background: #F5D97A !important;
    color: #0B0B0B !important;
}

/* Mobil uygulama bloğu */
.mobile-redirect-box {
    margin: 2.5rem auto 0 auto;
    max-width: 640px;
    padding: 2rem;
    background: #121212;
    border: 1px solid #2a2a2a;
    border-radius: 14px;
    text-align: center;
}
.store-links {
    display: flex;
    justify-content: center;
    gap: 1.2rem;
    flex-wrap: wrap;
}
.store-link-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}
.store-link-item span.platform-label {
    color: #6B6B6B;
    font-size: 0.8rem;
}
.btn-store {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: #D4AF37 !important;
    color: #0B0B0B !important;
    border: none !important;
    font-weight: 700 !important;
    border-radius: 8px !important;
    padding: 0.65rem 1.3rem !important;
    text-decoration: none;
    transition: background 0.2s !important;
}
.btn-store:hover {
    background: #F5D97A !important;
    color: #0B0B0B !important;
}
.btn-store-soon {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: transparent !important;
    color: #6B6B6B !important;
    border: 1px solid #2a2a2a !important;
    font-weight: 600 !important;
    border-radius: 8px !important;
    padding: 0.65rem 1.3rem !important;
    cursor: default;
}
</style>

<div class="container">
    <div class="text-center mb-4">
        <h1>@trans_safe('packages.Buy Tokens')</h1>
        @auth
            <p class="lead">@trans_safe('tokens.current_balance'):
                <span class="badge balance-badge fs-5">
                    {{ auth()->user()->tokenBalance->balance ?? 0 }} @trans_safe('packages.tokens')
                </span>
            </p>
        @else
            <p class="lead" style="color: #6B6B6B;">@trans_safe('packages.login_to_see_balance')</p>
        @endauth

        <div class="load-tokens-wrap">
            <a href="https://www.shopier.com/Asilov/51476418" class="btn-load-tokens" target="_blank" rel="noopener">
                <i class="bi bi-lightning-charge-fill"></i> @trans_safe('packages.load_tokens')
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert" style="background: rgba(124,58,237,0.1); border: 1px solid rgba(124,58,237,0.3); color: #7C3AED;">
            {{ session('success') }}
        </div>
    @endif

    {{-- Mobil uygulama indirme bağlantıları --}}
    <div class="mobile-redirect-box">
        <div class="store-links">
            <div class="store-link-item">
                <span class="platform-label">@trans_safe('packages.android_label')</span>
                <a href="https://play.google.com/store/apps/details?id=com.asilov.app&pcampaignid=web_share" class="btn-store" target="_blank" rel="noopener">
                    <i class="bi bi-google-play"></i> @trans_safe('packages.download_android')
                </a>
            </div>
            <div class="store-link-item">
                <span class="platform-label">@trans_safe('packages.ios_label')</span>
                <span class="btn-store-soon">
                    <i class="bi bi-apple"></i> @trans_safe('packages.ios_coming_soon')
                </span>
            </div>
        </div>
    </div>
</div>

@endsection
