document.addEventListener('DOMContentLoaded', function () {

    // Toggle buka/tutup sidebar (klik "Menu" di dalam sidebar, atau tombol
    // kecil di luar sidebar yang muncul saat sidebar tertutup)
    var sidebarToggle = document.getElementById('sidebarToggle');
    var sidebarReopen = document.getElementById('sidebarReopen');
    var sidebar = document.getElementById('sidebar');

    function closeSidebar() {
        sidebar.classList.add('collapsed');
        sidebarReopen.classList.add('visible');
    }

    function openSidebar() {
        sidebar.classList.remove('collapsed');
        sidebarReopen.classList.remove('visible');
    }

    if (sidebarToggle && sidebar && sidebarReopen) {
        sidebarToggle.addEventListener('click', function () {
            closeSidebar();
        });

        sidebarReopen.addEventListener('click', function () {
            openSidebar();
        });
    }

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
