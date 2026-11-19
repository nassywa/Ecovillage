
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

    // Memperbarui tampilan tombol tema
    function updateThemeButton() {
        if (!themeButton) return;

        const currentTheme = root.getAttribute("data-theme");

        const isDark = currentTheme === "dark";

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
        // Jika belum ada pilihan, ikuti pengaturan perangkat
        root.removeAttribute("data-theme");
        updateThemeButton();
    }

    // Menjalankan pergantian tema ketika tombol ditekan
    if (themeButton) {
        themeButton.addEventListener("click", function () {
            const currentTheme = getComputedStyle(root)
                .getPropertyValue("color-scheme")
                .trim();

            const nextTheme =
                currentTheme === "dark" ? "light" : "dark";

            setTheme(nextTheme);
        });
    }
})();
