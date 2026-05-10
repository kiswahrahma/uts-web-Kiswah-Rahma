<?php
// ============================================
// FILE: dashboard.php
// Fungsi: Halaman utama setelah login
// ============================================

session_start();
include "config.php";

// Proteksi halaman: jika belum login, paksa ke login
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Hitung total menu
$total_menu = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM menu"));

// Hitung menu per kategori
$total_makanan = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM menu WHERE kategori='Makanan'"));
$total_minuman = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM menu WHERE kategori='Minuman'"));
$total_snack   = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM menu WHERE kategori='Snack'"));

// Ambil 5 menu terbaru
$menu_terbaru = mysqli_query($koneksi, "SELECT * FROM menu ORDER BY created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Cafe Kiswah</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- ===== NAVBAR / NAVIGASI ===== -->
<nav class="navbar">
    <div class="nav-brand">☕ Cafe Kiswah</div>
    <ul class="nav-menu">
        <li><a href="dashboard.php" class="aktif">🏠 Dashboard</a></li>
        <li><a href="menu.php">🍽️ Daftar Menu</a></li>
        <li><a href="tambah_menu.php">➕ Tambah Menu</a></li>
        <li><a href="logout.php">🚪 Logout</a></li>
    </ul>
</nav>

<!-- ===== KONTEN UTAMA ===== -->
<div class="konten">

    <!-- Sambutan -->
    <div class="sambutan">
        <h2>Selamat datang, <?= $_SESSION["user_nama"] ?>! 👋</h2>
        <p>Kelola menu cafe kamu dari sini.</p>
    </div>

    <!-- ===== KARTU STATISTIK ===== -->
    <div class="grid-kartu">
        <div class="kartu kartu-biru">
            <div class="kartu-ikon">🍽️</div>
            <div class="kartu-info">
                <h3><?= $total_menu ?></h3>
                <p>Total Menu</p>
            </div>
        </div>

        <div class="kartu kartu-hijau">
            <div class="kartu-ikon">🍛</div>
            <div class="kartu-info">
                <h3><?= $total_makanan ?></h3>
                <p>Menu Makanan</p>
            </div>
        </div>

        <div class="kartu kartu-kuning">
            <div class="kartu-ikon">🥤</div>
            <div class="kartu-info">
                <h3><?= $total_minuman ?></h3>
                <p>Menu Minuman</p>
            </div>
        </div>

        <div class="kartu kartu-merah">
            <div class="kartu-ikon">🍟</div>
            <div class="kartu-info">
                <h3><?= $total_snack ?></h3>
                <p>Menu Snack</p>
            </div>
        </div>
    </div>

    <!-- ===== TABEL MENU TERBARU ===== -->
    <div class="kotak">
        <h3>📋 Menu Terbaru</h3>
        <table class="tabel-data">
            <thead>
                <tr>
                    <th>Nama Menu</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($baris = mysqli_fetch_assoc($menu_terbaru)) : ?>
                <tr>
                    <td><?= $baris["nama_menu"] ?></td>
                    <td><span class="badge badge-<?= strtolower($baris["kategori"]) ?>"><?= $baris["kategori"] ?></span></td>
                    <td>Rp <?= number_format($baris["harga"], 0, ',', '.') ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <a href="menu.php" class="tombol-kecil">Lihat Semua Menu →</a>
    </div>

</div><!-- akhir .konten -->

</body>
</html>