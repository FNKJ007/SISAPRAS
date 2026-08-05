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
    //  OTOMATIS BUKA SIDEBAR SAAT MENU DIKLIK (SAAT SIDEBAR TERTUTUP/COLLAPSED)
    //  + MULTI-OPEN ACCORDION SUBMENU WITH SESSIONSTORAGE PERSISTENCE
    //  (Reset otomatis setiap login baru / sesi baru)
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

        // 1. Pulihkan status terbuka untuk menu yang pernah dibuka secara manual di sesi ini
        if (submenu) {
            var isStoredOpen = storedSubmenus.indexOf(targetId) !== -1;

            if (isStoredOpen) {
                submenu.classList.add('open');
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
                    submenu.classList.remove('open');
                    title.classList.remove('active');
                    var index = storedSubmenus.indexOf(targetId);
                    if (index !== -1) {
                        storedSubmenus.splice(index, 1);
                    }
                } else {
                    submenu.classList.add('open');
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
