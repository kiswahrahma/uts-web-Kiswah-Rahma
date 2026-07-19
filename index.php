<?php
// ============================================
// FILE: index.php
// Halaman utama publik: siapa saja bisa lihat menu & harga.
// Untuk memesan, pelanggan wajib login dulu.
// ============================================

session_start();
include "config.php";
include "auth.php";

$kategori_filter = $_GET["kategori"] ?? "";
$cari = $_GET["cari"] ?? "";

$conditions = [];
if (!empty($kategori_filter)) {
    $conditions[] = "kategori='" . mysqli_real_escape_string($koneksi, $kategori_filter) . "'";
}
if (!empty($cari)) {
    $conditions[] = "nama_menu LIKE '%" . mysqli_real_escape_string($koneksi, $cari) . "%'";
}
$filter = count($conditions) > 0 ? "WHERE " . implode(" AND ", $conditions) : "";

$hasil_menu = mysqli_query($koneksi, "SELECT * FROM menu $filter ORDER BY kategori, nama_menu");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Noir Cafe - Menu & Harga</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/dark-mode.js" defer></script>
</head>
<body>

<nav class="navbar">
    <div class="nav-brand">☕ Noir Cafe</div>

    <?php if (is_admin()) : ?>
        <ul class="nav-menu">
            <li><a href="index.php" class="aktif">🍽️ Menu</a></li>
        </ul>
        <div class="nav-auth">
            <span class="nav-sapaan">Halo, Admin <?= htmlspecialchars($_SESSION["user_nama"]) ?></span>
            <a href="dashboard.php" class="tombol-nav-daftar">🏠 Dashboard</a>
            <a href="logout.php" class="tombol-nav-login">🚪 Logout</a>
        </div>
    <?php elseif (is_pelanggan()) : ?>
        <ul class="nav-menu">
            <li><a href="index.php" class="aktif">🍽️ Menu</a></li>
            <li><a href="pesanan_saya.php">📋 Pesanan Saya</a></li>
        </ul>
        <div class="nav-auth">
            <span class="nav-sapaan">Halo, <?= htmlspecialchars($_SESSION["user_nama"]) ?></span>
            <a href="logout.php" class="tombol-nav-login">🚪 Logout</a>
        </div>
    <?php else : ?>
        <ul class="nav-menu">
            <li><a href="index.php" class="aktif">🍽️ Menu</a></li>
        </ul>
        <div class="nav-auth">
            <a href="login.php" class="tombol-nav-login">Masuk</a>
            <a href="register.php" class="tombol-nav-daftar">Daftar</a>
        </div>
    <?php endif; ?>
</nav>

<div class="hero-beranda">
    <h1>☕ Selamat Datang di Noir Cafe</h1>
    <p>Lihat menu dan harga terbaru kami. Masuk ke akunmu untuk mulai memesan!</p>
</div>

<div class="konten">

    <!-- Form Pencarian -->
    <form method="GET" action="" style="margin-bottom: 20px; display: flex; gap: 10px;">
        <?php if (!empty($kategori_filter)) : ?>
            <input type="hidden" name="kategori" value="<?= htmlspecialchars($kategori_filter) ?>">
        <?php endif; ?>
        <input type="text" name="cari" placeholder="🔍 Cari nama menu..." value="<?= htmlspecialchars($cari) ?>"
               style="flex: 1; padding: 10px 15px; border: 2px solid #e0e0e0; border-radius: 8px;">
        <button type="submit" class="tombol-utama" style="width: auto; padding: 10px 20px; margin: 0;">Cari</button>
    </form>

    <div class="filter-kategori">
        <a href="index.php?cari=<?= urlencode($cari) ?>" class="tombol-filter <?= empty($kategori_filter) ? 'aktif' : '' ?>">Semua</a>
        <a href="index.php?kategori=Makanan&cari=<?= urlencode($cari) ?>" class="tombol-filter <?= $kategori_filter == 'Makanan' ? 'aktif' : '' ?>">🍛 Makanan</a>
        <a href="index.php?kategori=Minuman&cari=<?= urlencode($cari) ?>" class="tombol-filter <?= $kategori_filter == 'Minuman' ? 'aktif' : '' ?>">🥤 Minuman</a>
        <a href="index.php?kategori=Snack&cari=<?= urlencode($cari) ?>" class="tombol-filter <?= $kategori_filter == 'Snack' ? 'aktif' : '' ?>">🍟 Snack</a>
    </div>

    <div class="grid-menu-dashboard">
        <?php while ($menu = mysqli_fetch_assoc($hasil_menu)) : ?>
            <?php $habis = ($menu["stok"] == "Habis"); ?>
            <div class="kartu-menu-pelanggan">
                <div class="menu-foto-wadah">
                    <?php if (!empty($menu['foto']) && file_exists('uploads/' . $menu['foto'])) : ?>
                        <img src="uploads/<?= htmlspecialchars($menu['foto']) ?>" alt="<?= htmlspecialchars($menu['nama_menu']) ?>">
                    <?php else : ?>
                        <?php
                        $ikon = "🍽️";
                        if ($menu['kategori'] == 'Makanan') $ikon = "🍛";
                        if ($menu['kategori'] == 'Minuman') $ikon = "🥤";
                        if ($menu['kategori'] == 'Snack') $ikon = "🍟";
                        ?>
                        <div class="ikon-default"><?= $ikon ?></div>
                    <?php endif; ?>
                </div>

                <h4><?= htmlspecialchars($menu['nama_menu']) ?></h4>
                <span class="badge badge-<?= strtolower($menu['kategori']) ?>" style="width: fit-content; margin-bottom: 8px;"><?= $menu['kategori'] ?></span>
                <div class="harga-tag">Rp <?= number_format($menu['harga'], 0, ',', '.') ?></div>
                <p class="deskripsi-menu"><?= htmlspecialchars($menu['deskripsi'] ?: 'Tidak ada deskripsi.') ?></p>

                <?php if ($habis) : ?>
                    <span class="tombol-pesan-kartu habis">❌ Stok Habis</span>
                <?php elseif (is_admin()) : ?>
                    <a href="menu.php" class="tombol-pesan-kartu" style="background:#546e7a;">⚙️ Kelola di Admin</a>
                <?php elseif (is_pelanggan()) : ?>
                    <a href="pesan.php?menu_id=<?= $menu['id'] ?>" class="tombol-pesan-kartu">🛒 Pesan</a>
                <?php else : ?>
                    <a href="login.php?redirect=pesan.php%3Fmenu_id%3D<?= $menu['id'] ?>" class="tombol-pesan-kartu">🛒 Pesan (Login dulu)</a>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>

        <?php if (mysqli_num_rows($hasil_menu) == 0) : ?>
            <p style="color: #999; grid-column: 1/-1; text-align: center; padding: 20px;">Belum ada menu tersedia.</p>
        <?php endif; ?>
    </div>

</div>

</body>
</html>
