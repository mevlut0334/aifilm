<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name'))</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Navbar theme-aware styling */
        .navbar {
            background-color: var(--bs-body-bg) !important;
            border-bottom: 1px solid var(--bs-border-color);
        }
        .navbar .navbar-brand,
        .navbar .nav-link {
            color: var(--bs-body-color) !important;
        }
        .navbar .nav-link:hover {
            color: var(--bs-link-hover-color) !important;
        }
        .navbar .btn-link {
            color: var(--bs-body-color) !important;
        }
        .navbar .btn-link:hover {
            color: var(--bs-link-hover-color) !important;
        }
        
        #theme-toggle {
            font-size: 1.25rem;
            cursor: pointer;
            transition: transform 0.2s;
        }
        #theme-toggle:hover {
            transform: scale(1.1);
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
                    <li class="nav-item">
                        <button id="theme-toggle" class="btn btn-link nav-link" title="Toggle theme">🔄</button>
                    </li>
                    @auth
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('custom-images.index') }}">Custom Görseller</a>
                        </li>
                    @endauth
                    <li class="nav-item">
                        <a class="nav-link" href="{{ LaravelLocalization::getLocalizedURL(null, route('packages.index', [], false)) }}">
                            {{ __('packages.Buy Tokens') }}
                            @auth
                                <span class="badge bg-primary">{{ auth()->user()->tokenBalance->balance ?? 0 }}</span>
                            @endauth
                        </a>
                    </li>
                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ LaravelLocalization::getLocalizedURL(null, route('login', [], false)) }}">{{ __('auth.Login') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ LaravelLocalization::getLocalizedURL(null, route('register', [], false)) }}">{{ __('auth.Register') }}</a>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ LaravelLocalization::getLocalizedURL(null, route('profile', [], false)) }}">{{ __('auth.Profile') }}</a>
                        </li>
                        <li class="nav-item">
                            <form method="POST" action="{{ LaravelLocalization::getLocalizedURL(null, route('logout', [], false)) }}">
                                @csrf
                                <button type="submit" class="btn btn-link nav-link">{{ __('auth.Logout') }}</button>
                            </form>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <main class="py-4">
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
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/theme.js') }}"></script>
</body>
</html>
