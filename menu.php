<?php
// ============================================
// FILE: menu.php
// Fungsi: Menampilkan semua menu cafe (READ)
// ============================================

session_start();
include "config.php";

// Proteksi: harus login dulu
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Cek apakah ada filter kategori dari URL (?kategori=Makanan)
$filter = "";
$kategori_filter = "";

if (!empty($_GET["kategori"])) {
    $kategori_filter = $_GET["kategori"];
    $filter = "WHERE kategori='$kategori_filter'";
}

// Ambil semua menu dari database (dengan filter jika ada)
$query = "SELECT * FROM menu $filter ORDER BY kategori, nama_menu";
$hasil = mysqli_query($koneksi, $query);

// Cek apakah ada pesan sukses dari tambah/edit/hapus
$pesan = $_GET["pesan"] ?? "";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Menu - Noir Cafe</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- ===== NAVBAR ===== -->
<nav class="navbar">
    <div class="nav-brand">☕ Noir Cafe</div>
    <ul class="nav-menu">
        <li><a href="dashboard.php">🏠 Dashboard</a></li>
        <li><a href="menu.php" class="aktif">🍽️ Daftar Menu</a></li>
        <li><a href="tambah_menu.php">➕ Tambah Menu</a></li>
        <li><a href="logout.php">🚪 Logout</a></li>
    </ul>
</nav>

<div class="konten">

    <!-- Judul halaman -->
    <div class="header-halaman">
        <h2>🍽️ Daftar Menu Cafe</h2>
        <a href="tambah_menu.php" class="tombol-utama">+ Tambah Menu</a>
    </div>

    <!-- Pesan sukses/hapus -->
    <?php if ($pesan == "tambah") : ?>
        <div class="pesan sukses">✅ Menu baru berhasil ditambahkan!</div>
    <?php elseif ($pesan == "edit") : ?>
        <div class="pesan sukses">✅ Menu berhasil diperbarui!</div>
    <?php elseif ($pesan == "hapus") : ?>
        <div class="pesan error">🗑️ Menu berhasil dihapus!</div>
    <?php endif; ?>

    <!-- Filter kategori -->
    <div class="filter-kategori">
        <a href="menu.php" class="tombol-filter <?= empty($kategori_filter) ? 'aktif' : '' ?>">Semua</a>
        <a href="menu.php?kategori=Makanan" class="tombol-filter <?= $kategori_filter == 'Makanan' ? 'aktif' : '' ?>">🍛 Makanan</a>
        <a href="menu.php?kategori=Minuman" class="tombol-filter <?= $kategori_filter == 'Minuman' ? 'aktif' : '' ?>">🥤 Minuman</a>
        <a href="menu.php?kategori=Snack" class="tombol-filter <?= $kategori_filter == 'Snack' ? 'aktif' : '' ?>">🍟 Snack</a>
    </div>

    <!-- Tabel data menu -->
    <div class="kotak">
        <table class="tabel-data">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Menu</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Deskripsi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1; // Nomor urut baris
                while ($baris = mysqli_fetch_assoc($hasil)) :
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><strong><?= $baris["nama_menu"] ?></strong></td>
                    <td><span class="badge badge-<?= strtolower($baris["kategori"]) ?>"><?= $baris["kategori"] ?></span></td>
                    <td>Rp <?= number_format($baris["harga"], 0, ',', '.') ?></td>
                    <td><?= $baris["deskripsi"] ?></td>
                    <td class="kolom-aksi">
                        <!-- Tombol Edit -->
                        <a href="edit_menu.php?id=<?= $baris["id"] ?>" class="tombol-edit">✏️ Edit</a>

                        <!-- Tombol Hapus dengan konfirmasi -->
                        <a href="hapus_menu.php?id=<?= $baris["id"] ?>"
                           class="tombol-hapus"
                           onclick="return confirm('Yakin ingin menghapus menu ini?')">
                           🗑️ Hapus
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>

                <?php if (mysqli_num_rows($hasil) == 0) : ?>
                <tr>
                    <td colspan="6" style="text-align:center; color:#999; padding:30px;">
                        Belum ada menu. <a href="tambah_menu.php">Tambah sekarang!</a>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>