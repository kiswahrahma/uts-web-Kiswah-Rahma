<?php
// ============================================
// FILE: export_detail_pesanan_word.php
// ============================================

session_start();
include "config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'] ?? 0;
$id = mysqli_real_escape_string($koneksi, $id);

// Ambil info pesanan (ringkasan)
$query_pesanan = "SELECT pesanan.*, users.nama AS nama_user, users.username AS username_user
                  FROM pesanan
                  JOIN users ON pesanan.user_id = users.id
                  WHERE pesanan.id = '$id'";
$result_pesanan = mysqli_query($koneksi, $query_pesanan);

if (!$result_pesanan || mysqli_num_rows($result_pesanan) === 0) {
    header("Location: pesanan.php");
    exit();
}

$pesanan = mysqli_fetch_assoc($result_pesanan);

// Ambil detail pesanan (rincian menu)
$query_detail = "SELECT pesanan_detail.*, menu.nama_menu, menu.kategori
                 FROM pesanan_detail
                 JOIN menu ON pesanan_detail.menu_id = menu.id
                 WHERE pesanan_detail.pesanan_id = '$id'
                 ORDER BY pesanan_detail.id ASC";
$result_detail = mysqli_query($koneksi, $query_detail);

// Set header untuk mendownload sebagai file Word
header("Content-Type: application/vnd.ms-word");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Content-Disposition: attachment; filename=Detail_Pesanan_#" . $pesanan['id'] . ".doc");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Detail Pesanan #<?= $pesanan['id'] ?> - Noir Cafe</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #6f4e37;
            padding-bottom: 10px;
        }
        .header h1 {
            color: #6f4e37;
            font-size: 26px;
            margin: 0;
        }
        .header p {
            color: #777;
            margin: 5px 0 0 0;
            font-size: 14px;
        }
        .section-title {
            color: #6f4e37;
            font-size: 18px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-top: 25px;
            margin-bottom: 15px;
        }
        .tabel-info {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .tabel-info td {
            padding: 8px 10px;
            vertical-align: top;
        }
        .tabel-info td.label {
            font-weight: bold;
            width: 150px;
            color: #555;
        }
        .tabel-data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .tabel-data th {
            background-color: #6f4e37;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #6f4e37;
        }
        .tabel-data td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        .tabel-data tr:nth-child(even) {
            background-color: #fdfaf7;
        }
        .total-container {
            text-align: right;
            margin-top: 20px;
            font-size: 16px;
        }
        .total-harga {
            color: #6f4e37;
            font-size: 20px;
            font-weight: bold;
        }
        .badge {
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>☕ NOIR CAFE</h1>
        <p>Laporan Rincian Transaksi & Detail Pesanan Resmi</p>
    </div>

    <div class="section-title">📋 Ringkasan Pesanan</div>
    <table class="tabel-info">
        <tr>
            <td class="label">ID Pesanan:</td>
            <td>#<?= $pesanan['id'] ?></td>
        </tr>
        <tr>
            <td class="label">Tanggal & Waktu:</td>
            <td><?= date('d-m-Y H:i:s', strtotime($pesanan['tanggal'])) ?></td>
        </tr>
        <tr>
            <td class="label">Pelanggan:</td>
            <td><?= htmlspecialchars($pesanan['nama_user']) ?> (<?= htmlspecialchars($pesanan['username_user']) ?>)</td>
        </tr>
        <tr>
            <td class="label">Status:</td>
            <td><strong><?= $pesanan['status'] ?></strong></td>
        </tr>
        <tr>
            <td class="label">Catatan:</td>
            <td><?= htmlspecialchars($pesanan['catatan'] ?: 'Tidak ada catatan.') ?></td>
        </tr>
    </table>

    <div class="section-title">🍽️ Rincian Menu Yang Dipesan</div>
    <table class="tabel-data">
        <thead>
            <tr>
                <th style="width: 50px;">No</th>
                <th>Nama Menu</th>
                <th>Kategori</th>
                <th>Harga Satuan</th>
                <th style="text-align: center; width: 80px;">Jumlah</th>
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
                <td><?= $item['kategori'] ?></td>
                <td>Rp <?= number_format($item['harga_satuan'], 0, ',', '.') ?></td>
                <td style="text-align: center;"><?= $item['jumlah'] ?></td>
                <td style="text-align: right;"><strong>Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></strong></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="total-container">
        Total Pembayaran: <span class="total-harga">Rp <?= number_format($pesanan['total_harga'], 0, ',', '.') ?></span>
    </div>

</body>
</html>
