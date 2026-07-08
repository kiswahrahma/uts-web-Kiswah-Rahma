<?php
session_start();
include "config.php";
include "auth.php";
require_admin();

// Ambil data untuk statistik dashboard
$total_menu = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM menu"))['total'];
$total_tersedia = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM menu WHERE stok='Tersedia'"))['total'];
$total_habis = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM menu WHERE stok='Habis'"))['total'];

// Ambil daftar menu untuk ditampilkan di bawah statistik
$ambil_menu = mysqli_query($koneksi, "SELECT * FROM menu ORDER BY id DESC LIMIT 4");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Noir Cafe</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* CSS Tambahan khusus untuk layout Grid Gambar Menu di Dashboard */
        .grid-menu-dashboard {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }

        .kartu-menu-populer {
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            padding: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            display: flex;
            flex-direction: column;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .kartu-menu-populer:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.06);
        }

        .menu-foto-wadah {
            width: 100%;
            height: 150px;
            overflow: hidden;
            border-radius: 8px;
            margin-bottom: 12px;
            background-color: #f7f5f2;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #f0f0f0;
        }

        .menu-foto-wadah img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .ikon-default {
            font-size: 50px;
            color: #ccc;
            user-select: none;
        }

        .detail-menu-populer h4 {
            font-size: 16px;
            color: #333;
            margin-bottom: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .detail-menu-populer .harga-tag {
            font-weight: bold;
            color: #6f4e37;
            font-size: 14px;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="nav-brand">☕ Noir Cafe</div>
    <ul class="nav-menu">
        <li><a href="dashboard.php" class="aktif">🏠 Dashboard</a></li>
        <li><a href="menu.php">🍽️ Daftar Menu</a></li>
        <li><a href="pesanan.php">📋 Kelola Pesanan</a></li>
        <li><a href="index.php" target="_blank">🌐 Lihat Web</a></li>
        <li><a href="logout.php">🚪 Logout</a></li>
    </ul>
</nav>

<div class="konten">
    <div class="sambutan">
        <h2>Selamat Datang, <?= htmlspecialchars($_SESSION["user_nama"] ?? $_SESSION["username"] ?? "Admin"); ?>! 👋</h2>
        <p>Berikut adalah ringkasan data operasional Noir Cafe hari ini.</p>
    </div>

    <!-- Grid Statistik Atas -->
    <div class="grid-kartu">
        <div class="kartu kartu-biru">
            <div class="kartu-ikon">🍽️</div>
            <div class="kartu-info">
                <h3><?= $total_menu; ?></h3>
                <p>Total Menu</p>
            </div>
        </div>
        <div class="kartu kartu-hijau">
            <div class="kartu-ikon">✅</div>
            <div class="kartu-info">
                <h3><?= $total_tersedia; ?></h3>
                <p>Stok Tersedia</p>
            </div>
        </div>
        <div class="kartu kartu-merah">
            <div class="kartu-ikon">❌</div>
            <div class="kartu-info">
                <h3><?= $total_habis; ?></h3>
                <p>Stok Habis</p>
            </div>
        </div>
    </div>

    <!-- Bagian Tampilan Menu Cafe Berfoto -->
    <div class="kotak">
        <h3>✨ Menu Terbaru Cafe</h3>
        
        <div class="grid-menu-dashboard">
            <?php while ($baris = mysqli_fetch_assoc($ambil_menu)) : ?>
                <div class="kartu-menu-populer">
                    
                    <!-- BOX FOTO MENU -->
                    <div class="menu-foto-wadah">
                        <?php if (!empty($baris['foto']) && file_exists('uploads/' . $baris['foto'])) : ?>
                            <img src="uploads/<?= htmlspecialchars($baris['foto']) ?>" alt="<?= htmlspecialchars($baris['nama_menu']) ?>">
                        <?php else : ?>
                            <?php 
                            $ikon = "🍽️";
                            if ($baris['kategori'] == 'Makanan') $ikon = "🍛";
                            if ($baris['kategori'] == 'Minuman') $ikon = "🥤";
                            if ($baris['kategori'] == 'Snack') $ikon = "🍟";
                            ?>
                            <div class="ikon-default"><?= $ikon ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- DETAIL NAMA & HARGA -->
                    <div class="detail-menu-populer">
                        <h4><?= htmlspecialchars($baris['nama_menu']) ?></h4>
                        <div class="harga-tag">Rp <?= number_format($baris['harga'], 0, ',', '.') ?></div>
                        
                        <?php if ($baris["stok"] == "Tersedia") : ?>
                            <span class="badge badge-makanan" style="background: #e8f5e9; color: #2e7d32; font-size: 11px;">Tersedia</span>
                        <?php else : ?>
                            <span class="badge badge-merah" style="background: #fdecea; color: #c62828; font-size: 11px;">Habis</span>
                        <?php endif; ?>
                    </div>

                </div>
            <?php endwhile; ?>

            <?php if (mysqli_num_rows($ambil_menu) == 0) : ?>
                <p style="color: #999; grid-column: 1/-1; text-align: center; padding: 20px;">Belum ada data menu yang ditambahkan.</p>
            <?php endif; ?>
        </div>

    </div>
</div>

</body>
</html>