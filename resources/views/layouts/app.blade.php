<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Home')</title>

    {{-- Google Font (opsional, boleh dihapus kalau tidak ada koneksi internet di server) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.tailwindcss.com"></script>
     @stack('styles')
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ file_exists(public_path('css/app.css')) ? filemtime(public_path('css/app.css')) : '1' }}">
    @stack('styles')
</head>
<body>

    <div class="app-wrapper">

        {{-- ===================== SIDEBAR ===================== --}}
        <aside class="sidebar" id="sidebar">

            <button class="sidebar-toggle" id="sidebarToggle" type="button" aria-label="Buka/tutup sidebar" aria-expanded="true">
                <i data-lucide="menu" class="icon-bars"></i>
                <span class="menu-label">Menu</span>
            </button>

            <nav class="sidebar-menu">

            {{-- === Home === --}} 
                 <div class="menu-group">
        <a href="{{ route('home') }}" class="menu-title menu-title-link {{ request()->routeIs('home') ? 'active' : '' }}">
            <span class="menu-title-left">
                <i data-lucide="home" class="menu-icon"></i>
                <span>Home</span>
            </span>
        </a>
    </div>
            {{-- === Pemeliharaan === --}}
                <div class="menu-group">
                    <button class="menu-title" type="button" data-target="menuPemeliharaan" aria-expanded="true">
                        <span class="menu-title-left">
                            <i data-lucide="wrench" class="menu-icon"></i>
                            <span>Pemeliharaan</span>
                        </span>
                        <i data-lucide="chevron-down" class="chevron"></i>
                    </button>
                    <ul class="submenu open" id="menuPemeliharaan">
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
                    <button class="menu-title" type="button" data-target="menuPemadam" aria-expanded="true">
                        <span class="menu-title-left">
                            <i data-lucide="flame" class="menu-icon"></i>
                            <span>Pemadam</span>
                        </span>
                        <i data-lucide="chevron-down" class="chevron"></i>
                    </button>
                    <ul class="submenu open" id="menuPemadam">
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
                    <button class="menu-title" type="button" data-target="menuRescue" aria-expanded="true">
                        <span class="menu-title-left">
                            <i data-lucide="life-buoy" class="menu-icon"></i>
                            <span>Rescue</span>
                        </span>
                        <i data-lucide="chevron-down" class="chevron"></i>
                    </button>
                    <ul class="submenu open" id="menuRescue">
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
                    <button class="menu-title" type="button" data-target="menuCommand" aria-expanded="true">
                        <span class="menu-title-left">
                            <i data-lucide="radio-tower" class="menu-icon"></i>
                            <span>Command Center</span>
                        </span>
                        <i data-lucide="chevron-down" class="chevron"></i>
                    </button>
                    <ul class="submenu open" id="menuCommand">
                        <li>
                            <a href="{{ route('alat-cc.cek-alat-cc') }}"
                               class="{{ request()->routeIs('alat-cc.cek-alat-cc') ? 'active' : '' }}">
                                Cek Harian Alat
                            </a>
                        </li>
                    </ul>
                </div>

            </nav>
        </aside>

        {{-- Backdrop gelap, muncul di belakang sidebar saat sidebar dibuka di layar kecil --}}
        <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

        {{-- ===================== MAIN AREA ===================== --}}
        <div class="main-area">

            <header class="topbar">
                <button class="mobile-menu-btn" id="mobileMenuBtn" type="button" aria-label="Buka menu">
                    <i data-lucide="menu" class="icon-bars"></i>
                </button>

                <div class="topbar-logos">
                    <img src="{{ asset('images/logo-kabupaten.png') }}" alt="Logo Instansi" class="logo logo-left">
                    <img src="{{ asset('images/logo-damkar.png') }}" alt="Logo Yudha Brama Jaya" class="logo logo-right">
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
        // Render semua icon Lucide setelah DOM siap
        document.addEventListener('DOMContentLoaded', function () {
            if (window.lucide) {
                lucide.createIcons();
            }
        });
    </script>
    @stack('scripts')
</body>
</html>