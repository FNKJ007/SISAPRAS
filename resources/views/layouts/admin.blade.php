<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') — Dinas Damkar</title>
    <meta name="description" content="Admin Panel Sistem Informasi Dinas Pemadam Kebakaran">

    {{-- Identik dengan app.blade.php milik user --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
    @stack('styles')
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- CSS admin: identik dengan app.css --}}
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ file_exists(public_path('css/admin.css')) ? filemtime(public_path('css/admin.css')) : '1' }}">
    @stack('styles')
</head>
<body>

    <div class="app-wrapper">

        {{-- ===================== SIDEBAR ADMIN ===================== --}}
        <aside class="sidebar" id="sidebar">

            <button class="sidebar-toggle" id="sidebarToggle" type="button" aria-label="Buka/tutup sidebar" aria-expanded="true">
                <i data-lucide="menu" class="icon-bars"></i>
                <span class="menu-label">Menu</span>
            </button>

            <nav class="sidebar-menu">

                <div>
                    {{-- === Dashboard === --}}
                    <div class="menu-group">
                        <a href="{{ route('admin.dashboard') }}"
                           class="menu-title {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                           style="text-decoration:none;">
                            <span class="menu-title-left">
                                <i data-lucide="layout-dashboard" class="menu-icon"></i>
                                <span>Dashboard</span>
                            </span>
                        </a>
                    </div>

                    {{-- === Pemeliharaan === --}}
                    <div class="menu-group">
                        <button class="menu-title"
                                type="button" data-target="menuPemeliharaan">
                            <span class="menu-title-left">
                                <i data-lucide="wrench" class="menu-icon"></i>
                                <span>Pemeliharaan</span>
                            </span>
                            <i data-lucide="chevron-down" class="chevron"></i>
                        </button>
                        <ul class="submenu" id="menuPemeliharaan">
                            <li>
                                <a href="{{ route('admin.pemeliharaan.pengajuan') }}"
                                   class="{{ request()->routeIs('admin.pemeliharaan.pengajuan') ? 'active' : '' }}">
                                    Pengajuan
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.pemeliharaan.pemeriksaan') }}"
                                   class="{{ request()->routeIs('admin.pemeliharaan.pemeriksaan') ? 'active' : '' }}">
                                    Pemeriksaan
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.pemeliharaan.pemeliharaan') }}"
                                   class="{{ request()->routeIs('admin.pemeliharaan.pemeliharaan') ? 'active' : '' }}">
                                    Pemeliharaan
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.pemeliharaan.invoice') }}"
                                   class="{{ request()->routeIs('admin.pemeliharaan.invoice') ? 'active' : '' }}">
                                    Invoice
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.pemeliharaan.kartu-kendali') }}"
                                   class="{{ request()->routeIs('admin.pemeliharaan.kartu-kendali') ? 'active' : '' }}">
                                    Kartu Kendali
                                </a>
                            </li>
                        </ul>
                    </div>

                    {{-- === Unit Pemadam === --}}
                    <div class="menu-group">
                        <button class="menu-title"
                                type="button" data-target="menuPemadam">
                            <span class="menu-title-left">
                                <i data-lucide="flame" class="menu-icon"></i>
                                <span>Unit Pemadam</span>
                            </span>
                            <i data-lucide="chevron-down" class="chevron"></i>
                        </button>
                        <ul class="submenu" id="menuPemadam">
                            <li>
                                <a href="{{ route('admin.unit-pemadam.data-unit') }}"
                                   class="{{ request()->routeIs('admin.unit-pemadam.data-unit') ? 'active' : '' }}">
                                    Data Unit
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.unit-pemadam.pengecekan') }}"
                                   class="{{ request()->routeIs('admin.unit-pemadam.pengecekan') ? 'active' : '' }}">
                                    Pengecekan
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.unit-pemadam.riwayat') }}"
                                   class="{{ request()->routeIs('admin.unit-pemadam.riwayat') ? 'active' : '' }}">
                                    Riwayat
                                </a>
                            </li>
                        </ul>
                    </div>

                    {{-- === Unit Rescue === --}}
                    <div class="menu-group">
                        <button class="menu-title"
                                type="button" data-target="menuRescue">
                            <span class="menu-title-left">
                                <i data-lucide="life-buoy" class="menu-icon"></i>
                                <span>Unit Rescue</span>
                            </span>
                            <i data-lucide="chevron-down" class="chevron"></i>
                        </button>
                        <ul class="submenu" id="menuRescue">
                            <li>
                                <a href="{{ route('admin.unit-rescue.data-unit') }}"
                                   class="{{ request()->routeIs('admin.unit-rescue.data-unit') ? 'active' : '' }}">
                                    Data Unit
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.unit-rescue.pengecekan') }}"
                                   class="{{ request()->routeIs('admin.unit-rescue.pengecekan') ? 'active' : '' }}">
                                    Pengecekan
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.unit-rescue.riwayat') }}"
                                   class="{{ request()->routeIs('admin.unit-rescue.riwayat') ? 'active' : '' }}">
                                    Riwayat
                                </a>
                            </li>
                        </ul>
                    </div>

                    {{-- === Command Center === --}}
                    <div class="menu-group">
                        <button class="menu-title"
                                type="button" data-target="menuCommand">
                            <span class="menu-title-left">
                                <i data-lucide="radio-tower" class="menu-icon"></i>
                                <span>Command Center</span>
                            </span>
                            <i data-lucide="chevron-down" class="chevron"></i>
                        </button>
                        <ul class="submenu" id="menuCommand">
                            <li>
                                <a href="{{ route('admin.command-center.data-peralatan') }}"
                                   class="{{ request()->routeIs('admin.command-center.data-peralatan') ? 'active' : '' }}">
                                    Data Peralatan
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.command-center.pengecekan') }}"
                                   class="{{ request()->routeIs('admin.command-center.pengecekan') ? 'active' : '' }}">
                                    Pengecekan
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.command-center.riwayat') }}"
                                   class="{{ request()->routeIs('admin.command-center.riwayat') ? 'active' : '' }}">
                                    Riwayat
                                </a>
                            </li>
                        </ul>
                    </div>

                    {{-- === APAR & Kejadian === --}}
                    <div class="menu-group">
                        <button class="menu-title"
                                type="button" data-target="menuApar">
                            <span class="menu-title-left">
                                <i data-lucide="shield-alert" class="menu-icon"></i>
                                <span>APAR & Kejadian</span>
                            </span>
                            <i data-lucide="chevron-down" class="chevron"></i>
                        </button>
                        <ul class="submenu" id="menuApar">
                            <li>
                                <a href="{{ route('admin.apar.data-apar') }}"
                                   class="{{ request()->routeIs('admin.apar.data-apar') ? 'active' : '' }}">
                                    Data APAR
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.apar.laporan-kejadian') }}"
                                   class="{{ request()->routeIs('admin.apar.laporan-kejadian') ? 'active' : '' }}">
                                    Laporan Kejadian
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.apar.monitoring') }}"
                                   class="{{ request()->routeIs('admin.apar.monitoring') ? 'active' : '' }}">
                                    Monitoring Kejadian
                                </a>
                            </li>
                        </ul>
                    </div>

                    {{-- === Laporan === --}}
                    <div class="menu-group">
                        <button class="menu-title"
                                type="button" data-target="menuLaporan">
                            <span class="menu-title-left">
                                <i data-lucide="file-bar-chart" class="menu-icon"></i>
                                <span>Laporan</span>
                            </span>
                            <i data-lucide="chevron-down" class="chevron"></i>
                        </button>
                        <ul class="submenu" id="menuLaporan">
                            <li>
                                <a href="{{ route('admin.laporan.pemeliharaan') }}"
                                   class="{{ request()->routeIs('admin.laporan.pemeliharaan') ? 'active' : '' }}">
                                    Pemeliharaan
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.laporan.pemadam') }}"
                                   class="{{ request()->routeIs('admin.laporan.pemadam') ? 'active' : '' }}">
                                    Pemadam
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.laporan.rescue') }}"
                                   class="{{ request()->routeIs('admin.laporan.rescue') ? 'active' : '' }}">
                                    Rescue
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.laporan.command-center') }}"
                                   class="{{ request()->routeIs('admin.laporan.command-center') ? 'active' : '' }}">
                                    Command Center
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.laporan.bulanan') }}"
                                   class="{{ request()->routeIs('admin.laporan.bulanan') ? 'active' : '' }}">
                                    Laporan Bulanan
                                </a>
                            </li>
                        </ul>
                    </div>

                    {{-- === Pengaturan === --}}
                    <div class="menu-group">
                        <a href="{{ route('admin.pengaturan') }}"
                           class="menu-title {{ request()->routeIs('admin.pengaturan') ? 'active' : '' }}"
                           style="text-decoration:none;">
                            <span class="menu-title-left">
                                <i data-lucide="settings" class="menu-icon"></i>
                                <span>Pengaturan</span>
                            </span>
                        </a>
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

        {{-- Backdrop gelap (mobile) --}}
        <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

        {{-- ===================== MAIN AREA ===================== --}}
        <div class="main-area">

            <header class="topbar" style="display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <button class="mobile-menu-btn" id="mobileMenuBtn" type="button" aria-label="Buka menu">
                        <i data-lucide="menu" class="icon-bars"></i>
                    </button>

                    <div class="topbar-logos">
                        <img src="{{ asset('images/logo-kabupaten.png') }}" alt="Logo Instansi"         class="logo logo-left">
                        <img src="{{ asset('images/logo-damkar.png') }}"    alt="Logo Yudha Brama Jaya"  class="logo logo-right">
                    </div>

                    {{-- SISAPRAS Brand di Header (Tanpa Logo) --}}
                    <div class="topbar-brand">
                        <div class="topbar-brand-text">
                            <span class="topbar-brand-title">SISAPRAS</span>
                            <span class="topbar-brand-subtitle">Sistem Informasi Sarana Prasarana</span>
                        </div>
                    </div>
                </div>

                {{-- Area User Info di Topbar --}}
                <div class="user-card-topbar">
                    <i data-lucide="user-check"></i>
                    <span>{{ auth()->user()->name ?? 'Admin' }}</span>
                </div>
            </header>

            <main class="content-area">
                @yield('content')
            </main>

        </div>
    </div>

    {{-- Script app.js --}}
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
