<?php

session_start();
include "config.php";

// Proteksi: harus login dulu
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$pesan = "";

// Cek apakah form sudah dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Ambil data dari form
    $nama_menu  = trim($_POST["nama_menu"]);
    $kategori   = $_POST["kategori"];
    $harga      = $_POST["harga"];
    $deskripsi  = trim($_POST["deskripsi"]);

    // === VALIDASI FORM ===
    if (empty($nama_menu) || empty($kategori) || empty($harga)) {
        $pesan = "error|Nama menu, kategori, dan harga wajib diisi!";

    } elseif (!is_numeric($harga) || $harga <= 0) {
        $pesan = "error|Harga harus berupa angka positif!";

    } else {
        // Simpan menu baru ke database (CREATE)
        $sql = "INSERT INTO menu (nama_menu, kategori, harga, deskripsi)
                VALUES ('$nama_menu', '$kategori', '$harga', '$deskripsi')";

        if (mysqli_query($koneksi, $sql)) {
            // Berhasil! Kembali ke halaman menu dengan pesan sukses
            header("Location: menu.php?pesan=tambah");
            exit();
        } else {
            $pesan = "error|Gagal menyimpan menu. Coba lagi.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Menu - Cafe Kiswah</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- ===== NAVBAR ===== -->
<nav class="navbar">
    <div class="nav-brand">☕ Cafe Kiswah</div>
    <ul class="nav-menu">
        <li><a href="dashboard.php">🏠 Dashboard</a></li>
        <li><a href="menu.php">🍽️ Daftar Menu</a></li>
        <li><a href="tambah_menu.php" class="aktif">➕ Tambah Menu</a></li>
        <li><a href="logout.php">🚪 Logout</a></li>
    </ul>
</nav>

<div class="konten">
    <div class="header-halaman">
        <h2>➕ Tambah Menu Baru</h2>
        <a href="menu.php" class="tombol-kecil">← Kembali</a>
    </div>

    <?php
    if (!empty($pesan)) {
        $bagian = explode("|", $pesan);
        echo "<div class='pesan {$bagian[0]}'>{$bagian[1]}</div>";
    }
    ?>

    <div class="kotak kotak-form">
        <form method="POST" action="">

            <div class="grup-form">
                <label>Nama Menu <span class="wajib">*</span></label>
                <input type="text"
                       name="nama_menu"
                       placeholder="Contoh: Nasi Goreng Spesial"
                       value="<?= $_POST['nama_menu'] ?? '' ?>"
                       required>
            </div>

            <div class="grup-form">
                <label>Kategori <span class="wajib">*</span></label>
                <select name="kategori" required>
                    <option value="">-- Pilih Kategori --</option>
                    <option value="Makanan" <?= ($_POST['kategori'] ?? '') == 'Makanan' ? 'selected' : '' ?>>🍛 Makanan</option>
                    <option value="Minuman" <?= ($_POST['kategori'] ?? '') == 'Minuman' ? 'selected' : '' ?>>🥤 Minuman</option>
                    <option value="Snack"   <?= ($_POST['kategori'] ?? '') == 'Snack'   ? 'selected' : '' ?>>🍟 Snack</option>
                </select>
            </div>

            <div class="grup-form">
                <label>Harga (Rp) <span class="wajib">*</span></label>
                <input type="number"
                       name="harga"
                       placeholder="Contoh: 15000"
                       min="1"
                       value="<?= $_POST['harga'] ?? '' ?>"
                       required>
            </div>

            <div class="grup-form">
                <label>Deskripsi <small>(opsional)</small></label>
                <textarea name="deskripsi"
                          placeholder="Tulis deskripsi singkat menu ini..."
                          rows="3"><?= $_POST['deskripsi'] ?? '' ?></textarea>
            </div>

            <div class="tombol-grup">
                <button type="submit" class="tombol-utama">💾 Simpan Menu</button>
                <a href="menu.php" class="tombol-batal">Batal</a>
            </div>

        </form>
    </div>
</div>

</body>
</html>