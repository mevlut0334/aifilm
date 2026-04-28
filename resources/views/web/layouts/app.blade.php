<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name'))</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    @yield('meta')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* 🎨 Renk Paleti */
        :root {
            --bg-primary: #0B0B0B;
            --bg-secondary: #121212;
            --gold: #D4AF37;
            --gold-hover: #F5D97A;
            --purple: #7C3AED;
            --blue: #3B82F6;
            --text-primary: #FFFFFF;
            --text-secondary: #BFBFBF;
            --text-passive: #6B6B6B;
        }

        html, body {
            overflow-x: hidden;
            max-width: 100%;
            margin: 0;
            padding: 0;
            background: var(--bg-primary);
        }

        /* Navbar */
        .navbar {
            background: var(--bg-secondary) !important;
            border-bottom: 2px solid var(--gold);
            padding: 15px 0;
        }

        .navbar-brand {
            color: var(--gold) !important;
            font-weight: bold;
            font-size: 1.5rem;
            transition: all 0.3s ease;
        }

        .navbar-brand:hover {
            color: var(--gold-hover) !important;
            transform: scale(1.05);
        }

        .navbar-toggler { border-color: var(--gold); }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='%23D4AF37' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        .nav-link {
            color: var(--text-primary) !important;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 8px 15px !important;
            margin: 0 5px;
        }

        .nav-link:hover {
            color: var(--gold) !important;
            transform: translateY(-2px);
        }

        .navbar .btn-link {
            color: var(--text-primary) !important;
            font-weight: 500;
            text-decoration: none;
            padding: 8px 15px !important;
            transition: all 0.3s ease;
        }

        .navbar .btn-link:hover { color: var(--gold) !important; }

        .badge.bg-primary {
            background: linear-gradient(135deg, var(--gold), var(--gold-hover)) !important;
            color: var(--bg-primary);
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 6px;
            margin-left: 5px;
        }

        .alert { border-radius: 8px; border: none; }

        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid #22c55e;
            color: var(--text-primary);
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid #ef4444;
            color: var(--text-primary);
        }

        @media (max-width: 991px) {
            .navbar-collapse {
                background: var(--bg-secondary);
                padding: 15px;
                margin-top: 10px;
                border-radius: 8px;
                border: 1px solid var(--gold);
            }
            .nav-link { margin: 5px 0; }
        }

        /* ── Footer ─────────────────────────────── */
        .site-footer {
            background: var(--bg-secondary);
            border-top: 1px solid rgba(212, 175, 55, 0.2);
            padding: 3rem 0 1.5rem;
            margin-top: auto;
        }

        .footer-inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        .footer-top {
            display: grid;
            grid-template-columns: 1.6fr 1fr 1fr;
            gap: 2.5rem;
            margin-bottom: 2.5rem;
        }

        /* Brand col */
        .footer-brand-name {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--gold);
            margin-bottom: 0.5rem;
        }

        .footer-brand-desc {
            font-size: 0.83rem;
            color: var(--text-passive);
            line-height: 1.7;
            margin-bottom: 0.9rem;
        }

        .footer-company {
            font-size: 0.78rem;
            color: var(--text-passive);
            line-height: 1.6;
        }

        /* Link cols */
        .footer-col-title {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 1rem;
        }

        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 0.55rem;
        }

        .footer-links a {
            font-size: 0.88rem;
            color: var(--text-passive);
            text-decoration: none;
            transition: color 0.2s;
        }

        .footer-links a:hover { color: var(--gold); }

        /* Bottom bar */
        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.06);
            padding-top: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .footer-copy {
            font-size: 0.78rem;
            color: var(--text-passive);
        }

        .footer-legal-links {
            display: flex;
            gap: 1.2rem;
            flex-wrap: wrap;
        }

        .footer-legal-links a {
            font-size: 0.78rem;
            color: var(--text-passive);
            text-decoration: none;
            transition: color 0.2s;
        }

        .footer-legal-links a:hover { color: var(--gold); }

        @media (max-width: 768px) {
            .footer-top {
                grid-template-columns: 1fr;
                gap: 1.8rem;
            }
            .footer-bottom {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body style="display:flex; flex-direction:column; min-height:100vh;">

    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">{{ config('app.name') }}</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    {{-- About & Contact her zaman görünür --}}
                    <li class="nav-item">
                        <a class="nav-link" href="{{ LaravelLocalization::getLocalizedURL(null, route('about', [], false)) }}">
                            {{ app()->getLocale() === 'tr' ? 'Hakkımızda' : 'About' }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ LaravelLocalization::getLocalizedURL(null, route('contact', [], false)) }}">
                            {{ app()->getLocale() === 'tr' ? 'İletişim' : 'Contact' }}
                        </a>
                    </li>

                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ LaravelLocalization::getLocalizedURL(null, route('register', [], false)) }}">{{ __('auth.Register') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ LaravelLocalization::getLocalizedURL(null, route('login', [], false)) }}">{{ __('auth.Login') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ LaravelLocalization::getLocalizedURL(null, route('packages.index', [], false)) }}">
                                {{ __('packages.Buy Tokens') }}
                            </a>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ LaravelLocalization::getLocalizedURL(null, route('packages.index', [], false)) }}">
                                {{ __('packages.Buy Tokens') }}
                                <span class="badge bg-primary">{{ auth()->user()->tokenBalance->balance ?? 0 }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ LaravelLocalization::getLocalizedURL(null, route('generation-requests.index', [], false)) }}">
                                {{ __('navigation.my_templates') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ LaravelLocalization::getLocalizedURL(null, route('custom-images.index', [], false)) }}">
                                {{ __('navigation.my_images') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ LaravelLocalization::getLocalizedURL(null, route('custom-videos.index', [], false)) }}">
                                {{ __('navigation.my_videos') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ LaravelLocalization::getLocalizedURL(null, route('profile', [], false)) }}">{{ __('auth.Profile') }}</a>
                        </li>
                        <li class="nav-item">
                            <form method="POST" action="{{ LaravelLocalization::getLocalizedURL(null, route('logout', [], false)) }}" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-link nav-link">{{ __('auth.Logout') }}</button>
                            </form>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    @if(session('success'))
        <div class="container">
            <div class="alert alert-success">{{ session('success') }}</div>
        </div>
    @endif
    @if(session('error'))
        <div class="container">
            <div class="alert alert-danger">{{ session('error') }}</div>
        </div>
    @endif

    <main style="flex:1;">
        @yield('content')
    </main>

    {{-- ── FOOTER ─────────────────────────────────────────────── --}}
    <footer class="site-footer">
        <div class="footer-inner">
            <div class="footer-top">

                {{-- Brand --}}
                <div>
                    <div class="footer-brand-name">{{ config('app.name') }}</div>
                    <p class="footer-brand-desc">
                        {{ app()->getLocale() === 'tr'
                            ? 'Yapay zeka ile film ve video içerikleri oluşturun. Hızlı, verimli ve erişilebilir.'
                            : 'Create film and video content with AI. Fast, efficient and accessible.' }}
                    </p>
                    <div class="footer-company">
                        Gökbey Savunma Sanayi Limited Şirketi<br>
                        <a href="mailto:asilovstudio@gmail.com" style="color:var(--text-passive);text-decoration:none;">asilovstudio@gmail.com</a>
                    </div>
                </div>

                {{-- Platform Links --}}
                <div>
                    <div class="footer-col-title">
                        {{ app()->getLocale() === 'tr' ? 'Platform' : 'Platform' }}
                    </div>
                    <ul class="footer-links">
                        <li>
                            <a href="{{ LaravelLocalization::getLocalizedURL(null, route('about', [], false)) }}">
                                {{ app()->getLocale() === 'tr' ? 'Hakkımızda' : 'About Us' }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ LaravelLocalization::getLocalizedURL(null, route('packages.index', [], false)) }}">
                                {{ app()->getLocale() === 'tr' ? 'Paketler' : 'Packages' }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ LaravelLocalization::getLocalizedURL(null, route('templates.index', [], false)) }}">
                                {{ app()->getLocale() === 'tr' ? 'Şablonlar' : 'Templates' }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ LaravelLocalization::getLocalizedURL(null, route('contact', [], false)) }}">
                                {{ app()->getLocale() === 'tr' ? 'İletişim' : 'Contact' }}
                            </a>
                        </li>
                    </ul>
                </div>

                {{-- Legal Links --}}
                <div>
                    <div class="footer-col-title">
                        {{ app()->getLocale() === 'tr' ? 'Yasal' : 'Legal' }}
                    </div>
                    <ul class="footer-links">
                        <li>
                            <a href="{{ LaravelLocalization::getLocalizedURL(null, route('privacy', [], false)) }}">
                                {{ app()->getLocale() === 'tr' ? 'Gizlilik Politikası' : 'Privacy Policy' }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ LaravelLocalization::getLocalizedURL(null, route('terms', [], false)) }}">
                                {{ app()->getLocale() === 'tr' ? 'Kullanım Şartları' : 'Terms of Service' }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ LaravelLocalization::getLocalizedURL(null, route('refund', [], false)) }}">
                                {{ app()->getLocale() === 'tr' ? 'İade Politikası' : 'Refund Policy' }}
                            </a>
                        </li>
                    </ul>
                </div>

            </div>

            {{-- Bottom Bar --}}
            <div class="footer-bottom">
                <span class="footer-copy">
                    © {{ date('Y') }} Gökbey Savunma Sanayi Limited Şirketi.
                    {{ app()->getLocale() === 'tr' ? 'Tüm hakları saklıdır.' : 'All rights reserved.' }}
                </span>
                <div class="footer-legal-links">
                    <a href="{{ LaravelLocalization::getLocalizedURL(null, route('privacy', [], false)) }}">
                        {{ app()->getLocale() === 'tr' ? 'Gizlilik' : 'Privacy' }}
                    </a>
                    <a href="{{ LaravelLocalization::getLocalizedURL(null, route('terms', [], false)) }}">
                        {{ app()->getLocale() === 'tr' ? 'Şartlar' : 'Terms' }}
                    </a>
                    <a href="{{ LaravelLocalization::getLocalizedURL(null, route('refund', [], false)) }}">
                        {{ app()->getLocale() === 'tr' ? 'İade' : 'Refund' }}
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/theme.js') }}"></script>
</body>
</html>
