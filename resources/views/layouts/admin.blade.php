<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard Admin') — BRAHMA | Berkala Rawat Armada, Alat, dan Sarana DAMKAR</title>
    <meta name="description" content="Admin Panel Sistem Informasi Dinas Pemadam Kebakaran">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ asset('images/fav_icon.jpeg') }}">
    <link rel="shortcut icon" type="image/jpeg" href="{{ asset('images/fav_icon.jpeg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/fav_icon.jpeg') }}">

    {{-- Identik dengan app.blade.php milik user --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }

        /* Smooth Page Transition Animation */
        @keyframes smoothPageFadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .content-area, .dashboard-container {
            animation: smoothPageFadeIn 0.22s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        /* Top Progress Bar on Navigation */
        #nprogress-bar {
            position: fixed;
            top: 0;
            left: 0;
            height: 3px;
            width: 0%;
            background: linear-gradient(90deg, #C0201F, #EF4444, #F59E0B);
            box-shadow: 0 0 10px rgba(192, 32, 31, 0.7), 0 0 5px rgba(239, 68, 68, 0.5);
            z-index: 999999;
            transition: width 0.25s ease, opacity 0.3s ease;
            pointer-events: none;
            opacity: 0;
            border-radius: 0 4px 4px 0;
        }
    </style>

    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ file_exists(public_path('css/admin.css')) ? filemtime(public_path('css/admin.css')) : '1' }}">
    @stack('styles')
    <style>
        .search-autocomplete-menu { position:absolute; z-index:100000; left:0; right:0; top:calc(100% + 4px); display:none; max-height:220px; overflow-y:auto; background:#fff; border:1px solid #cbd5e1; border-radius:8px; box-shadow:0 10px 25px rgba(15,23,42,.16); }
        .search-autocomplete-option { display:block; width:100%; padding:8px 10px; border:0; border-bottom:1px solid #f1f5f9; background:#fff; color:#334155; text-align:left; font-size:12px; cursor:pointer; }
        .search-autocomplete-option:hover, .search-autocomplete-option.is-active { background:#eff6ff; color:#1d4ed8; }
    </style>
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

                    {{-- === Master Data === --}}
                    <div class="menu-group">
                        <button class="menu-title"
                                type="button" data-target="menuMasterData">
                            <span class="menu-title-left">
                                <i data-lucide="database" class="menu-icon"></i>
                                <span>Master Data</span>
                            </span>
                            <i data-lucide="chevron-down" class="chevron"></i>
                        </button>
                        <ul class="submenu" id="menuMasterData">
                            <li>
                                <a href="{{ route('admin.pemeliharaan.data-unit') }}"
                                   class="{{ request()->routeIs('admin.pemeliharaan.data-unit*') ? 'active' : '' }}">
                                    Data Unit
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.pemeliharaan.data-peralatan') }}"
                                   class="{{ request()->routeIs('admin.pemeliharaan.data-peralatan*') ? 'active' : '' }}">
                                    Data Peralatan
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.pemeliharaan.data-pos') }}"
                                   class="{{ request()->routeIs('admin.pemeliharaan.data-pos*') ? 'active' : '' }}">
                                    Data Pos
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.pemeliharaan.data-regu') }}"
                                   class="{{ request()->routeIs('admin.pemeliharaan.data-regu*') ? 'active' : '' }}">
                                    Data Regu
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.pemeliharaan.data-pegawai') }}"
                                   class="{{ request()->routeIs('admin.pemeliharaan.data-pegawai*') ? 'active' : '' }}">
                                    Data Pegawai
                                </a>
                            </li>
                        </ul>
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
                                <a href="{{ route('admin.pemeliharaan.pemeliharaan') }}"
                                   class="{{ request()->routeIs('admin.pemeliharaan.pemeliharaan') ? 'active' : '' }}">
                                    Surat Permohonan 
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('admin.pemeliharaan.monitoring-aktual.index') }}"
                                   class="{{ request()->routeIs('admin.pemeliharaan.monitoring-aktual.*') ? 'active' : '' }}">
                                    Monitoring Aktual
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.pemeliharaan.invoice.index') }}"
                                   class="{{ request()->routeIs('admin.pemeliharaan.invoice.*') ? 'active' : '' }}">
                                    Monitoring Invoice
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.pemeliharaan.kartu-kendali-pembayaran') }}"
                                   class="{{ request()->routeIs('admin.pemeliharaan.kartu-kendali-pembayaran') ? 'active' : '' }}">
                                    Kartu Kendali Pembayaran
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.pemeliharaan.kartu-kendali-aktual') }}"
                                   class="{{ request()->routeIs('admin.pemeliharaan.kartu-kendali-aktual') ? 'active' : '' }}">
                                    Kartu Kendali Aktual
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
                                <a href="{{ route('admin.unit-pemadam.pengecekan') }}"
                                   class="{{ request()->routeIs('admin.unit-pemadam.pengecekan') ? 'active' : '' }}">
                                    Pengecekan
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
                                <a href="{{ route('admin.unit-rescue.pengecekan') }}"
                                   class="{{ request()->routeIs('admin.unit-rescue.pengecekan') ? 'active' : '' }}">
                                    Pengecekan
                                </a>
                            </li>
                        </ul>
                    </div>

                    {{-- === Unit Pencegahan === --}}
                    <div class="menu-group">
                        <button class="menu-title"
                                type="button" data-target="menuPencegahan">
                            <span class="menu-title-left">
                                <i data-lucide="shield" class="menu-icon"></i>
                                <span>Unit Pencegahan</span>
                            </span>
                            <i data-lucide="chevron-down" class="chevron"></i>
                        </button>
                        <ul class="submenu" id="menuPencegahan">
                            <li>
                                <a href="{{ route('admin.unit-pencegahan.pengecekan') }}"
                                   class="{{ request()->routeIs('admin.unit-pencegahan.pengecekan') ? 'active' : '' }}">
                                    Pengecekan
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
                                <a href="{{ route('admin.command-center.pengecekan') }}"
                                   class="{{ request()->routeIs('admin.command-center.pengecekan') ? 'active' : '' }}">
                                    Pengecekan
                                </a>
                            </li>
                        </ul>
                    </div>

                    {{-- === Monitoring Kejadian (APAR) - link langsung ke website APAR eksternal === --}}
                    <div class="menu-group">
                        <a href="https://apar.bandungkab.go.id/" target="_blank" rel="noopener noreferrer"
                           class="menu-title"
                           style="text-decoration:none;">
                            <span class="menu-title-left">
                                <i data-lucide="activity" class="menu-icon"></i>
                                <span>Monitoring Kejadian</span>
                            </span>
                            <i data-lucide="external-link" class="chevron" style="width:13px; height:13px; opacity:0.8;"></i>
                        </a>
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

                    <!-- Removed old logos; using unified brand logo below -->

                    {{--API Brand di Header (Tanpa Logo) --}}
                        <div class="topbar-brand">
                            <div class="topbar-brand-text flex items-center gap-2">
                                <!-- Enlarged logo aligned to the left -->
                                <img src="{{ asset('images/brama.png') }}" alt="BRAHMA" class="h-14 inline-block" style="max-height:46px; max-width:auto; background:none; margin-right:8px; align-self:flex-start;">
                                <span class="topbar-brand-subtitle">Berkala Rawat Armada, Alat, dan Sarana DAMKAR</span>
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
    <script>
        (() => {
            const normalize = value => (value || '').replace(/\s+/g, ' ').trim();
            document.querySelectorAll('input[name="search"]').forEach(input => {
                if (input.dataset.autocompleteReady === '1') return;
                input.dataset.autocompleteReady = '1';
                const wrapper = input.parentElement;
                if (!wrapper) return;
                if (getComputedStyle(wrapper).position === 'static') wrapper.style.position = 'relative';
                const menu = document.createElement('div');
                menu.className = 'search-autocomplete-menu';
                wrapper.appendChild(menu);
                let options = [], activeIndex = -1;
                const render = () => {
                    const query = normalize(input.value);
                    const values = new Set();
                    document.querySelectorAll('tbody tr td').forEach(cell => {
                        const value = normalize(cell.textContent);
                        if (query && value.length > 1 && value.toLowerCase().startsWith(query.toLowerCase())) values.add(value);
                    });
                    options = [...values].filter(value => value.toLowerCase() !== query.toLowerCase()).slice(0, 8);
                    activeIndex = -1;
                    menu.innerHTML = '';
                    options.forEach((value, index) => {
                        const option = document.createElement('button');
                        option.type = 'button'; option.className = 'search-autocomplete-option'; option.textContent = value;
                        option.addEventListener('mousedown', event => event.preventDefault());
                        option.addEventListener('click', () => { input.value = value; menu.style.display = 'none'; });
                        menu.appendChild(option);
                    });
                    menu.style.display = options.length ? 'block' : 'none';
                };
                input.addEventListener('input', render); input.addEventListener('focus', render);
                input.addEventListener('keydown', event => {
                    if (!options.length) return;
                    if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                        event.preventDefault(); activeIndex = event.key === 'ArrowDown' ? (activeIndex + 1) % options.length : (activeIndex - 1 + options.length) % options.length;
                        [...menu.children].forEach((item, index) => item.classList.toggle('is-active', index === activeIndex));
                    } else if (event.key === 'Enter' && activeIndex >= 0) { event.preventDefault(); input.value = options[activeIndex]; menu.style.display = 'none'; }
                    else if (event.key === 'Escape') menu.style.display = 'none';
                });
                document.addEventListener('click', event => { if (!wrapper.contains(event.target)) menu.style.display = 'none'; });
            });
        })();
    </script>
    <script>
        // Instant Feedback & Top Progress Bar on Link Click
        (() => {
            const progressBar = document.createElement('div');
            progressBar.id = 'nprogress-bar';
            document.body.appendChild(progressBar);

            let progressTimeout;

            function startProgress() {
                clearTimeout(progressTimeout);
                progressBar.style.opacity = '1';
                progressBar.style.width = '35%';
                progressTimeout = setTimeout(() => {
                    progressBar.style.width = '75%';
                }, 100);
            }

            document.addEventListener('click', (e) => {
                const link = e.target.closest('a');
                if (!link) return;
                
                const href = link.getAttribute('href');
                if (!href || href.startsWith('#') || href.startsWith('javascript:') || link.getAttribute('target') === '_blank' || link.hasAttribute('download')) {
                    return;
                }

                if (link.hostname === window.location.hostname) {
                    startProgress();
                }
            });

            // Form submit progress
            document.addEventListener('submit', () => {
                startProgress();
            });

            // Hover Prefetching for Instant Navigation
            const prefetchedUrls = new Set();
            document.addEventListener('mouseover', (e) => {
                const link = e.target.closest('a');
                if (!link) return;
                const href = link.getAttribute('href');
                if (!href || href.startsWith('#') || href.startsWith('javascript:') || link.getAttribute('target') === '_blank' || link.hasAttribute('download')) {
                    return;
                }
                if (link.hostname === window.location.hostname && !prefetchedUrls.has(href)) {
                    prefetchedUrls.add(href);
                    const prefetchTag = document.createElement('link');
                    prefetchTag.rel = 'prefetch';
                    prefetchTag.href = href;
                    document.head.appendChild(prefetchTag);
                }
            });
        })();
    </script>
</body>
</html>
