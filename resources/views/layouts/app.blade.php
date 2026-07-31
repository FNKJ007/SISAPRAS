<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Home')</title>

    {{-- Google Font (opsional, boleh dihapus kalau tidak ada koneksi internet di server) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ file_exists(public_path('css/app.css')) ? filemtime(public_path('css/app.css')) : '1' }}">
    @stack('styles')
</head>
<body>

    <div class="app-wrapper">

        {{-- ===================== SIDEBAR ===================== --}}
        <aside class="sidebar" id="sidebar">

            <div class="sidebar-toggle" id="sidebarToggle">
                <span class="icon-bars">&#9776;</span>
                <span class="menu-label">Menu</span>
            </div>

            <nav class="sidebar-menu">

                {{-- === Pemeliharaan === --}}
                <div class="menu-group">
                    <button class="menu-title" data-target="menuPemeliharaan">
                        <span>Pemeliharaan</span>
                        <span class="chevron">&#9662;</span>
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
                    <button class="menu-title" data-target="menuPemadam">
                        <span>Unit Pemadam</span>
                        <span class="chevron">&#9662;</span>
                    </button>
                    <ul class="submenu open" id="menuPemadam">
                        <li>
                            <a href="{{ route('unit-pemadam.cek-harian-unit') }}"
                               class="{{ request()->routeIs('unit-pemadam.cek-harian-unit') ? 'active' : '' }}">
                                Cek Harian Unit
                            </a>
                        </li>
                        <li><a href="#">Cek Harian Alat</a></li>
                    </ul>
                </div>

                {{-- === Unit Rescue === --}}
                <div class="menu-group">
                    <button class="menu-title" data-target="menuRescue">
                        <span>Unit Rescue</span>
                        <span class="chevron">&#9662;</span>
                    </button>
                    <ul class="submenu open" id="menuRescue">
                        <li><a href="#">Cek Harian Unit</a></li>
                        <li><a href="#">Cek Harian Alat</a></li>
                    </ul>
                </div>

                {{-- === Command Center === --}}
                <div class="menu-group">
                    <button class="menu-title" data-target="menuCommand">
                        <span>Command Center</span>
                        <span class="chevron">&#9662;</span>
                    </button>
                    <ul class="submenu open" id="menuCommand">
                        <li><a href="#">Cek Sistem</a></li>
                        <li><a href="#">Laporan</a></li>
                    </ul>
                </div>

                {{-- === APAR === --}}
                <div class="menu-group">
                    <button class="menu-title" data-target="menuApar">
                        <span>APAR</span>
                        <span class="chevron">&#9662;</span>
                    </button>
                    <ul class="submenu open" id="menuApar">
                        <li><a href="#">Data APAR</a></li>
                        <li><a href="#">Cek Sistem</a></li>
                    </ul>
                </div>

            </nav>
        </aside>

        {{-- Backdrop gelap, muncul di belakang sidebar saat sidebar dibuka di layar kecil --}}
        <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

        {{-- ===================== MAIN AREA ===================== --}}
        <div class="main-area">

            <header class="topbar">
                {{-- Tombol hamburger ini HANYA tampil di layar kecil (lihat app.css).
                     Sengaja diletakkan di dalam header (bukan position:fixed) supaya
                     tidak pernah menimpa logo, mengikuti alur flex header biasa. --}}
                <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Buka menu">
                    <span class="icon-bars">&#9776;</span>
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
    @stack('scripts')
</body>
</html>
