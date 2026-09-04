document.addEventListener('DOMContentLoaded', function () {

    var sidebar          = document.getElementById('sidebar');
    var sidebarToggle     = document.getElementById('sidebarToggle');   // tombol "Menu" di dalam sidebar
    var mobileMenuBtn     = document.getElementById('mobileMenuBtn');   // tombol hamburger di header (khusus mobile)
    var sidebarBackdrop   = document.getElementById('sidebarBackdrop'); // overlay gelap (khusus mobile)

    var MOBILE_BREAKPOINT = '(max-width: 768px)';
    var SIDEBAR_STATE_KEY = 'BRAMA_sidebar_state';

    function isMobile() {
        return window.matchMedia(MOBILE_BREAKPOINT).matches;
    }

    // Initialize sidebar state on page load
    function initSidebarState() {
        if (!sidebar) return;
        if (isMobile()) {
            sidebar.classList.remove('collapsed');
            sidebar.classList.remove('mobile-open');
            if (sidebarBackdrop) sidebarBackdrop.classList.remove('show');
            document.body.classList.remove('sidebar-mobile-locked');
        } else {
            var storedSidebarState = sessionStorage.getItem(SIDEBAR_STATE_KEY);
            if (storedSidebarState === 'open') {
                sidebar.classList.remove('collapsed');
            } else {
                sidebar.classList.add('collapsed');
            }
            sidebar.classList.remove('mobile-open');
            if (sidebarBackdrop) sidebarBackdrop.classList.remove('show');
            document.body.classList.remove('sidebar-mobile-locked');
        }
    }

    initSidebarState();

    function openMobileSidebar() {
        if (!sidebar) return;
        sidebar.classList.remove('collapsed');
        sidebar.classList.add('mobile-open');
        if (sidebarBackdrop) sidebarBackdrop.classList.add('show');
        document.body.classList.add('sidebar-mobile-locked');
    }

    function closeMobileSidebar() {
        if (!sidebar) return;
        sidebar.classList.remove('mobile-open');
        if (sidebarBackdrop) sidebarBackdrop.classList.remove('show');
        document.body.classList.remove('sidebar-mobile-locked');
    }

    function toggleSidebar() {
        if (isMobile()) {
            if (sidebar && sidebar.classList.contains('mobile-open')) {
                closeMobileSidebar();
            } else {
                openMobileSidebar();
            }
        } else {
            if (sidebar) {
                sidebar.classList.toggle('collapsed');
                sessionStorage.setItem(SIDEBAR_STATE_KEY, sidebar.classList.contains('collapsed') ? 'collapsed' : 'open');
            }
        }
    }

    if (sidebar && sidebarToggle) {
        sidebarToggle.addEventListener('click', function(e) {
            e.preventDefault();
            toggleSidebar();
        });
    }

    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function(e) {
            e.preventDefault();
            toggleSidebar();
        });
    }

    if (sidebarBackdrop) {
        sidebarBackdrop.addEventListener('click', function(e) {
            e.preventDefault();
            closeMobileSidebar();
        });
    }

    window.addEventListener('resize', function () {
        initSidebarState();
    });

    // =========================================================================
    //  SMOOTH SUBMENU ANIMATION WITH scrollHeight
    //  Animates to exact pixel height for buttery-smooth open/close.
    // =========================================================================

    /**
     * Membuka submenu dengan animasi halus.
     * Menghitung tinggi konten sesungguhnya (scrollHeight) lalu
     * men-transisikan height: 0 → height: Npx secara presisi.
     */
    function openSubmenu(submenu) {
        if (!submenu) return;

        // Pastikan tidak ada transisi yang sedang berjalan
        submenu.style.willChange = 'height, opacity';

        // Tambahkan class .open (mengaktifkan opacity, padding, dan stagger li)
        submenu.classList.add('open');

        // Hitung tinggi konten yang sesungguhnya
        // Set height sementara ke 'auto' agar scrollHeight bisa dihitung
        submenu.style.height = 'auto';
        var targetHeight = submenu.scrollHeight;

        // Kembalikan ke 0 dulu, lalu paksa reflow, lalu set ke target
        submenu.style.height = '0px';
        // Force reflow agar browser mengenali perubahan dari 0 → target
        submenu.offsetHeight; // eslint-disable-line no-unused-expressions

        // Animasikan ke tinggi target
        submenu.style.height = targetHeight + 'px';

        // Setelah transisi selesai, ubah height ke 'auto'
        // agar submenu bisa menyesuaikan jika isinya berubah dinamis
        var onTransitionEnd = function (e) {
            if (e.propertyName !== 'height') return;
            submenu.removeEventListener('transitionend', onTransitionEnd);
            if (submenu.classList.contains('open')) {
                submenu.style.height = 'auto';
            }
            submenu.style.willChange = '';
        };
        submenu.addEventListener('transitionend', onTransitionEnd);
    }

    /**
     * Menutup submenu dengan animasi halus.
     * Menyimpan tinggi saat ini → set ke pixel tetap → reflow → set ke 0
     * sehingga CSS transition berjalan dari Npx → 0px secara mulus.
     */
    function closeSubmenu(submenu) {
        if (!submenu) return;

        submenu.style.willChange = 'height, opacity';

        // Ambil tinggi saat ini (mungkin 'auto'), ubah ke pixel tetap
        var currentHeight = submenu.scrollHeight;
        submenu.style.height = currentHeight + 'px';

        // Force reflow agar browser mengenali dari Npx
        submenu.offsetHeight; // eslint-disable-line no-unused-expressions

        // Hapus class .open (mereset opacity, transform stagger li)
        submenu.classList.remove('open');

        // Animasikan ke 0
        submenu.style.height = '0px';

        var onTransitionEnd = function (e) {
            if (e.propertyName !== 'height') return;
            submenu.removeEventListener('transitionend', onTransitionEnd);
            submenu.style.willChange = '';
        };
        submenu.addEventListener('transitionend', onTransitionEnd);
    }

    // =========================================================================
    //  MULTI-OPEN ACCORDION SUBMENU WITH SESSIONSTORAGE PERSISTENCE
    // =========================================================================
    var STORAGE_KEY = 'API_open_submenus';

    function getOpenSubmenus() {
        try {
            var data = sessionStorage.getItem(STORAGE_KEY);
            return data ? JSON.parse(data) : [];
        } catch (e) {
            return [];
        }
    }

    function saveOpenSubmenus(list) {
        try {
            sessionStorage.setItem(STORAGE_KEY, JSON.stringify(list));
        } catch (e) {}
    }

    var storedSubmenus = getOpenSubmenus();
    var allMenuTitles  = document.querySelectorAll('.menu-title');

    allMenuTitles.forEach(function (title) {
        var targetId = title.getAttribute('data-target');
        var submenu  = targetId ? document.getElementById(targetId) : null;

        // 1. Pulihkan status terbuka dari sesi sebelumnya atau jika mengandung link aktif
        if (submenu) {
            var isStoredOpen = storedSubmenus.indexOf(targetId) !== -1;

            if (isStoredOpen) {
                // Buka langsung tanpa animasi saat load halaman jika disimpan dalam sesi
                submenu.classList.add('open');
                submenu.style.height = 'auto';
                title.classList.add('active');
            }
        }

        // 2. Event Klik pada Menu Item
        title.addEventListener('click', function () {
            // JIKA SIDEBAR DALAM KEADAAN TERTUTUP (COLLAPSED), OTOMATIS BUKA SIDEBAR!
            if (sidebar && sidebar.classList.contains('collapsed')) {
                sidebar.classList.remove('collapsed');
                if (!isMobile()) sessionStorage.setItem(SIDEBAR_STATE_KEY, 'open');
            }

            // Jika item ini memiliki submenu, lakukan toggle buka/tutup submenu
            if (submenu) {
                var isOpen = submenu.classList.contains('open');

                if (isOpen) {
                    closeSubmenu(submenu);
                    title.classList.remove('active');
                    var index = storedSubmenus.indexOf(targetId);
                    if (index !== -1) {
                        storedSubmenus.splice(index, 1);
                    }
                } else {
                    openSubmenu(submenu);
                    title.classList.add('active');
                    if (storedSubmenus.indexOf(targetId) === -1) {
                        storedSubmenus.push(targetId);
                    }
                }

                saveOpenSubmenus(storedSubmenus);
            }
        });
    });

    // =========================================================================
    //  AUTO-LOGOUT INACTIVITY TIMER (15 Menit Tanpa Aktivitas)
    // =========================================================================
    var INACTIVITY_TIME = 15 * 60 * 1000; // 15 Menit dalam milidetik
    var inactivityTimer;

    function performAutoLogout() {
        sessionStorage.removeItem(SIDEBAR_STATE_KEY);
        var logoutForm = document.querySelector('form[action*="logout"]');
        if (logoutForm) {
            logoutForm.submit();
        } else {
            window.location.href = '/login';
        }
    }

    function resetInactivityTimer() {
        clearTimeout(inactivityTimer);
        inactivityTimer = setTimeout(performAutoLogout, INACTIVITY_TIME);
    }

    var activityEvents = ['mousemove', 'keydown', 'click', 'touchstart', 'scroll'];

    activityEvents.forEach(function (eventName) {
        window.addEventListener(eventName, resetInactivityTimer, { passive: true });
    });

    resetInactivityTimer();

    // =========================================================================
    //  CLEAR SIDEBAR STATE ON LOGOUT
    // =========================================================================
    var logoutForms = document.querySelectorAll('form[action*="logout"]');
    logoutForms.forEach(function(form) {
        form.addEventListener('submit', function() {
            sessionStorage.removeItem(SIDEBAR_STATE_KEY);
        });
    });

});
