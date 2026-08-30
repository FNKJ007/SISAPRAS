<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') — BRAHMA | Berkala Rawat Armada, Alat, dan Sarana DAMKAR</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Google Font & Fonts --}}
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

        .content-area, .dashboard-container, main {
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

    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ file_exists(public_path('css/app.css')) ? filemtime(public_path('css/app.css')) : '1' }}">
    @stack('styles')
    <style>
        .search-autocomplete-menu {
            position: absolute;
            z-index: 100000;
            left: 0;
            right: 0;
            top: calc(100% + 4px);
            display: none;
            max-height: 220px;
            overflow-y: auto;
            background: #fff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, .16);
        }
        .search-autocomplete-option {
            display: block;
            width: 100%;
            padding: 8px 10px;
            border: 0;
            border-bottom: 1px solid #f1f5f9;
            background: #fff;
            color: #334155;
            text-align: left;
            font-size: 12px;
            cursor: pointer;
        }
        .search-autocomplete-option:hover,
        .search-autocomplete-option.is-active { background: #eff6ff; color: #1d4ed8; }
    </style>
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
                           class="menu-title {{ request()->routeIs('home', 'home.index') ? 'active' : '' }}"
                           style="text-decoration:none;">
                            <span class="menu-title-left">
                                <i data-lucide="home" class="menu-icon"></i>
                                <span>Home</span>
                            </span>
                        </a>
                    </div>

                    {{-- === Pemeliharaan === --}}
                    <div class="menu-group">
                        <button class="menu-title" type="button" data-target="menuPemeliharaan">
                            <span class="menu-title-left">
                                <i data-lucide="wrench" class="menu-icon"></i>
                                <span>Pemeliharaan</span>
                            </span>
                            <i data-lucide="chevron-down" class="chevron"></i>
                        </button>
                        <ul class="submenu" id="menuPemeliharaan">
                            <li>
                                <a href="{{ route('pemeliharaan.pengajuan') }}"
                                   class="{{ request()->routeIs('pemeliharaan.pengajuan') ? 'active' : '' }}">
                                    Pengajuan
                                </a>
                            </li>
                        </ul>
                    </div>

                    @php
                        $userBidang = strtolower(auth()->user()->bidang ?? '');
                        $isSpi = str_contains($userBidang, 'informasi') || str_contains($userBidang, 'spi');
                        $isAdminSimulasi = auth()->user()->isAdmin() && session('admin_viewing_as_user');
                        $noBidang = empty(trim(auth()->user()->bidang ?? ''));
                        $showAll = $isSpi || $isAdminSimulasi || $noBidang;
                    @endphp

                    {{-- === Unit Pemadam === --}}
                    @if($showAll || str_contains($userBidang, 'pemadam'))
                    <div class="menu-group">
                        <button class="menu-title" type="button" data-target="menuPemadam">
                            <span class="menu-title-left">
                                <i data-lucide="flame" class="menu-icon"></i>
                                <span>Pemadam</span>
                            </span>
                            <i data-lucide="chevron-down" class="chevron"></i>
                        </button>
                        <ul class="submenu" id="menuPemadam">
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
                            <li>
                                <a href="{{ route('unit-pemadam.riwayat') }}"
                                   class="{{ request()->routeIs('unit-pemadam.riwayat') ? 'active' : '' }}">
                                    Riwayat Pengecekan
                                </a>
                            </li>
                        </ul>
                    </div>
                    @endif

                    {{-- === Unit Rescue === --}}
                    @if($showAll || str_contains($userBidang, 'rescue'))
                    <div class="menu-group">
                        <button class="menu-title" type="button" data-target="menuRescue">
                            <span class="menu-title-left">
                                <i data-lucide="life-buoy" class="menu-icon"></i>
                                <span>Rescue</span>
                            </span>
                            <i data-lucide="chevron-down" class="chevron"></i>
                        </button>
                        <ul class="submenu" id="menuRescue">
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
                            <li>
                                <a href="{{ route('unit-rescue.riwayat') }}"
                                   class="{{ request()->routeIs('unit-rescue.riwayat') ? 'active' : '' }}">
                                    Riwayat Pengecekan
                                </a>
                            </li>
                        </ul>
                    </div>
                    @endif

                    {{-- === Bidang Pencegahan === --}}
                    @if($showAll || str_contains($userBidang, 'pencegahan'))
                    <div class="menu-group">
                        <button class="menu-title" type="button" data-target="menuPencegahan">
                            <span class="menu-title-left">
                                <i data-lucide="shield" class="menu-icon"></i>
                                <span>Pencegahan</span>
                            </span>
                            <i data-lucide="chevron-down" class="chevron"></i>
                        </button>
                        <ul class="submenu" id="menuPencegahan">
                            <li>
                                <a href="{{ route('unit-pencegahan.cek-harian-unit') }}"
                                   class="{{ request()->routeIs('unit-pencegahan.cek-harian-unit') ? 'active' : '' }}">
                                    Cek Harian Unit
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('alat-pencegahan.cek-harian-alat') }}"
                                   class="{{ request()->routeIs('alat-pencegahan.cek-harian-alat') ? 'active' : '' }}">
                                    Cek Harian Alat
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('unit-pencegahan.riwayat') }}"
                                   class="{{ request()->routeIs('unit-pencegahan.riwayat') ? 'active' : '' }}">
                                    Riwayat Pengecekan
                                </a>
                            </li>
                        </ul>
                    </div>
                    @endif

                    {{-- === Command Center === --}}
                    @if($showAll || str_contains($userBidang, 'command center'))
                    <div class="menu-group">
                        <button class="menu-title" type="button" data-target="menuCommand">
                            <span class="menu-title-left">
                                <i data-lucide="radio-tower" class="menu-icon"></i>
                                <span>Command Center</span>
                            </span>
                            <i data-lucide="chevron-down" class="chevron"></i>
                        </button>
                        <ul class="submenu" id="menuCommand">
                            <li>
                                <a href="{{ route('alat-cc.cek-alat-cc') }}"
                                   class="{{ request()->routeIs('alat-cc.cek-alat-cc') ? 'active' : '' }}">
                                    Cek Harian Alat
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('alat-cc.riwayat') }}"
                                   class="{{ request()->routeIs('alat-cc.riwayat') ? 'active' : '' }}">
                                    Riwayat Pengecekan
                                </a>
                            </li>
                        </ul>
                    </div>
                    @endif

                    {{-- === Monitoring Kejadian (Tampil di semua bidang, posisi paling bawah menu) === --}}
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
                </div>

                {{-- === Tombol Bawah Sidebar (Hanya 1 Tombol: Kembali ke Admin ATAU Logout) === --}}
                <div class="menu-group" style="margin-top: 15px; padding-top: 10px; border-top: 1px solid var(--sidebar-border);">
                    @if(auth()->check() && auth()->user()->isAdmin() && session('admin_viewing_as_user'))
                        <form action="{{ route('admin.switch-back-to-admin') }}" method="POST">
                            @csrf
                            <button type="submit" class="menu-title" style="width: 100%; border: none; background: none; color: #fff; cursor: pointer; text-decoration: none;" title="Kembali ke Panel Admin">
                                <span class="menu-title-left">
                                    <i data-lucide="arrow-left" class="menu-icon"></i>
                                    <span>Kembali ke Admin</span>
                                </span>
                            </button>
                        </form>
                    @else
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="menu-title" style="width: 100%; border: none; background: none; color: #fff; cursor: pointer; text-decoration: none;">
                                <span class="menu-title-left">
                                    <i data-lucide="log-out" class="menu-icon"></i>
                                    <span>Logout</span>
                                </span>
                            </button>
                        </form>
                    @endif
                </div>

            </nav>
        </aside>

        {{-- Backdrop gelap --}}
        <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

        {{-- ===================== MAIN AREA ===================== --}}
        <div class="main-area">

            @if(session('admin_viewing_as_user') && auth()->check() && auth()->user()->isAdmin())
                <div style="background:#1B2A6B; color:#FFFFFF; padding:8px 20px; font-size:12.5px; font-weight:600; display:flex; align-items:center; justify-content:space-between; box-shadow:0 2px 8px rgba(0,0,0,0.15); z-index:9999; flex-shrink:0;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <i data-lucide="eye" style="width:16px; height:16px; color:#F59E0B;"></i>
                        <span>Anda sedang dalam Mode Simulasi Tampilan User (Administrator)</span>
                    </div>
                    <form method="POST" action="{{ route('admin.switch-back-to-admin') }}" style="margin:0;">
                        @csrf
                        <button type="submit" style="background:#DC2626; color:#FFFFFF; border:none; padding:5px 14px; border-radius:6px; font-size:11.5px; font-weight:700; cursor:pointer;">
                            Kembali ke Panel Admin
                        </button>
                    </form>
                </div>
            @endif

            <header class="topbar" style="display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <button class="mobile-menu-btn" id="mobileMenuBtn" type="button" aria-label="Buka menu">
                        <i data-lucide="menu" class="icon-bars"></i>
                    </button>

                    {{-- API Brand di Header (Dengan Logo) --}}
                        <div class="topbar-brand">
                            <div class="topbar-brand-text flex items-center gap-2">
                                <!-- Enlarged logo aligned to the left -->
                                <img src="{{ asset('images/brama.png') }}" alt="BRAHMA" class="inline-block" style="height:46px; max-width:none; width:auto; background:none; margin-right:8px; align-self:flex-start;">
                                <span class="topbar-brand-subtitle">Berkala Rawat Armada, Alat, dan Sarana DAMKAR</span>
                            </div>
                        </div>
                </div>

                {{-- Area User Info di Topbar --}}
                <div class="user-card-topbar">
                    <i data-lucide="user"></i>
                    <span>{{ auth()->user()->name ?? 'User' }}</span>
                </div>
            </header>

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
    <script>
        (() => {
            const normalize = value => (value || '').replace(/\s+/g, ' ').trim();

            const setupSearchAutocomplete = input => {
                if (input.dataset.autocompleteReady === '1') return;
                input.dataset.autocompleteReady = '1';
                const wrapper = input.parentElement;
                if (!wrapper) return;
                if (getComputedStyle(wrapper).position === 'static') wrapper.style.position = 'relative';

                const menu = document.createElement('div');
                menu.className = 'search-autocomplete-menu';
                wrapper.appendChild(menu);
                let options = [];
                let activeIndex = -1;

                const collectOptions = query => {
                    const values = new Set();
                    document.querySelectorAll('tbody tr').forEach(row => {
                        row.querySelectorAll('td').forEach(cell => {
                            const value = normalize(cell.textContent);
                            if (value.length > 1 && value.toLowerCase().startsWith(query.toLowerCase())) values.add(value);
                        });
                    });
                    return [...values].filter(value => value.toLowerCase() !== query.toLowerCase()).slice(0, 8);
                };

                const render = () => {
                    const query = normalize(input.value);
                    options = query.length > 0 ? collectOptions(query) : [];
                    activeIndex = -1;
                    menu.innerHTML = '';
                    options.forEach((value, index) => {
                        const option = document.createElement('button');
                        option.type = 'button';
                        option.className = 'search-autocomplete-option';
                        option.textContent = value;
                        option.addEventListener('mousedown', event => event.preventDefault());
                        option.addEventListener('click', () => {
                            input.value = value;
                            menu.style.display = 'none';
                            input.dispatchEvent(new Event('change', { bubbles: true }));
                        });
                        menu.appendChild(option);
                    });
                    menu.style.display = options.length ? 'block' : 'none';
                };

                input.addEventListener('input', render);
                input.addEventListener('focus', render);
                input.addEventListener('keydown', event => {
                    if (!options.length) return;
                    if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                        event.preventDefault();
                        activeIndex = event.key === 'ArrowDown'
                            ? (activeIndex + 1) % options.length
                            : (activeIndex - 1 + options.length) % options.length;
                        [...menu.children].forEach((item, index) => item.classList.toggle('is-active', index === activeIndex));
                    } else if (event.key === 'Enter' && activeIndex >= 0) {
                        event.preventDefault();
                        input.value = options[activeIndex];
                        menu.style.display = 'none';
                    } else if (event.key === 'Escape') {
                        menu.style.display = 'none';
                    }
                });
                document.addEventListener('click', event => {
                    if (!wrapper.contains(event.target)) menu.style.display = 'none';
                });
            };

            document.querySelectorAll('input[name="search"]').forEach(setupSearchAutocomplete);
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