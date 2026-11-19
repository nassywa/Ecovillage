
(function () {
    const nav = document.querySelector(".site-nav");
    const toggle = document.querySelector(".nav-toggle");

    // Pastikan elemen navigasi tersedia
    if (!nav || !toggle) return;

    const menuLinks = nav.querySelectorAll("a");
    const mobileBreakpoint = window.matchMedia("(max-width: 720px)");

    // Mengatur kondisi menu
    function setMenu(open) {
        nav.setAttribute("data-open", String(open));

        toggle.setAttribute("aria-expanded", String(open));

        toggle.setAttribute(
            "aria-label",
            open ? "Tutup menu navigasi" : "Buka menu navigasi"
        );

        toggle.textContent = open ? "✕" : "☰";
    }

    // Membuka dan menutup menu
    toggle.addEventListener("click", function () {
        const isOpen = nav.getAttribute("data-open") === "true";

        setMenu(!isOpen);
    });

    // Menutup menu setelah memilih halaman
    menuLinks.forEach(function (link) {
        link.addEventListener("click", function () {
            setMenu(false);
        });
    });

    // Menutup menu ketika pengguna menekan Escape
    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            setMenu(false);
            toggle.focus();
        }
    });

    // Menutup menu ketika pengguna mengklik area di luar navigasi
    document.addEventListener("click", function (event) {
        const isOpen = nav.getAttribute("data-open") === "true";

        if (
            isOpen &&
            !nav.contains(event.target) &&
            !toggle.contains(event.target)
        ) {
            setMenu(false);
        }
    });

    // Menutup menu ketika layar kembali ke desktop
    mobileBreakpoint.addEventListener("change", function (event) {
        if (!event.matches) {
            setMenu(false);
        }
    });

    // Kondisi awal menu
    setMenu(false);
})();
