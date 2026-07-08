<?php
// ============================================
// FILE: edit_pesanan.php
// ============================================

session_start();
include "config.php";
include "auth.php";
require_admin();

$id = $_GET["id"] ?? 0;
$id = mysqli_real_escape_string($koneksi, $id);

// 1. Ambil data pesanan lama
$query_pesanan = "SELECT * FROM pesanan WHERE id='$id'";
$result_pesanan = mysqli_query($koneksi, $query_pesanan);

if (mysqli_num_rows($result_pesanan) == 0) {
    header("Location: pesanan.php");
    exit();
}

$pesanan = mysqli_fetch_assoc($result_pesanan);
$pesan_status = "";

// 2. Ambil list user untuk dropdown
$users_result = mysqli_query($koneksi, "SELECT id, nama, username FROM users ORDER BY nama");

// 3. Ambil rincian menu yang dipesan (read-only)
$query_detail = "SELECT pesanan_detail.*, menu.nama_menu, menu.kategori 
                 FROM pesanan_detail 
                 JOIN menu ON pesanan_detail.menu_id = menu.id 
                 WHERE pesanan_detail.pesanan_id = '$id'";
$result_detail = mysqli_query($koneksi, $query_detail);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pelanggan_id = $_POST["user_id"];
    $status       = $_POST["status"];
    $catatan      = trim($_POST["catatan"]);

    if (empty($pelanggan_id) || empty($status)) {
        $pesan_status = "error|Semua kolom wajib diisi!";
    } else {
        $sql = "UPDATE pesanan 
                SET user_id='$pelanggan_id', status='$status', catatan='$catatan' 
                WHERE id='$id'";

        if (mysqli_query($koneksi, $sql)) {
            header("Location: pesanan.php?pesan=edit");
            exit();
        } else {
            $pesan_status = "error|Gagal memperbarui pesanan. Coba lagi.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pesanan #<?= $pesanan['id'] ?> - Cafe Kiswah</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .split-view {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }
        @media (max-width: 900px) {
            .split-view {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="nav-brand">☕ Cafe Kiswah</div>
    <ul class="nav-menu">
        <li><a href="dashboard.php">🏠 Dashboard</a></li>
        <li><a href="menu.php">🍽️ Daftar Menu</a></li>
        <li><a href="pesanan.php" class="aktif">📋 Kelola Pesanan</a></li>
        <li><a href="index.php" target="_blank">🌐 Lihat Web</a></li>
        <li><a href="logout.php">🚪 Logout</a></li>
    </ul>
</nav>

<div class="konten">
    <div class="header-halaman">
        <h2>✏️ Edit Pesanan #<?= $pesanan['id'] ?></h2>
        <a href="pesanan.php" class="tombol-kecil">← Kembali</a>
    </div>

    <?php
    if (!empty($pesan_status)) {
        $bagian = explode("|", $pesan_status);
        echo "<div class='pesan {$bagian[0]}'>{$bagian[1]}</div>";
    }
    ?>

    <div class="split-view">
        
        <!-- KOLOM KIRI: FORM EDIT INFORMASI -->
        <div class="kotak">
            <h3>📝 Edit Informasi Pesanan</h3>
            <form method="POST" action="">
                
                <div class="grup-form">
                    <label>Pelanggan / User <span class="wajib">*</span></label>
                    <select name="user_id" required>
                        <option value="">-- Pilih Pelanggan --</option>
                        <?php while ($user = mysqli_fetch_assoc($users_result)) : ?>
                            <option value="<?= $user['id'] ?>" <?= ($pesanan['user_id'] == $user['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($user['nama']) ?> (<?= htmlspecialchars($user['username']) ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="grup-form">
                    <label>Status Pesanan <span class="wajib">*</span></label>
                    <select name="status" required>
                        <option value="Pending" <?= ($pesanan['status'] == 'Pending') ? 'selected' : '' ?>>⏳ Pending</option>
                        <option value="Diproses" <?= ($pesanan['status'] == 'Diproses') ? 'selected' : '' ?>>🔄 Diproses</option>
                        <option value="Selesai" <?= ($pesanan['status'] == 'Selesai') ? 'selected' : '' ?>>✅ Selesai</option>
                        <option value="Dibatalkan" <?= ($pesanan['status'] == 'Dibatalkan') ? 'selected' : '' ?>>❌ Dibatalkan</option>
                    </select>
                </div>

                <div class="grup-form">
                    <label>Catatan Tambahan <small>(opsional)</small></label>
                    <textarea name="catatan" rows="3" placeholder="Tambahkan catatan..."><?= htmlspecialchars($pesanan['catatan']) ?></textarea>
                </div>

                <div class="tombol-grup">
                    <button type="submit" class="tombol-utama">💾 Simpan Perubahan</button>
                    <a href="pesanan.php" class="tombol-batal">Batal</a>
                </div>

            </form>
        </div>

        <!-- KOLOM KANAN: RINCIAN MENU (READ-ONLY) -->
        <div class="kotak">
            <h3>🍽️ Rincian Menu Dipesan</h3>
            <table class="tabel-data" style="margin-bottom: 20px;">
                <thead>
                    <tr>
                        <th>Menu</th>
                        <th>Kategori</th>
                        <th style="text-align: center;">Qty</th>
                        <th style="text-align: right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($item = mysqli_fetch_assoc($result_detail)) :
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($item['nama_menu']) ?></strong></td>
                        <td>
                            <span class="badge badge-<?= strtolower($item['kategori']) ?>">
                                <?= $item['kategori'] ?>
                            </span>
                        </td>
                        <td style="text-align: center;"><?= $item['jumlah'] ?></td>
                        <td style="text-align: right;">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
            <div style="border-top: 2px solid #eee; padding-top: 15px; text-align: right;">
                <span style="font-size: 14px; color: #777;">Total Harga:</span><br>
                <strong style="font-size: 20px; color: #6f4e37;">Rp <?= number_format($pesanan['total_harga'], 0, ',', '.') ?></strong>
            </div>
        </div>

    </div>
</div>

</body>
</html>
