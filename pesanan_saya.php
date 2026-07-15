<?php
// ============================================
// FILE: pesanan_saya.php
// Riwayat pesanan milik PELANGGAN yang sedang login saja.
// ============================================

session_start();
include "config.php";
include "auth.php";
require_pelanggan();

$user_id = $_SESSION["user_id"];
$pesan = $_GET["pesan"] ?? "";

$hasil = mysqli_query($koneksi, "SELECT * FROM pesanan WHERE user_id='$user_id' ORDER BY tanggal DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - Noir Cafe</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .kartu-pesanan {
            background: #fff; border: 1px solid #e0e0e0; border-radius: 12px;
            margin-bottom: 15px; overflow: hidden;
        }
        .kartu-pesanan summary {
            list-style: none; cursor: pointer; padding: 16px 20px;
            display: flex; justify-content: space-between; align-items: center;
            flex-wrap: wrap; gap: 10px;
        }
        .kartu-pesanan summary::-webkit-details-marker { display: none; }
        .kartu-pesanan summary:after { content: "▾"; color: #999; margin-left: 8px; }
        .kartu-pesanan .isi-detail { padding: 0 20px 18px; border-top: 1px solid #f0f0f0; }
        .kartu-pesanan .isi-detail table { margin-top: 12px; }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="nav-brand">☕ Noir Cafe</div>
    <ul class="nav-menu">
        <li><a href="index.php">🍽️ Menu</a></li>
        <li><a href="pesanan_saya.php" class="aktif">📋 Pesanan Saya</a></li>
    </ul>
    <div class="nav-auth">
        <span class="nav-sapaan">Halo, <?= htmlspecialchars($_SESSION["user_nama"]) ?></span>
        <a href="logout.php" class="tombol-nav-login">🚪 Logout</a>
    </div>
</nav>

<div class="konten">
    <div class="header-halaman">
        <h2>📋 Pesanan Saya</h2>
        <a href="pesan.php" class="tombol-utama" style="width:auto; padding:10px 20px; margin:0;">+ Pesan Lagi</a>
    </div>

    <?php if ($pesan == "tambah") : ?>
        <div class="pesan sukses">✅ Pesanan kamu berhasil dikirim! Silakan tunggu konfirmasi dari cafe.</div>
    <?php endif; ?>

    <?php if (mysqli_num_rows($hasil) == 0) : ?>
        <div class="kotak" style="text-align:center; color:#999; padding:30px;">
            Kamu belum pernah memesan. <a href="index.php">Yuk lihat menu &amp; pesan sekarang!</a>
        </div>
    <?php endif; ?>

    <?php while ($p = mysqli_fetch_assoc($hasil)) :
        $status_class = "badge-" . strtolower($p["status"]);
        $label_status = [
            "Pending" => "⏳ Pending",
            "Diproses" => "🔄 Diproses",
            "Selesai" => "✅ Selesai",
            "Dibatalkan" => "❌ Dibatalkan",
        ][$p["status"]] ?? $p["status"];
    ?>
    <details class="kartu-pesanan">
        <summary>
            <div>
                <strong>#<?= $p['id'] ?></strong> &middot;
                <?= date('d-m-Y H:i', strtotime($p['tanggal'])) ?>
            </div>
            <span class="<?= $status_class ?>"><?= $label_status ?></span>
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 12px; color: #777; background: #f5f0ea; padding: 3px 10px; border-radius: 20px;">
                    <?php
                    $mp = $p['metode_pembayaran'] ?? 'Tunai';
                    if ($mp === 'Tunai') echo '💵 ';
                    elseif ($mp === 'Transfer Bank') echo '🏦 ';
                    elseif ($mp === 'QRIS') echo '📱 ';
                    elseif ($mp === 'Dompet Digital') echo '👛 ';
                    echo htmlspecialchars($mp);
                    ?>
                </span>
                <strong>Rp <?= number_format($p['total_harga'], 0, ',', '.') ?></strong>
            </div>
        </summary>
        <div class="isi-detail">
            <?php if (!empty($p['metode_pembayaran'])) : ?>
                <p style="color:#555; font-size:13px; margin-bottom: 8px;">
                    💳 <strong>Metode Pembayaran:</strong> <?= htmlspecialchars($p['metode_pembayaran']) ?>
                </p>
            <?php endif; ?>
            <?php if (!empty($p['catatan'])) : ?>
                <p style="color:#666; font-style:italic;">Catatan: <?= htmlspecialchars($p['catatan']) ?></p>
            <?php endif; ?>
            <table class="tabel-data">
                <thead>
                    <tr>
                        <th>Nama Menu</th>
                        <th style="text-align:center;">Jumlah</th>
                        <th style="text-align:right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $detail = mysqli_query($koneksi, "SELECT pesanan_detail.*, menu.nama_menu
                        FROM pesanan_detail JOIN menu ON pesanan_detail.menu_id = menu.id
                        WHERE pesanan_id='" . $p['id'] . "'");
                    while ($item = mysqli_fetch_assoc($detail)) : ?>
                        <tr>
                            <td><?= htmlspecialchars($item['nama_menu']) ?></td>
                            <td style="text-align:center;"><?= $item['jumlah'] ?></td>
                            <td style="text-align:right;">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </details>
    <?php endwhile; ?>

</div>

</body>
</html>
