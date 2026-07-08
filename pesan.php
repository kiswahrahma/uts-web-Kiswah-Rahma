<?php
// ============================================
// FILE: pesan.php
// Halaman pemesanan untuk PELANGGAN.
// Pelanggan hanya bisa memesan untuk dirinya sendiri.
// ============================================

session_start();
include "config.php";
include "auth.php";
require_pelanggan();

$pesan = "";
$menu_id_awal = $_GET["menu_id"] ?? 0;

// Ambil data menu yang tersedia untuk dipesan
$menu_result = mysqli_query($koneksi, "SELECT id, nama_menu, kategori, harga, foto FROM menu WHERE stok='Tersedia' ORDER BY kategori, nama_menu");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $catatan      = trim($_POST["catatan"]);
    $jumlah_order = $_POST["jumlah"] ?? []; // key = menu_id, value = qty

    $ada_pesanan = false;
    foreach ($jumlah_order as $menu_id => $qty) {
        if ($qty > 0) { $ada_pesanan = true; break; }
    }

    if (!$ada_pesanan) {
        $pesan = "error|Silakan pilih minimal 1 menu dengan jumlah lebih dari 0!";
    } else {
        $user_id = $_SESSION["user_id"];

        // 1. Insert ke tabel pesanan dulu (status selalu Pending, total sementara 0)
        $query_pesanan = "INSERT INTO pesanan (user_id, status, total_harga, catatan)
                          VALUES ('$user_id', 'Pending', 0, '" . mysqli_real_escape_string($koneksi, $catatan) . "')";

        if (mysqli_query($koneksi, $query_pesanan)) {
            $pesanan_id  = mysqli_insert_id($koneksi);
            $total_harga = 0;

            foreach ($jumlah_order as $menu_id => $qty) {
                $qty = (int)$qty;
                if ($qty > 0) {
                    $menu_id = mysqli_real_escape_string($koneksi, $menu_id);

                    // Ambil harga asli & pastikan menu masih tersedia
                    $menu_query = mysqli_query($koneksi, "SELECT harga FROM menu WHERE id='$menu_id' AND stok='Tersedia'");
                    if ($menu_data = mysqli_fetch_assoc($menu_query)) {
                        $harga_satuan = $menu_data["harga"];
                        $subtotal = $harga_satuan * $qty;
                        $total_harga += $subtotal;

                        $query_detail = "INSERT INTO pesanan_detail (pesanan_id, menu_id, jumlah, harga_satuan, subtotal)
                                         VALUES ('$pesanan_id', '$menu_id', '$qty', '$harga_satuan', '$subtotal')";
                        mysqli_query($koneksi, $query_detail);
                    }
                }
            }

            mysqli_query($koneksi, "UPDATE pesanan SET total_harga='$total_harga' WHERE id='$pesanan_id'");

            header("Location: pesanan_saya.php?pesan=tambah");
            exit();
        } else {
            $pesan = "error|Gagal membuat pesanan baru. Coba lagi.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Menu - Noir Cafe</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-brand">☕ Noir Cafe</div>
    <ul class="nav-menu">
        <li><a href="index.php">🍽️ Menu</a></li>
        <li><a href="pesanan_saya.php">📋 Pesanan Saya</a></li>
    </ul>
    <div class="nav-auth">
        <span class="nav-sapaan">Halo, <?= htmlspecialchars($_SESSION["user_nama"]) ?></span>
        <a href="logout.php" class="tombol-nav-login">🚪 Logout</a>
    </div>
</nav>

<div class="konten">
    <div class="header-halaman">
        <h2>🛒 Buat Pesanan</h2>
        <a href="index.php" class="tombol-kecil">← Kembali ke Menu</a>
    </div>

    <?php if (!empty($pesan)) : $bagian = explode("|", $pesan); ?>
        <div class="pesan <?= $bagian[0] ?>"><?= $bagian[1] ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="kotak">
            <h3>🍽️ Pilih Menu & Jumlah</h3>
            <table class="tabel-data">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Nama Menu</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th style="text-align: center; width: 150px;">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    while ($menu = mysqli_fetch_assoc($menu_result)) :
                        $qty_awal = ($menu['id'] == $menu_id_awal) ? 1 : 0;
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><strong><?= htmlspecialchars($menu["nama_menu"]) ?></strong></td>
                        <td>
                            <span class="badge badge-<?= strtolower($menu["kategori"]) ?>">
                                <?= $menu["kategori"] ?>
                            </span>
                        </td>
                        <td>Rp <?= number_format($menu["harga"], 0, ',', '.') ?></td>
                        <td style="text-align: center;">
                            <input type="number" name="jumlah[<?= $menu['id'] ?>]"
                                   class="input-jumlah" value="<?= $qty_awal ?>" min="0" max="100">
                        </td>
                    </tr>
                    <?php endwhile; ?>

                    <?php if (mysqli_num_rows($menu_result) == 0) : ?>
                    <tr>
                        <td colspan="5" style="text-align:center; color:#999; padding:30px;">
                            Belum ada menu yang tersedia untuk dipesan saat ini.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="grup-form" style="margin-top: 20px;">
                <label>Catatan Tambahan <small>(opsional)</small></label>
                <textarea name="catatan" rows="2" placeholder="Contoh: Kopi kurangi gula, es batu dipisah..."></textarea>
            </div>

            <div class="tombol-grup" style="margin-top: 20px;">
                <button type="submit" class="tombol-utama">💾 Kirim Pesanan</button>
                <a href="index.php" class="tombol-batal">Batal</a>
            </div>
        </div>
    </form>
</div>

</body>
</html>
