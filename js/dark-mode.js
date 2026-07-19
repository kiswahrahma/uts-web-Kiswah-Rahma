// ============================================
// FILE: js/dark-mode.js
// Fitur dark mode: simpan preferensi di localStorage,
// terapkan ke semua halaman, dan sediakan tombol toggle.
// ============================================

(function () {
    const STORAGE_KEY = "noircafe-theme";

    // Terapkan tema secepat mungkin (sebelum konten lain dirender) agar tidak "kedip"
    function terapkanTema(tema) {
        if (tema === "dark") {
            document.documentElement.classList.add("dark-mode");
        } else {
            document.documentElement.classList.remove("dark-mode");
        }
    }

    const temaTersimpan = localStorage.getItem(STORAGE_KEY) || "light";
    terapkanTema(temaTersimpan);

    // Setelah DOM siap, pasang tombol toggle-nya
    document.addEventListener("DOMContentLoaded", function () {
        const tombol = document.createElement("button");
        tombol.id = "dark-mode-toggle";
        tombol.type = "button";
        tombol.setAttribute("aria-label", "Ganti mode gelap/terang");
        tombol.title = "Ganti mode gelap/terang";

        function updateIkon() {
            const isDark = document.documentElement.classList.contains("dark-mode");
            tombol.textContent = isDark ? "☀️" : "🌙";
        }
        updateIkon();

        tombol.addEventListener("click", function () {
            const isDark = document.documentElement.classList.toggle("dark-mode");
            localStorage.setItem(STORAGE_KEY, isDark ? "dark" : "light");
            updateIkon();
        });

        document.body.appendChild(tombol);
    });
})();
