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
    //  MULTI-OPEN ACCORDION SUBMENU WITH LOCALSTORAGE PERSISTENCE
    //  (Memungkinkan banyak menu tetap terbuka bersamaan & bertahan antar halaman)
    // =========================================================================
    var STORAGE_KEY = 'sisapras_open_submenus';

    function getOpenSubmenus() {
        try {
            var data = localStorage.getItem(STORAGE_KEY);
            return data ? JSON.parse(data) : [];
        } catch (e) {
            return [];
        }
    }

    function saveOpenSubmenus(list) {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(list));
        } catch (e) {}
    }

    var storedSubmenus = getOpenSubmenus();
    var menuTitles = document.querySelectorAll('.menu-title[data-target]');

    menuTitles.forEach(function (title) {
        var targetId = title.getAttribute('data-target');
        if (!targetId) return;
        var submenu = document.getElementById(targetId);

        if (!submenu) return;

        // Pulihkan status terbuka jika ada di localStorage atau dari kelas bawaan Blade
        var isCurrentlyOpen = submenu.classList.contains('open');
        var isStoredOpen = storedSubmenus.indexOf(targetId) !== -1;

        if (isStoredOpen || isCurrentlyOpen) {
            submenu.classList.add('open');
            title.classList.add('active');

            if (storedSubmenus.indexOf(targetId) === -1) {
                storedSubmenus.push(targetId);
                saveOpenSubmenus(storedSubmenus);
            }
        }

        // Toggle independen (Multi-Open): muka/tutup menu ini tanpa menutup menu lain
        title.addEventListener('click', function () {
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
