<?php
// ============================================
// FILE: menu.php (versi update - ada fitur stok)
// ============================================

session_start();
include "config.php"; // ganti koneksi.php jika perlu

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// ============================================
// PROSES UBAH STOK (kalau tombol stok diklik)
// ============================================
if (isset($_GET["ubah_stok"])) {
    $id        = $_GET["ubah_stok"];
    $stok_baru = $_GET["stok"];

    // Validasi nilai stok
    if (in_array($stok_baru, ["Tersedia", "Habis"])) {
        mysqli_query($koneksi, "UPDATE menu SET stok='$stok_baru' WHERE id='$id'");
    }
    header("Location: menu.php?pesan=stok");
    exit();
}

// Filter kategori
$filter          = "";
$kategori_filter = "";

if (!empty($_GET["kategori"])) {
    $kategori_filter = $_GET["kategori"];
    $filter          = "WHERE kategori='$kategori_filter'";
}

// Ambil semua menu
$hasil = mysqli_query($koneksi, "SELECT * FROM menu $filter ORDER BY kategori, nama_menu");

$pesan = $_GET["pesan"] ?? "";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Menu - Cafe Kiswah</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .badge-tersedia {
            background: #e8f5e9; color: #2e7d32;
            padding: 4px 12px; border-radius: 20px;
            font-size: 12px; font-weight: 600;
        }
        .badge-habis {
            background: #fdecea; color: #c62828;
            padding: 4px 12px; border-radius: 20px;
            font-size: 12px; font-weight: 600;
        }
        .tombol-stok-tersedia {
            background: #e8f5e9; color: #2e7d32;
            border: 1px solid #a5d6a7; padding: 4px 10px;
            border-radius: 6px; font-size: 11px; font-weight: 600;
            cursor: pointer; text-decoration: none; display: inline-block;
        }
        .tombol-stok-habis {
            background: #fdecea; color: #c62828;
            border: 1px solid #ef9a9a; padding: 4px 10px;
            border-radius: 6px; font-size: 11px; font-weight: 600;
            cursor: pointer; text-decoration: none; display: inline-block;
        }
        /* Baris menu habis jadi agak redup */
        .baris-habis td { opacity: 0.5; }
        .baris-habis td:last-child,
        .baris-habis td:nth-last-child(2) { opacity: 1; }
    </style>
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
        <h2>🍽️ Daftar Menu Cafe</h2>
        <a href="tambah_menu.php" class="tombol-utama">+ Tambah Menu</a>
    </div>

    <?php if ($pesan == "tambah") : ?>
        <div class="pesan sukses">✅ Menu baru berhasil ditambahkan!</div>
    <?php elseif ($pesan == "edit") : ?>
        <div class="pesan sukses">✅ Menu berhasil diperbarui!</div>
    <?php elseif ($pesan == "hapus") : ?>
        <div class="pesan error">🗑️ Menu berhasil dihapus!</div>
    <?php elseif ($pesan == "stok") : ?>
        <div class="pesan sukses">🔄 Status stok berhasil diubah!</div>
    <?php endif; ?>

    <div class="filter-kategori">
        <a href="menu.php" class="tombol-filter <?= empty($kategori_filter) ? 'aktif' : '' ?>">Semua</a>
        <a href="menu.php?kategori=Makanan" class="tombol-filter <?= $kategori_filter == 'Makanan' ? 'aktif' : '' ?>">🍛 Makanan</a>
        <a href="menu.php?kategori=Minuman" class="tombol-filter <?= $kategori_filter == 'Minuman' ? 'aktif' : '' ?>">🥤 Minuman</a>
        <a href="menu.php?kategori=Snack" class="tombol-filter <?= $kategori_filter == 'Snack' ? 'aktif' : '' ?>">🍟 Snack</a>
    </div>

    <div class="kotak">
        <table class="tabel-data">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Menu</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Status Stok</th>
                    <th>Ubah Stok</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                while ($baris = mysqli_fetch_assoc($hasil)) :
                    $class_baris = ($baris["stok"] == "Habis") ? "baris-habis" : "";
                ?>
                <tr class="<?= $class_baris ?>">
                    <td><?= $no++ ?></td>
                    <td><strong><?= $baris["nama_menu"] ?></strong></td>
                    <td>
                        <span class="badge badge-<?= strtolower($baris["kategori"]) ?>">
                            <?= $baris["kategori"] ?>
                        </span>
                    </td>
                    <td>Rp <?= number_format($baris["harga"], 0, ',', '.') ?></td>

                    <!-- Status stok -->
                    <td>
                        <?php if ($baris["stok"] == "Tersedia") : ?>
                            <span class="badge-tersedia">✅ Tersedia</span>
                        <?php else : ?>
                            <span class="badge-habis">❌ Habis</span>
                        <?php endif; ?>
                    </td>

                    <!-- Tombol ubah stok -->
                    <td>
                        <?php if ($baris["stok"] == "Tersedia") : ?>
                            <a href="menu.php?ubah_stok=<?= $baris["id"] ?>&stok=Habis"
                               class="tombol-stok-habis"
                               onclick="return confirm('Tandai menu ini sebagai HABIS?')">
                               Tandai Habis
                            </a>
                        <?php else : ?>
                            <a href="menu.php?ubah_stok=<?= $baris["id"] ?>&stok=Tersedia"
                               class="tombol-stok-tersedia"
                               onclick="return confirm('Tandai menu ini sebagai TERSEDIA?')">
                               Tandai Tersedia
                            </a>
                        <?php endif; ?>
                    </td>

                    <!-- Aksi edit & hapus -->
                    <td class="kolom-aksi">
                        <a href="edit_menu.php?id=<?= $baris["id"] ?>" class="tombol-edit">✏️ Edit</a>
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
                    <td colspan="7" style="text-align:center; color:#999; padding:30px;">
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