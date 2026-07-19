// ============================================
// FILE: js/dark-mode.js
// Fitur dark mode: simpan preferensi di localStorage,
// terapkan ke semua halaman, dan sediakan tombol toggle di navbar.
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

    // Setelah DOM siap, pasang tombol toggle di navbar
    document.addEventListener("DOMContentLoaded", function () {
        const tombol = document.createElement("button");
        tombol.id = "dark-mode-toggle";
        tombol.type = "button";
        tombol.setAttribute("aria-label", "Ganti mode gelap/terang");
        tombol.title = "Ganti mode gelap/terang";
        tombol.className = "nav-dark-toggle";

        function updateIkon() {
            const isDark = document.documentElement.classList.contains("dark-mode");
            tombol.innerHTML = isDark
                ? '<span>☀️</span><span class="dark-toggle-label">Terang</span>'
                : '<span>🌙</span><span class="dark-toggle-label">Gelap</span>';
        }
        updateIkon();

        tombol.addEventListener("click", function () {
            const isDark = document.documentElement.classList.toggle("dark-mode");
            localStorage.setItem(STORAGE_KEY, isDark ? "dark" : "light");
            updateIkon();
        });

        // Sisipkan di .nav-auth (kalau ada), atau di akhir .navbar
        const navAuth = document.querySelector(".nav-auth");
        const navbar  = document.querySelector(".navbar");

        if (navAuth) {
            // Sisipkan sebelum elemen pertama nav-auth agar tombol paling kanan
            navAuth.insertBefore(tombol, navAuth.firstChild);
        } else if (navbar) {
            navbar.appendChild(tombol);
        }
    });
})();

