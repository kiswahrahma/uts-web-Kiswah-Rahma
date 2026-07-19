<?php
// ============================================
// FILE: detail_pesanan.php
// ============================================

session_start();
include "config.php";
include "auth.php";
require_admin();

$id = $_GET["id"] ?? 0;
$id = mysqli_real_escape_string($koneksi, $id);

// 1. Ambil info pesanan
$query_pesanan = "SELECT pesanan.*, users.nama AS nama_user, users.username AS username_user 
                  FROM pesanan 
                  JOIN users ON pesanan.user_id = users.id 
                  WHERE pesanan.id = '$id'";
$result_pesanan = mysqli_query($koneksi, $query_pesanan);

if (mysqli_num_rows($result_pesanan) == 0) {
    header("Location: pesanan.php");
    exit();
}

$pesanan = mysqli_fetch_assoc($result_pesanan);

// 2. Ambil detail pesanan (item yang dibeli)
$query_detail = "SELECT pesanan_detail.*, menu.nama_menu, menu.kategori 
                 FROM pesanan_detail 
                 JOIN menu ON pesanan_detail.menu_id = menu.id 
                 WHERE pesanan_detail.pesanan_id = '$id'";
$result_detail = mysqli_query($koneksi, $query_detail);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan #<?= $pesanan['id'] ?> - Noir Cafe</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/dark-mode.js" defer></script>
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

        .meta-detail {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        .meta-item {
            border-bottom: 1px solid #f0f0f0;
            padding: 8px 0;
        }
        .meta-item strong {
            display: inline-block;
            width: 150px;
            color: #666;
        }

        .total-section {
            text-align: right;
            font-size: 18px;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px solid #6f4e37;
        }

        /* ===== CETAK / STRUK STYLE ===== */
        @media print {
            body {
                background: white !important;
                color: black !important;
                font-family: 'Courier New', Courier, monospace;
                font-size: 12px;
            }
            .navbar, .tombol-kecil, .tombol-utama, .tombol-edit, .tombol-batal, .header-halaman, .no-print {
                display: none !important;
            }
            .konten {
                margin: 0 !important;
                padding: 0 !important;
                max-width: 100% !important;
            }
            .kotak {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                background: white !important;
            }
            .meta-detail {
                grid-template-columns: 1fr !important;
                gap: 5px !important;
            }
            .tabel-data thead {
                background: #f0f0f0 !important;
                color: black !important;
            }
            .tabel-data th, .tabel-data td {
                padding: 6px !important;
                border-bottom: 1px dashed #ccc !important;
            }
            .total-section {
                border-top: 1px dashed black !important;
            }
            /* Menampilkan struk minimalis */
            .struk-header {
                text-align: center;
                margin-bottom: 20px;
            }
            .struk-header h1 {
                font-size: 20px;
                margin: 0;
            }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="nav-brand">☕ Noir Cafe</div>
    <ul class="nav-menu">
        <li><a href="dashboard.php">🏠 Dashboard</a></li>
        <li><a href="menu.php">🍽️ Daftar Menu</a></li>
        <li><a href="pesanan.php" class="aktif">📋 Kelola Pesanan</a></li>
        <li><a href="index.php" target="_blank">🌐 Lihat Web</a></li>
        <li><a href="logout.php">🚪 Logout</a></li>
    </ul>
</nav>

<div class="konten">
    
    <!-- Header struk saat diprint -->
    <div class="struk-header" style="display: none; text-align: center; margin-bottom: 20px;">
        <h2>☕ Noir Cafe</h2>
        <p>Jl. Noir Cafe No. 1, Jakarta</p>
        <p>------------------------------------------</p>
    </div>

    <div class="header-halaman">
        <h2>👁️ Detail Pesanan #<?= $pesanan['id'] ?></h2>
        <div class="no-print" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <button onclick="window.print()" class="tombol-utama" style="background: #37474f;">🖨️ Cetak Struk</button>
            <a href="export_detail_pesanan_word.php?id=<?= $pesanan['id'] ?>" class="tombol-edit" style="padding: 12px 24px; font-size: 14px; background: #0288d1;">⬇️ Export Word</a>
            <a href="edit_pesanan.php?id=<?= $pesanan['id'] ?>" class="tombol-edit" style="padding: 12px 24px; font-size: 14px;">✏️ Edit Status</a>
            <a href="pesanan.php" class="tombol-batal">← Kembali</a>
        </div>
    </div>

    <!-- Informasi Utama Pesanan -->
    <div class="kotak">
        <h3>📋 Ringkasan Pesanan</h3>
        <div class="meta-detail">
            <div class="meta-item">
                <strong>ID Pesanan:</strong> #<?= $pesanan['id'] ?>
            </div>
            <div class="meta-item">
                <strong>Tanggal:</strong> <?= date('d-m-Y H:i:s', strtotime($pesanan['tanggal'])) ?>
            </div>
            <div class="meta-item">
                <strong>Pelanggan:</strong> <?= htmlspecialchars($pesanan['nama_user']) ?> (<?= htmlspecialchars($pesanan['username_user']) ?>)
            </div>
            <div class="meta-item">
                <strong>Status:</strong> 
                <?php
                $status_class = "badge-" . strtolower($pesanan["status"]);
                ?>
                <span class="<?= $status_class ?>">
                    <?php
                    if ($pesanan["status"] == 'Pending') echo "⏳ Pending";
                    elseif ($pesanan["status"] == 'Diproses') echo "🔄 Diproses";
                    elseif ($pesanan["status"] == 'Selesai') echo "✅ Selesai";
                    elseif ($pesanan["status"] == 'Dibatalkan') echo "❌ Dibatalkan";
                    else echo $pesanan["status"];
                    ?>
                </span>
            </div>
            <div class="meta-item">
                <strong>Metode Pembayaran:</strong>
                <?php
                $mp = $pesanan['metode_pembayaran'] ?? 'Tunai';
                $mp_icon = '';
                if ($mp === 'Tunai') $mp_icon = '💵';
                elseif ($mp === 'Transfer Bank') $mp_icon = '🏦';
                elseif ($mp === 'QRIS') $mp_icon = '📱';
                elseif ($mp === 'Dompet Digital') $mp_icon = '👛';
                ?>
                <span style="background: #fdf3e3; color: #6f4e37; padding: 3px 12px; border-radius: 20px; font-weight: 600; font-size: 13px;">
                    <?= $mp_icon ?> <?= htmlspecialchars($mp) ?>
                </span>
            </div>
        </div>

        <div class="grup-form" style="margin-top: 15px;">
            <label style="color: #666; font-weight: 600;">Catatan Pesanan:</label>
            <p style="background: #fdfaf6; padding: 12px; border-radius: 8px; border-left: 4px solid #6f4e37; font-style: italic;">
                <?= htmlspecialchars($pesanan['catatan'] ?: 'Tidak ada catatan.') ?>
            </p>
        </div>
    </div>

    <!-- Rincian Item Menu yang Dipesan -->
    <div class="kotak">
        <h3>🍽️ Rincian Menu</h3>
        <table class="tabel-data">
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Nama Menu</th>
                    <th>Kategori</th>
                    <th>Harga Satuan</th>
                    <th style="text-align: center; width: 100px;">Jumlah</th>
                    <th style="text-align: right; width: 150px;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                while ($item = mysqli_fetch_assoc($result_detail)) :
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><strong><?= htmlspecialchars($item['nama_menu']) ?></strong></td>
                    <td>
                        <span class="badge badge-<?= strtolower($item['kategori']) ?>">
                            <?= $item['kategori'] ?>
                        </span>
                    </td>
                    <td>Rp <?= number_format($item['harga_satuan'], 0, ',', '.') ?></td>
                    <td style="text-align: center;"><?= $item['jumlah'] ?></td>
                    <td style="text-align: right;"><strong>Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></strong></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="total-section">
            Total Pembayaran: <strong style="color: #6f4e37; font-size: 24px;">Rp <?= number_format($pesanan['total_harga'], 0, ',', '.') ?></strong>
        </div>
    </div>

</div>

<!-- Tampilkan print header secara dinamis saat print -->
<script>
    window.onbeforeprint = function() {
        document.querySelector('.struk-header').style.display = 'block';
    };
    window.onafterprint = function() {
        document.querySelector('.struk-header').style.display = 'none';
    };
</script>
</body>
</html>
