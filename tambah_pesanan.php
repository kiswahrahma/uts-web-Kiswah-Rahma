<?php
// ============================================
// FILE: tambah_pesanan.php
// ============================================

session_start();
include "config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$pesan = "";

// Ambil data user untuk dropdown pelanggan
$users_result = mysqli_query($koneksi, "SELECT id, nama, username FROM users ORDER BY nama");

// Ambil data menu yang tersedia untuk dipesan
$menu_result = mysqli_query($koneksi, "SELECT id, nama_menu, kategori, harga FROM menu WHERE stok='Tersedia' ORDER BY kategori, nama_menu");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pelanggan_id = $_POST["user_id"];
    $status       = $_POST["status"];
    $catatan      = trim($_POST["catatan"]);
    $jumlah_order = $_POST["jumlah"] ?? []; // Array dengan key menu_id dan value quantity

    // Validasi: Cek apakah ada menu yang dipesan (jumlah > 0)
    $ada_pesanan = false;
    foreach ($jumlah_order as $menu_id => $qty) {
        if ($qty > 0) {
            $ada_pesanan = true;
            break;
        }
    }

    if (empty($pelanggan_id) || empty($status)) {
        $pesan = "error|Semua kolom wajib diisi!";
    } elseif (!$ada_pesanan) {
        $pesan = "error|Silakan pilih minimal 1 menu dengan jumlah lebih dari 0!";
    } else {
        // 1. Insert ke tabel pesanan terlebih dahulu (dengan total_harga = 0 dulu)
        $query_pesanan = "INSERT INTO pesanan (user_id, status, total_harga, catatan) 
                          VALUES ('$pelanggan_id', '$status', 0, '$catatan')";
        
        if (mysqli_query($koneksi, $query_pesanan)) {
            $pesanan_id = mysqli_insert_id($koneksi);
            $total_harga = 0;

            // 2. Loop menu items and insert into pesanan_detail
            foreach ($jumlah_order as $menu_id => $qty) {
                $qty = (int)$qty;
                if ($qty > 0) {
                    $menu_id = mysqli_real_escape_string($koneksi, $menu_id);
                    
                    // Ambil harga asli dari database agar aman
                    $menu_query = mysqli_query($koneksi, "SELECT harga FROM menu WHERE id='$menu_id'");
                    if ($menu_data = mysqli_fetch_assoc($menu_query)) {
                        $harga_satuan = $menu_data["harga"];
                        $subtotal = $harga_satuan * $qty;
                        $total_harga += $subtotal;

                        // Insert ke pesanan_detail
                        $query_detail = "INSERT INTO pesanan_detail (pesanan_id, menu_id, jumlah, harga_satuan, subtotal) 
                                         VALUES ('$pesanan_id', '$menu_id', '$qty', '$harga_satuan', '$subtotal')";
                        mysqli_query($koneksi, $query_detail);
                    }
                }
            }

            // 3. Update total_harga di tabel pesanan dengan nilai akhir
            mysqli_query($koneksi, "UPDATE pesanan SET total_harga='$total_harga' WHERE id='$pesanan_id'");

            header("Location: pesanan.php?pesan=tambah");
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
    <title>Tambah Pesanan - Cafe Kiswah</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .input-jumlah {
            width: 70px !important;
            padding: 6px 10px !important;
            text-align: center;
        }
        .info-pesanan-grup {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        @media (max-width: 768px) {
            .info-pesanan-grup {
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
        <li><a href="tambah_menu.php">➕ Tambah Menu</a></li>
        <li><a href="pesanan.php" class="aktif">📋 Kelola Pesanan</a></li>
        <li><a href="logout.php">🚪 Logout</a></li>
    </ul>
</nav>

<div class="konten">
    <div class="header-halaman">
        <h2>➕ Buat Pesanan Baru</h2>
        <a href="pesanan.php" class="tombol-kecil">← Kembali</a>
    </div>

    <?php
    if (!empty($pesan)) {
        $bagian = explode("|", $pesan);
        echo "<div class='pesan {$bagian[0]}'>{$bagian[1]}</div>";
    }
    ?>

    <form method="POST" action="">
        <!-- BAGIAN 1: INFORMASI PESANAN -->
        <div class="kotak">
            <h3>📝 Informasi Pesanan</h3>
            <div class="info-pesanan-grup">
                <div class="grup-form">
                    <label>Pelanggan / User <span class="wajib">*</span></label>
                    <select name="user_id" required>
                        <option value="">-- Pilih Pelanggan --</option>
                        <?php while ($user = mysqli_fetch_assoc($users_result)) : ?>
                            <option value="<?= $user['id'] ?>" <?= ($_SESSION['user_id'] == $user['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($user['nama']) ?> (<?= htmlspecialchars($user['username']) ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="grup-form">
                    <label>Status Pesanan <span class="wajib">*</span></label>
                    <select name="status" required>
                        <option value="Pending" selected>⏳ Pending</option>
                        <option value="Diproses">🔄 Diproses</option>
                        <option value="Selesai">✅ Selesai</option>
                        <option value="Dibatalkan">❌ Dibatalkan</option>
                    </select>
                </div>
            </div>

            <div class="grup-form">
                <label>Catatan Tambahan <small>(opsional)</small></label>
                <textarea name="catatan" rows="2" placeholder="Contoh: Kopi kurangi gula, es batu dipisah..."></textarea>
            </div>
        </div>

        <!-- BAGIAN 2: DAFTAR MENU -->
        <div class="kotak">
            <h3>🍽️ Pilih Menu & Jumlah Porsi</h3>
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
                                   class="input-jumlah" value="0" min="0" max="100">
                        </td>
                    </tr>
                    <?php endwhile; ?>

                    <?php if (mysqli_num_rows($menu_result) == 0) : ?>
                    <tr>
                        <td colspan="5" style="text-align:center; color:#999; padding:30px;">
                            Tidak ada menu tersedia untuk dipesan. <a href="tambah_menu.php">Tambah menu terlebih dahulu!</a>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="tombol-grup" style="margin-top: 20px;">
                <button type="submit" class="tombol-utama">💾 Simpan Pesanan</button>
                <a href="pesanan.php" class="tombol-batal">Batal</a>
            </div>
        </div>
    </form>
</div>

</body>
</html>
