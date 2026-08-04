<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Home') — Dinas Damkar</title>

    {{-- Google Font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
     @stack('styles')
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ file_exists(public_path('css/app.css')) ? filemtime(public_path('css/app.css')) : '1' }}">
    @stack('styles')
</head>
<body>

    <div class="app-wrapper">

        {{-- ===================== SIDEBAR USER ===================== --}}
        <aside class="sidebar" id="sidebar">

            <button class="sidebar-toggle" id="sidebarToggle" type="button" aria-label="Buka/tutup sidebar" aria-expanded="true">
                <i data-lucide="menu" class="icon-bars"></i>
                <span class="menu-label">Menu</span>
            </button>

            <nav class="sidebar-menu">

                <div>
                    {{-- === Home === --}}
                    <div class="menu-group">
                        <a href="{{ route('home') }}"
                           class="menu-title"
                           style="text-decoration:none; {{ request()->routeIs('home', 'home.index') ? 'background-color: rgba(255,255,255,0.95); color: var(--sidebar-red); box-shadow: 0 1px 3px rgba(0,0,0,0.15);' : '' }}">
                            <span class="menu-title-left">
                                <i data-lucide="home" class="menu-icon"></i>
                                <span>Home</span>
                            </span>
                        </a>
                    </div>

                    {{-- === Pemeliharaan === --}}
                    <div class="menu-group">
                        <button class="menu-title {{ request()->routeIs('pemeliharaan.*') ? 'active' : '' }}" type="button" data-target="menuPemeliharaan">
                            <span class="menu-title-left">
                                <i data-lucide="wrench" class="menu-icon"></i>
                                <span>Pemeliharaan</span>
                            </span>
                            <i data-lucide="chevron-down" class="chevron"></i>
                        </button>
                        <ul class="submenu {{ request()->routeIs('pemeliharaan.*') ? 'open' : '' }}" id="menuPemeliharaan">
                            <li>
                                <a href="{{ route('pemeliharaan.pengajuan') }}"
                                   class="{{ request()->routeIs('pemeliharaan.pengajuan') ? 'active' : '' }}">
                                    Pengajuan
                                </a>
                            </li>
                        </ul>
                    </div>

                    {{-- === Unit Pemadam === --}}
                    <div class="menu-group">
                        <button class="menu-title {{ request()->routeIs('unit-pemadam.*', 'alat-pemadam.*') ? 'active' : '' }}" type="button" data-target="menuPemadam">
                            <span class="menu-title-left">
                                <i data-lucide="flame" class="menu-icon"></i>
                                <span>Pemadam</span>
                            </span>
                            <i data-lucide="chevron-down" class="chevron"></i>
                        </button>
                        <ul class="submenu {{ request()->routeIs('unit-pemadam.*', 'alat-pemadam.*') ? 'open' : '' }}" id="menuPemadam">
                            <li>
                                <a href="{{ route('unit-pemadam.cek-harian-unit') }}"
                                   class="{{ request()->routeIs('unit-pemadam.cek-harian-unit') ? 'active' : '' }}">
                                    Cek Harian Unit
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('alat-pemadam.cek-harian-alat') }}"
                                   class="{{ request()->routeIs('alat-pemadam.cek-harian-alat') ? 'active' : '' }}">
                                    Cek Harian Alat
                                </a>
                            </li>
                        </ul>
                    </div>

                    {{-- === Unit Rescue === --}}
                    <div class="menu-group">
                        <button class="menu-title {{ request()->routeIs('unit-rescue.*', 'alat-rescue.*') ? 'active' : '' }}" type="button" data-target="menuRescue">
                            <span class="menu-title-left">
                                <i data-lucide="life-buoy" class="menu-icon"></i>
                                <span>Rescue</span>
                            </span>
                            <i data-lucide="chevron-down" class="chevron"></i>
                        </button>
                        <ul class="submenu {{ request()->routeIs('unit-rescue.*', 'alat-rescue.*') ? 'open' : '' }}" id="menuRescue">
                            <li>
                                <a href="{{ route('unit-rescue.cek-harian-unit-rescue') }}"
                                   class="{{ request()->routeIs('unit-rescue.cek-harian-unit-rescue') ? 'active' : '' }}">
                                    Cek Harian Unit
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('alat-rescue.cek-harian-alat') }}"
                                   class="{{ request()->routeIs('alat-rescue.cek-harian-alat') ? 'active' : '' }}">
                                    Cek Harian Alat
                                </a>
                            </li>
                        </ul>
                    </div>

                    {{-- === Command Center === --}}
                    <div class="menu-group">
                        <button class="menu-title {{ request()->routeIs('alat-cc.*') ? 'active' : '' }}" type="button" data-target="menuCommand">
                            <span class="menu-title-left">
                                <i data-lucide="radio-tower" class="menu-icon"></i>
                                <span>Command Center</span>
                            </span>
                            <i data-lucide="chevron-down" class="chevron"></i>
                        </button>
                        <ul class="submenu {{ request()->routeIs('alat-cc.*') ? 'open' : '' }}" id="menuCommand">
                            <li>
                                <a href="{{ route('alat-cc.cek-alat-cc') }}"
                                   class="{{ request()->routeIs('alat-cc.cek-alat-cc') ? 'active' : '' }}">
                                    Cek Harian Alat
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- === Logout (Bawah Sidebar) === --}}
                <div class="menu-group" style="margin-top: 15px; padding-top: 10px; border-top: 1px solid var(--sidebar-border);">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="menu-title" style="width: 100%; border: none; background: none; color: #fff; cursor: pointer; text-decoration: none;">
                            <span class="menu-title-left">
                                <i data-lucide="log-out" class="menu-icon"></i>
                                <span>Logout</span>
                            </span>
                        </button>
                    </form>
                </div>

            </nav>
        </aside>

        {{-- Backdrop gelap --}}
        <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

        {{-- ===================== MAIN AREA ===================== --}}
        <div class="main-area">

            <header class="topbar" style="display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <button class="mobile-menu-btn" id="mobileMenuBtn" type="button" aria-label="Buka menu">
                        <i data-lucide="menu" class="icon-bars"></i>
                    </button>

                    <div class="topbar-logos">
                        <img src="{{ asset('images/logo-kabupaten.png') }}" alt="Logo Instansi" class="logo logo-left">
                        <img src="{{ asset('images/logo-damkar.png') }}" alt="Logo Yudha Brama Jaya" class="logo logo-right">
                    </div>
                </div>

                {{-- Area User Info di Topbar --}}
                <div style="display: flex; align-items: center; gap: 12px; margin-left: auto;">
                    <span style="font-size: 13px; font-weight: 500; color: #333; display: flex; align-items: center; gap: 6px;">
                        <i data-lucide="user" style="width: 16px; height: 16px; color: #C0201F;"></i>
                        <span>{{ auth()->user()->name ?? 'User' }}</span>
                    </span>
                </div>
            </header>
            <div class="topbar-accent"></div>

            <main class="content-area">
                @yield('content')
            </main>

        </div>
    </div>

    <script src="{{ asset('js/app.js') }}?v={{ file_exists(public_path('js/app.js')) ? filemtime(public_path('js/app.js')) : '1' }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.lucide) {
                lucide.createIcons();
            }
        });
    </script>
    @stack('scripts')
</body>
</html>