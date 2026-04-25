<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            overflow-x: hidden;
        }
        
        /* Sidebar styling */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 250px;
            background-color: var(--bs-body-bg);
            border-right: 1px solid var(--bs-border-color);
            overflow-y: auto;
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 1.5rem 1rem;
            border-bottom: 1px solid var(--bs-border-color);
        }
        
        .sidebar-brand {
            font-size: 1.25rem;
            font-weight: bold;
            color: var(--bs-body-color);
            text-decoration: none;
            display: block;
        }
        
        .sidebar-nav {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-nav-item {
            border-bottom: 1px solid var(--bs-border-color);
        }
        
        .sidebar-nav-link {
            display: block;
            padding: 1rem 1.5rem;
            color: var(--bs-body-color);
            text-decoration: none;
            transition: background-color 0.2s, padding-left 0.2s;
        }
        
        .sidebar-nav-link:hover {
            background-color: var(--bs-tertiary-bg);
            padding-left: 2rem;
            color: var(--bs-body-color);
        }
        
        .sidebar-nav-link.active {
            background-color: var(--bs-primary);
            color: white;
        }
        
        .sidebar-nav-link .badge {
            float: right;
            font-size: 0.75rem;
        }
        
        /* Theme selector */
        .theme-selector {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--bs-border-color);
        }
        
        .theme-selector label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .theme-selector select {
            width: 100%;
            padding: 0.5rem;
            background-color: var(--bs-body-bg);
            color: var(--bs-body-color);
            border: 1px solid var(--bs-border-color);
            border-radius: 0.25rem;
        }
        
        /* User info */
        .sidebar-user {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--bs-border-color);
        }
        
        .sidebar-logout {
            padding: 1rem 1.5rem;
        }
        
        .sidebar-logout form {
            margin: 0;
        }
        
        .sidebar-logout button {
            width: 100%;
            padding: 0.5rem;
            background-color: var(--bs-danger);
            color: white;
            border: none;
            border-radius: 0.25rem;
            cursor: pointer;
        }
        
        .sidebar-logout button:hover {
            background-color: var(--bs-danger-border-subtle);
        }
        
        /* Main content */
        .main-content {
            margin-left: 250px;
            padding: 2rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    @auth('admin')
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">AI Film Admin</a>
        </div>
        
        <!-- Theme Selector -->
        <div class="theme-selector">
            <label for="theme-select">Tema Seçimi</label>
            <select id="theme-select" class="form-select">
                <option value="light">Açık Tema</option>
                <option value="dark">Koyu Tema</option>
            </select>
        </div>
        
        <!-- Navigation Menu -->
        <ul class="sidebar-nav">
            <li class="sidebar-nav-item">
                <a class="sidebar-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" 
                   href="{{ route('admin.dashboard') }}">
                    {{ __('admin.Dashboard') }}
                    @php
                        // Count all pending requests
                        $pendingRequestsCount = 0;
                        
                        // 1. Custom Video Requests - pending
                        $pendingRequestsCount += \App\Models\CustomVideoRequest::where('status', 'pending')->count();
                        
                        // 2. Custom Video Requests - completed with pending segments
                        $pendingRequestsCount += \App\Models\CustomVideoRequest::where('status', 'completed')
                            ->whereHas('segments', function ($query) {
                                $query->where('status', 'pending');
                            })->count();
                        
                        // 3. Custom Video Requests - with pending edit requests
                        $pendingRequestsCount += \App\Models\CustomVideoRequest::whereHas('segments.editRequests', function ($query) {
                            $query->where('status', 'pending');
                        })->count();
                        
                        // 4. Generation Requests - pending
                        $pendingRequestsCount += \App\Models\GenerationRequest::where('status', 'pending')->count();
                        
                        // 5. Custom Images - pending
                        $pendingRequestsCount += \App\Models\CustomImage::where('status', 'pending')->count();
                    @endphp
                    @if($pendingRequestsCount > 0)
                        <span class="badge bg-danger ms-2">{{ $pendingRequestsCount }}</span>
                    @endif
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a class="sidebar-nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" 
                   href="{{ route('admin.users.index') }}">
                    {{ __('admin.Users') }}
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a class="sidebar-nav-link {{ request()->routeIs('admin.packages.*') ? 'active' : '' }}" 
                   href="{{ route('admin.packages.index') }}">
                    {{ __('admin.Packages') }}
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a class="sidebar-nav-link {{ request()->routeIs('admin.templates.*') ? 'active' : '' }}" 
                   href="{{ route('admin.templates.index') }}">
                    Templates
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a class="sidebar-nav-link {{ request()->routeIs('admin.generation-requests.*') ? 'active' : '' }}" 
                   href="{{ route('admin.generation-requests.index') }}">
                    Talepler
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a class="sidebar-nav-link {{ request()->routeIs('admin.custom-images.*') ? 'active' : '' }}" 
                   href="{{ route('admin.custom-images.index') }}">
                    Custom Görseller
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a class="sidebar-nav-link {{ request()->routeIs('admin.custom-videos.*') ? 'active' : '' }}" 
                   href="{{ route('admin.custom-videos.index') }}">
                    Custom Videolar
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a class="sidebar-nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" 
                   href="{{ route('admin.settings.index') }}">
                    {{ __('admin.Settings') }}
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a class="sidebar-nav-link {{ request()->routeIs('admin.admins.*') ? 'active' : '' }}" 
                   href="{{ route('admin.admins.index') }}">
                    Admin Yönetimi
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a class="sidebar-nav-link {{ request()->routeIs('admin.sliders.*') ? 'active' : '' }}" 
                   href="{{ route('admin.sliders.index') }}">
                    Slider Yönetimi
                </a>
            </li>
        </ul>
        
        <!-- User Info -->
        <div class="sidebar-user">
            <strong>{{ Auth::guard('admin')->user()->name }}</strong>
            <div class="text-muted small">{{ Auth::guard('admin')->user()->email }}</div>
        </div>
        
        <!-- Logout -->
        <div class="sidebar-logout">
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit">Çıkış Yap</button>
            </form>
        </div>
    </aside>
    @endauth

    <!-- Main Content -->
    <main class="main-content">
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Theme switcher
        const themeSelect = document.getElementById('theme-select');
        const htmlElement = document.documentElement;
        
        // Load saved theme
        const savedTheme = localStorage.getItem('admin-theme') || 'light';
        htmlElement.setAttribute('data-bs-theme', savedTheme);
        if (themeSelect) {
            themeSelect.value = savedTheme;
        }
        
        // Theme change handler
        if (themeSelect) {
            themeSelect.addEventListener('change', function() {
                const theme = this.value;
                htmlElement.setAttribute('data-bs-theme', theme);
                localStorage.setItem('admin-theme', theme);
            });
        }
    </script>
</body>
</html>
