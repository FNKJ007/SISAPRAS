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
        sidebar.classList.add('mobile-open');
        sidebarBackdrop.classList.add('show');
    }

    function closeMobileSidebar() {
        sidebar.classList.remove('mobile-open');
        sidebarBackdrop.classList.remove('show');
    }

    function toggleSidebar() {
        if (isMobile()) {
            // Mode mobile: sidebar off-canvas (geser keluar/masuk layar) + backdrop
            if (sidebar.classList.contains('mobile-open')) {
                closeMobileSidebar();
            } else {
                openMobileSidebar();
            }
        } else {
            // Mode desktop: sidebar menyempit jadi rel tipis (perilaku lama, tidak berubah)
            sidebar.classList.toggle('collapsed');
        }
    }

    if (sidebar && sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
    }

    if (sidebar && mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', toggleSidebar);
    }

    // Klik di area gelap (backdrop) menutup sidebar mobile
    if (sidebarBackdrop) {
        sidebarBackdrop.addEventListener('click', closeMobileSidebar);
    }

    // Kalau layar di-resize dari mobile ke desktop (atau sebaliknya),
    // reset state supaya tidak "nyangkut" di kondisi yang salah.
    window.addEventListener('resize', function () {
        if (!isMobile()) {
            closeMobileSidebar();
        } else {
            sidebar.classList.remove('collapsed');
        }
    });

    // Accordion untuk tiap grup menu (Pemeliharaan, Unit Pemadam, dst)
    var menuTitles = document.querySelectorAll('.menu-title');

    menuTitles.forEach(function (title) {
        title.addEventListener('click', function () {
            var targetId = title.getAttribute('data-target');
            var submenu = document.getElementById(targetId);

            if (!submenu) {
                return;
            }

            submenu.classList.toggle('open');
            title.classList.toggle('active');
        });
    });

});
