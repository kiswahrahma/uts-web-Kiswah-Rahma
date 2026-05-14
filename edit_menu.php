<?php
// ============================================
// FILE: edit_menu.php (update - ada stok)
// ============================================

session_start();
include "config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$id  = $_GET["id"] ?? 0;
$cek = mysqli_query($koneksi, "SELECT * FROM menu WHERE id='$id'");

if (mysqli_num_rows($cek) == 0) {
    header("Location: menu.php");
    exit();
}

$menu  = mysqli_fetch_assoc($cek);
$pesan = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nama_menu = trim($_POST["nama_menu"]);
    $kategori  = $_POST["kategori"];
    $harga     = $_POST["harga"];
    $deskripsi = trim($_POST["deskripsi"]);
    $stok      = $_POST["stok"];

    if (empty($nama_menu) || empty($kategori) || empty($harga) || empty($stok)) {
        $pesan = "error|Semua kolom wajib diisi!";

    } elseif (!is_numeric($harga) || $harga <= 0) {
        $pesan = "error|Harga harus berupa angka positif!";

    } else {
        $sql = "UPDATE menu
                SET nama_menu='$nama_menu', kategori='$kategori',
                    harga='$harga', deskripsi='$deskripsi', stok='$stok'
                WHERE id='$id'";

        if (mysqli_query($koneksi, $sql)) {
            header("Location: menu.php?pesan=edit");
            exit();
        } else {
            $pesan = "error|Gagal mengupdate menu. Coba lagi.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Menu - Cafe Kiswah</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-brand">☕ Cafe Kiswah</div>
    <ul class="nav-menu">
        <li><a href="dashboard.php">🏠 Dashboard</a></li>
        <li><a href="menu.php" class="aktif">🍽️ Daftar Menu</a></li>
        <li><a href="tambah_menu.php">➕ Tambah Menu</a></li>
        <li><a href="logout.php">🚪 Logout</a></li>
    </ul>
</nav>

<div class="konten">
    <div class="header-halaman">
        <h2>✏️ Edit Menu</h2>
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
                <input type="text" name="nama_menu"
                       value="<?= $menu['nama_menu'] ?>" required>
            </div>

            <div class="grup-form">
                <label>Kategori <span class="wajib">*</span></label>
                <select name="kategori" required>
                    <option value="">-- Pilih Kategori --</option>
                    <option value="Makanan" <?= $menu['kategori'] == 'Makanan' ? 'selected' : '' ?>>🍛 Makanan</option>
                    <option value="Minuman" <?= $menu['kategori'] == 'Minuman' ? 'selected' : '' ?>>🥤 Minuman</option>
                    <option value="Snack"   <?= $menu['kategori'] == 'Snack'   ? 'selected' : '' ?>>🍟 Snack</option>
                </select>
            </div>

            <div class="grup-form">
                <label>Harga (Rp) <span class="wajib">*</span></label>
                <input type="number" name="harga"
                       value="<?= $menu['harga'] ?>" min="1" required>
            </div>

            <!-- Pilihan stok - otomatis terpilih sesuai data lama -->
            <div class="grup-form">
                <label>Status Stok <span class="wajib">*</span></label>
                <select name="stok" required>
                    <option value="">-- Pilih Status --</option>
                    <option value="Tersedia" <?= $menu['stok'] == 'Tersedia' ? 'selected' : '' ?>>✅ Tersedia</option>
                    <option value="Habis"    <?= $menu['stok'] == 'Habis'    ? 'selected' : '' ?>>❌ Habis</option>
                </select>
            </div>

            <div class="grup-form">
                <label>Deskripsi <small>(opsional)</small></label>
                <textarea name="deskripsi" rows="3"><?= $menu['deskripsi'] ?></textarea>
            </div>

            <div class="tombol-grup">
                <button type="submit" class="tombol-utama">💾 Simpan Perubahan</button>
                <a href="menu.php" class="tombol-batal">Batal</a>
            </div>

        </form>
    </div>
</div>

</body>
</html>