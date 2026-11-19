
(function () {
    const root = document.documentElement;
    const STORAGE_KEY = "ecovillage-theme";
    const themeButton = document.querySelector(".theme-toggle");

    // Mengambil tema yang pernah dipilih pengguna
    function getSavedTheme() {
        try {
            return localStorage.getItem(STORAGE_KEY);
        } catch (error) {
            return null;
        }
    }

    // Menentukan tema yang sedang aktif
    function getCurrentTheme() {
        const savedTheme = root.getAttribute("data-theme");

        if (savedTheme === "light" || savedTheme === "dark") {
            return savedTheme;
        }

        // Jika belum ada pilihan, ikuti tema perangkat
        return window.matchMedia("(prefers-color-scheme: dark)").matches
            ? "dark"
            : "light";
    }

    // Memperbarui tampilan tombol tema
    function updateThemeButton() {
        if (!themeButton) return;

        const isDark = getCurrentTheme() === "dark";

        themeButton.textContent = isDark ? "☀" : "☾";

        themeButton.setAttribute(
            "aria-label",
            isDark ? "Aktifkan tema terang" : "Aktifkan tema gelap"
        );

        themeButton.setAttribute(
            "aria-pressed",
            String(isDark)
        );
    }

    // Menerapkan tema pilihan pengguna
    function setTheme(theme, save = true) {
        root.setAttribute("data-theme", theme);

        if (save) {
            try {
                localStorage.setItem(STORAGE_KEY, theme);
            } catch (error) {
                console.warn("Tema tidak dapat disimpan.", error);
            }
        }

        updateThemeButton();
    }

    // Memuat tema yang pernah disimpan
    const savedTheme = getSavedTheme();

    if (savedTheme === "light" || savedTheme === "dark") {
        setTheme(savedTheme, false);
    } else {
        root.removeAttribute("data-theme");
        updateThemeButton();
    }

    // Menjalankan pergantian tema ketika tombol ditekan
    if (themeButton) {
        themeButton.addEventListener("click", function () {
            const currentTheme = getCurrentTheme();

            const nextTheme = currentTheme === "dark"
                ? "light"
                : "dark";

            setTheme(nextTheme);
        });
    }
})();
