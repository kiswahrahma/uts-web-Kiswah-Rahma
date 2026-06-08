<?php
// ============================================
// FILE: pesanan.php
// ============================================

session_start();
include "config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Filter Status & Pencarian
$status_filter = $_GET["status"] ?? "";
$cari = $_GET["cari"] ?? "";

$conditions = [];
if (!empty($status_filter)) {
    $status_escaped = mysqli_real_escape_string($koneksi, $status_filter);
    $conditions[] = "pesanan.status='$status_escaped'";
}
if (!empty($cari)) {
    $cari_escaped = mysqli_real_escape_string($koneksi, $cari);
    $conditions[] = "(users.nama LIKE '%$cari_escaped%' OR pesanan.id = '$cari_escaped')";
}

$filter = "";
if (count($conditions) > 0) {
    $filter = "WHERE " . implode(" AND ", $conditions);
}

// Ambil semua pesanan dan join dengan tabel users untuk mendapatkan nama pemesan
$query = "SELECT pesanan.*, users.nama AS nama_user 
          FROM pesanan 
          JOIN users ON pesanan.user_id = users.id 
          $filter 
          ORDER BY pesanan.tanggal DESC";
$hasil = mysqli_query($koneksi, $query);

$pesan = $_GET["pesan"] ?? "";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - Cafe Kiswah</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .badge-pending {
            background: #fff3e0; color: #e65100;
            padding: 4px 12px; border-radius: 20px;
            font-size: 12px; font-weight: 600;
            display: inline-block;
        }
        .badge-diproses {
            background: #e3f2fd; color: #1565c0;
            padding: 4px 12px; border-radius: 20px;
            font-size: 12px; font-weight: 600;
            display: inline-block;
        }
        .badge-selesai {
            background: #e8f5e9; color: #2e7d32;
            padding: 4px 12px; border-radius: 20px;
            font-size: 12px; font-weight: 600;
            display: inline-block;
        }
        .badge-dibatalkan {
            background: #fdecea; color: #c62828;
            padding: 4px 12px; border-radius: 20px;
            font-size: 12px; font-weight: 600;
            display: inline-block;
        }
        .tombol-detail {
            background: #546e7a;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            transition: background 0.3s;
            display: inline-block;
        }
        .tombol-detail:hover {
            background: #37474f;
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="nav-brand">☕ Cafe Kiswah</div>
    <ul class="nav-menu">
        <li><a href="dashboard.php">🏠 Dashboard</a></li>
        <li><a href="menu.php">🍽️ Daftar Menu</a></li>
        <li><a href="tambah_menu.php">➕ Tambah Menu</a></li>
        <li><a href="pesanan.php" class="aktif">📋 Kelola Pesanan</a></li>
        <li><a href="logout.php">🚪 Logout</a></li>
    </ul>
</nav>

<div class="konten">

    <div class="header-halaman">
        <h2>📋 Daftar Pesanan Cafe</h2>
        <a href="tambah_pesanan.php" class="tombol-utama">+ Tambah Pesanan</a>
    </div>

    <?php if ($pesan == "tambah") : ?>
        <div class="pesan sukses">✅ Pesanan baru berhasil ditambahkan!</div>
    <?php elseif ($pesan == "edit") : ?>
        <div class="pesan sukses">✅ Pesanan berhasil diperbarui!</div>
    <?php elseif ($pesan == "hapus") : ?>
        <div class="pesan error">🗑️ Pesanan berhasil dihapus!</div>
    <?php endif; ?>

    <!-- Form Pencarian -->
    <form method="GET" action="" class="form-cari-wadah" style="margin-bottom: 20px; display: flex; gap: 10px; align-items: center;">
        <?php if (!empty($status_filter)) : ?>
            <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>">
        <?php endif; ?>
        <input type="text" name="cari" placeholder="🔍 Cari nama pelanggan atau ID pesanan..." value="<?= htmlspecialchars($cari) ?>" 
               style="flex: 1; padding: 10px 15px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;">
        <button type="submit" class="tombol-utama" style="width: auto; padding: 10px 20px; margin: 0;">Cari</button>
        <?php if (!empty($cari)) : ?>
            <a href="pesanan.php?status=<?= urlencode($status_filter) ?>" class="tombol-batal" style="padding: 10px 20px; text-decoration: none; line-height: 1.5;">Reset</a>
        <?php endif; ?>
    </form>

    <div class="filter-kategori">
        <a href="pesanan.php?cari=<?= urlencode($cari) ?>" class="tombol-filter <?= empty($status_filter) ? 'aktif' : '' ?>">Semua</a>
        <a href="pesanan.php?status=Pending&cari=<?= urlencode($cari) ?>" class="tombol-filter <?= $status_filter == 'Pending' ? 'aktif' : '' ?>">⏳ Pending</a>
        <a href="pesanan.php?status=Diproses&cari=<?= urlencode($cari) ?>" class="tombol-filter <?= $status_filter == 'Diproses' ? 'aktif' : '' ?>">🔄 Diproses</a>
        <a href="pesanan.php?status=Selesai&cari=<?= urlencode($cari) ?>" class="tombol-filter <?= $status_filter == 'Selesai' ? 'aktif' : '' ?>">✅ Selesai</a>
        <a href="pesanan.php?status=Dibatalkan&cari=<?= urlencode($cari) ?>" class="tombol-filter <?= $status_filter == 'Dibatalkan' ? 'aktif' : '' ?>">❌ Dibatalkan</a>
    </div>

    <div class="kotak">
        <table class="tabel-data">
            <thead>
                <tr>
                    <th>No</th>
                    <th>ID Pesanan</th>
                    <th>Tanggal & Waktu</th>
                    <th>Pelanggan</th>
                    <th>Status</th>
                    <th>Total Harga</th>
                    <th>Catatan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                while ($baris = mysqli_fetch_assoc($hasil)) :
                    $status_class = "badge-" . strtolower($baris["status"]);
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><strong>#<?= $baris["id"] ?></strong></td>
                    <td><?= date('d-m-Y H:i', strtotime($baris["tanggal"])) ?></td>
                    <td><?= htmlspecialchars($baris["nama_user"]) ?></td>
                    <td>
                        <span class="<?= $status_class ?>">
                            <?php
                            if ($baris["status"] == 'Pending') echo "⏳ Pending";
                            elseif ($baris["status"] == 'Diproses') echo "🔄 Diproses";
                            elseif ($baris["status"] == 'Selesai') echo "✅ Selesai";
                            elseif ($baris["status"] == 'Dibatalkan') echo "❌ Dibatalkan";
                            else echo $baris["status"];
                            ?>
                        </span>
                    </td>
                    <td><strong>Rp <?= number_format($baris["total_harga"], 0, ',', '.') ?></strong></td>
                    <td><?= htmlspecialchars($baris["catatan"] ?? '-') ?></td>
                    <td class="kolom-aksi">
                        <a href="detail_pesanan.php?id=<?= $baris["id"] ?>" class="tombol-detail">👁️ Detail</a>
                        <a href="edit_pesanan.php?id=<?= $baris["id"] ?>" class="tombol-edit">✏️ Edit</a>
                        <a href="hapus_pesanan.php?id=<?= $baris["id"] ?>" 
                           class="tombol-hapus"
                           onclick="return confirm('Yakin ingin menghapus pesanan ini beserta seluruh detailnya?')">
                           🗑️ Hapus
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>

                <?php if (mysqli_num_rows($hasil) == 0) : ?>
                <tr>
                    <td colspan="8" style="text-align:center; color:#999; padding:30px;">
                        Belum ada data pesanan. <a href="tambah_pesanan.php">Tambah pesanan sekarang!</a>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>
</body>
</html>
