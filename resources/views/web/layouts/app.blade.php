<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name'))</title>
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

        /* Prevent horizontal overflow */
        html, body {
            overflow-x: hidden;
            max-width: 100%;
            margin: 0;
            padding: 0;
            background: var(--bg-primary);
        }
        
        /* Navbar styling */
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

        .navbar-toggler {
            border-color: var(--gold);
        }

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

        .navbar .btn-link:hover {
            color: var(--gold) !important;
        }

        .badge.bg-primary {
            background: linear-gradient(135deg, var(--gold), var(--gold-hover)) !important;
            color: var(--bg-primary);
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 6px;
            margin-left: 5px;
        }

        .alert {
            border-radius: 8px;
            border: none;
        }

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

            .nav-link {
                margin: 5px 0;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">{{ config('app.name') }}</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
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
    
    <main>
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/theme.js') }}"></script>
</body>
</html>
