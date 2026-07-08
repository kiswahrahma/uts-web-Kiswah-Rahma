<?php
// ============================================
// FILE: edit_menu.php (update - ada stok)
// ============================================

session_start();
include "config.php";
include "auth.php";
require_admin();

$id  = $_GET["id"] ?? 0;
$cek = mysqli_query($koneksi, "SELECT * FROM menu WHERE id='$id'");

if (mysqli_num_rows($cek) == 0) {
    header("Location: menu.php");
    exit();
}

$menu  = mysqli_fetch_assoc($cek);
$pesan = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nama_menu = trim($_POST["nama_menu"]);
    $kategori  = $_POST["kategori"];
    $harga     = $_POST["harga"];
    $deskripsi = trim($_POST["deskripsi"]);
    $stok      = $_POST["stok"];

    if (empty($nama_menu) || empty($kategori) || empty($harga) || empty($stok)) {
        $pesan = "error|Semua kolom wajib diisi!";

    } elseif (!is_numeric($harga) || $harga <= 0) {
        $pesan = "error|Harga harus berupa angka positif!";

    } else {
        $foto_nama = $menu['foto']; // Default tetap pakai foto lama
        $upload_ok = true;

        // Cek apakah ada file foto baru yang diunggah
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['foto']['tmp_name'];
            $file_name = $_FILES['foto']['name'];
            $file_size = $_FILES['foto']['size'];
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($ext, $allowed_ext)) {
                $pesan = "error|Format gambar tidak didukung! Gunakan JPG, JPEG, PNG, atau WEBP.";
                $upload_ok = false;
            } elseif ($file_size > 2 * 1024 * 1024) {
                $pesan = "error|Ukuran gambar terlalu besar! Maksimal 2MB.";
                $upload_ok = false;
            } else {
                // Berhasil divalidasi, upload file baru
                $new_foto_nama = time() . '_' . uniqid() . '.' . $ext;
                $dest_path = 'uploads/' . $new_foto_nama;

                if (move_uploaded_file($file_tmp, $dest_path)) {
                    // Hapus foto lama jika ada
                    if (!empty($menu['foto']) && file_exists('uploads/' . $menu['foto'])) {
                        unlink('uploads/' . $menu['foto']);
                    }
                    $foto_nama = $new_foto_nama;
                } else {
                    $pesan = "error|Gagal mengunggah foto baru.";
                    $upload_ok = false;
                }
            }
        }

        if ($upload_ok) {
            $foto_val = $foto_nama ? "'$foto_nama'" : "NULL";
            $sql = "UPDATE menu
                    SET nama_menu='$nama_menu', kategori='$kategori',
                        harga='$harga', deskripsi='$deskripsi', stok='$stok', foto=$foto_val
                    WHERE id='$id'";

            if (mysqli_query($koneksi, $sql)) {
                header("Location: menu.php?pesan=edit");
                exit();
            } else {
                // Jika query gagal dan kita baru mengunggah file baru, hapus file baru tersebut agar tidak menumpuk sampah
                if ($foto_nama !== $menu['foto'] && $foto_nama) {
                    unlink('uploads/' . $foto_nama);
                }
                $pesan = "error|Gagal mengupdate menu ke database. Coba lagi.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Menu - Cafe Kiswah</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-brand">☕ Cafe Kiswah</div>
    <ul class="nav-menu">
        <li><a href="dashboard.php">🏠 Dashboard</a></li>
        <li><a href="menu.php" class="aktif">🍽️ Daftar Menu</a></li>
        <li><a href="pesanan.php">📋 Kelola Pesanan</a></li>
        <li><a href="logout.php">🚪 Logout</a></li>
    </ul>
</nav>

<div class="konten">
    <div class="header-halaman">
        <h2>✏️ Edit Menu</h2>
        <a href="menu.php" class="tombol-kecil">← Kembali</a>
    </div>

    <?php
    if (!empty($pesan)) {
        $bagian = explode("|", $pesan);
        echo "<div class='pesan {$bagian[0]}'>{$bagian[1]}</div>";
    }
    ?>

    <div class="kotak kotak-form">
        <form method="POST" action="" enctype="multipart/form-data">

            <div class="grup-form">
                <label>Nama Menu <span class="wajib">*</span></label>
                <input type="text" name="nama_menu"
                       value="<?= $menu['nama_menu'] ?>" required>
            </div>

            <div class="grup-form">
                <label>Kategori <span class="wajib">*</span></label>
                <select name="kategori" required>
                    <option value="">-- Pilih Kategori --</option>
                    <option value="Makanan" <?= $menu['kategori'] == 'Makanan' ? 'selected' : '' ?>>🍛 Makanan</option>
                    <option value="Minuman" <?= $menu['kategori'] == 'Minuman' ? 'selected' : '' ?>>🥤 Minuman</option>
                    <option value="Snack"   <?= $menu['kategori'] == 'Snack'   ? 'selected' : '' ?>>🍟 Snack</option>
                </select>
            </div>

            <div class="grup-form">
                <label>Harga (Rp) <span class="wajib">*</span></label>
                <input type="number" name="harga"
                       value="<?= $menu['harga'] ?>" min="1" required>
            </div>

            <!-- Pilihan stok - otomatis terpilih sesuai data lama -->
            <div class="grup-form">
                <label>Status Stok <span class="wajib">*</span></label>
                <select name="stok" required>
                    <option value="">-- Pilih Status --</option>
                    <option value="Tersedia" <?= $menu['stok'] == 'Tersedia' ? 'selected' : '' ?>>✅ Tersedia</option>
                    <option value="Habis"    <?= $menu['stok'] == 'Habis'    ? 'selected' : '' ?>>❌ Habis</option>
                </select>
            </div>

            <!-- Pratinjau foto lama & Input file -->
            <div class="grup-form">
                <label>Foto Menu Saat Ini</label>
                <?php if (!empty($menu['foto']) && file_exists('uploads/' . $menu['foto'])) : ?>
                    <div style="margin-bottom: 10px;">
                        <img src="uploads/<?= $menu['foto'] ?>" alt="Foto Menu" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px; border: 2px solid #6f4e37;">
                    </div>
                <?php else : ?>
                    <p style="color: #999; font-style: italic; margin-bottom: 10px;">Belum ada foto.</p>
                <?php endif; ?>
                <label>Unggah Foto Baru <small>(opsional, maks 2MB, format: JPG/PNG/WEBP)</small></label>
                <input type="file" name="foto" accept="image/*">
            </div>

            <div class="grup-form">
                <label>Deskripsi <small>(opsional)</small></label>
                <textarea name="deskripsi" rows="3"><?= $menu['deskripsi'] ?></textarea>
            </div>

            <div class="tombol-grup">
                <button type="submit" class="tombol-utama">💾 Simpan Perubahan</button>
                <a href="menu.php" class="tombol-batal">Batal</a>
            </div>

        </form>
    </div>
</div>

</body>
</html>