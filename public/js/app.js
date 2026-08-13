document.addEventListener('DOMContentLoaded', function () {

    var sidebar          = document.getElementById('sidebar');
    var sidebarToggle     = document.getElementById('sidebarToggle');   // tombol "Menu" di dalam sidebar
    var mobileMenuBtn     = document.getElementById('mobileMenuBtn');   // tombol hamburger di header (khusus mobile)
    var sidebarBackdrop   = document.getElementById('sidebarBackdrop'); // overlay gelap (khusus mobile)

    var MOBILE_BREAKPOINT = '(max-width: 768px)';

    function isMobile() {
        return window.matchMedia(MOBILE_BREAKPOINT).matches;
    }

    function openMobileSidebar() {
        if (!sidebar) return;
        sidebar.classList.add('mobile-open');
        if (sidebarBackdrop) sidebarBackdrop.classList.add('show');
    }

    function closeMobileSidebar() {
        if (!sidebar) return;
        sidebar.classList.remove('mobile-open');
        if (sidebarBackdrop) sidebarBackdrop.classList.remove('show');
    }

    function toggleSidebar() {
        if (isMobile()) {
            if (sidebar && sidebar.classList.contains('mobile-open')) {
                closeMobileSidebar();
            } else {
                openMobileSidebar();
            }
        } else {
            if (sidebar) sidebar.classList.toggle('collapsed');
        }
    }

    if (sidebar && sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
    }

    if (sidebar && mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', toggleSidebar);
    }

    if (sidebarBackdrop) {
        sidebarBackdrop.addEventListener('click', closeMobileSidebar);
    }

    window.addEventListener('resize', function () {
        if (!isMobile()) {
            closeMobileSidebar();
        } else {
            if (sidebar) sidebar.classList.remove('collapsed');
        }
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
    var STORAGE_KEY = 'sisapras_open_submenus';

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
            var hasActiveChild = submenu.querySelector('a.active') !== null;

            if (isStoredOpen || hasActiveChild) {
                // Buka langsung tanpa animasi saat load halaman
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

});
