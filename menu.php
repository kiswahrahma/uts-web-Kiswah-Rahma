<?php
session_start();
include "config.php";
include "auth.php";
require_admin();

// ============================================
// PROSES TAMBAH MENU
// ============================================
$error_tambah = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_tambah_menu"])) {
    $nama_menu = trim($_POST["nama_menu"]);
    $kategori  = $_POST["kategori"];
    $harga     = $_POST["harga"];
    $deskripsi = trim($_POST["deskripsi"]);
    $stok      = $_POST["stok"];

    if (empty($nama_menu) || empty($kategori) || empty($harga) || empty($stok)) {
        $error_tambah = "Semua kolom wajib diisi!";
    } elseif (!is_numeric($harga) || $harga <= 0) {
        $error_tambah = "Harga harus berupa angka positif!";
    } else {
        $foto_nama = null;
        $upload_ok = true;

        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['foto']['tmp_name'];
            $file_name = $_FILES['foto']['name'];
            $file_size = $_FILES['foto']['size'];
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($ext, $allowed_ext)) {
                $error_tambah = "Format gambar tidak didukung! Gunakan JPG, JPEG, PNG, atau WEBP.";
                $upload_ok = false;
            } elseif ($file_size > 2 * 1024 * 1024) {
                $error_tambah = "Ukuran gambar terlalu besar! Maksimal 2MB.";
                $upload_ok = false;
            } else {
                $foto_nama = time() . '_' . uniqid() . '.' . $ext;
                $dest_path = 'uploads/' . $foto_nama;
                if (!move_uploaded_file($file_tmp, $dest_path)) {
                    $error_tambah = "Gagal mengunggah foto menu.";
                    $upload_ok = false;
                }
            }
        }

        if ($upload_ok) {
            $foto_val = $foto_nama ? "'$foto_nama'" : "NULL";
            
            $nama_menu_db = mysqli_real_escape_string($koneksi, $nama_menu);
            $kategori_db = mysqli_real_escape_string($koneksi, $kategori);
            $harga_db = mysqli_real_escape_string($koneksi, $harga);
            $deskripsi_db = mysqli_real_escape_string($koneksi, $deskripsi);
            $stok_db = mysqli_real_escape_string($koneksi, $stok);
            
            $sql = "INSERT INTO menu (nama_menu, kategori, harga, deskripsi, stok, foto)
                    VALUES ('$nama_menu_db', '$kategori_db', '$harga_db', '$deskripsi_db', '$stok_db', $foto_val)";

            if (mysqli_query($koneksi, $sql)) {
                header("Location: menu.php?pesan=tambah");
                exit();
            } else {
                if ($foto_nama) { unlink('uploads/' . $foto_nama); }
                $error_tambah = "Gagal menyimpan menu ke database.";
            }
        }
    }
}

// ============================================
// PROSES UBAH STOK
// ============================================
if (isset($_GET["ubah_stok"])) {
    $id        = $_GET["ubah_stok"];
    $stok_baru = $_GET["stok"];

    if (in_array($stok_baru, ["Tersedia", "Habis"])) {
        mysqli_query($koneksi, "UPDATE menu SET stok='$stok_baru' WHERE id='$id'");
    }
    header("Location: menu.php?pesan=stok");
    exit();
}

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
$hasil = mysqli_query($koneksi, "SELECT * FROM menu $filter ORDER BY kategori, nama_menu");
$pesan = $_GET["pesan"] ?? "";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Menu - Noir Cafe</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/dark-mode.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .badge-tersedia {
            background: #e8f5e9; color: #2e7d32;
            padding: 4px 12px; border-radius: 20px;
            font-size: 12px; font-weight: 600;
        }
        .badge-habis {
            background: #fdecea; color: #c62828;
            padding: 4px 12px; border-radius: 20px;
            font-size: 12px; font-weight: 600;
        }
        .tombol-stok-tersedia {
            background: #e8f5e9; color: #2e7d32;
            border: 1px solid #a5d6a7; padding: 4px 10px;
            border-radius: 6px; font-size: 11px; font-weight: 600;
            cursor: pointer; text-decoration: none; display: inline-block;
        }
        .tombol-stok-habis {
            background: #fdecea; color: #c62828;
            border: 1px solid #ef9a9a; padding: 4px 10px;
            border-radius: 6px; font-size: 11px; font-weight: 600;
            cursor: pointer; text-decoration: none; display: inline-block;
        }
        .baris-habis td { opacity: 0.5; }
        .baris-habis td:last-child, .baris-habis td:nth-last-child(2) { opacity: 1; }

        /* CSS MODAL FIX POP-UP */
        .modal-overlay {
            position: fixed !important;
            top: 0 !important; left: 0 !important;
            width: 100% !important; height: 100% !important;
            background: rgba(0, 0, 0, 0.6) !important;
            backdrop-filter: blur(5px) !important;
            display: flex !important;
            align-items: center !important; justify-content: center !important;
            z-index: 99999 !important;
            opacity: 0 !important; visibility: hidden !important;
            transition: opacity 0.3s ease, visibility 0.3s ease !important;
        }
        .modal-overlay.aktif { opacity: 1 !important; visibility: visible !important; }
        .modal-kotak {
            background: white !important; width: 95% !important; max-width: 500px !important;
            border-radius: 14px !important; box-shadow: 0 15px 45px rgba(0, 0, 0, 0.3) !important;
            overflow: hidden !important; transform: translateY(-30px) !important;
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) !important;
            display: flex !important; flex-direction: column !important;
        }
        .modal-overlay.aktif .modal-kotak { transform: translateY(0) !important; }
        .modal-overlay .modal-header {
            background: #6f4e37 !important; color: white !important; padding: 16px 20px !important;
            display: flex !important; justify-content: space-between !important; align-items: center !important;
            width: 100% !important; flex-direction: row !important;
        }
        .modal-overlay .modal-header h3 { margin: 0 !important; color: white !important; font-size: 18px; }
        .modal-close {
            background: none !important; border: none !important; color: white !important;
            font-size: 24px !important; cursor: pointer !important; display: flex !important;
            align-items: center !important; justify-content: center !important; width: 30px !important; height: 30px !important;
        }
        .modal-body { padding: 20px 24px 24px !important; max-height: 75vh !important; overflow-y: auto !important; text-align: left !important; }
        
        /* Dark mode overrides for modal inside menu.php */
        html.dark-mode .modal-kotak {
            background: #121110 !important;
            color: #e8e2d9 !important;
            box-shadow: 0 15px 45px rgba(0, 0, 0, 0.7) !important;
        }
        html.dark-mode .modal-overlay .modal-header {
            background: #5c3d2e !important;
            border-bottom: 1px solid #38322c !important;
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="nav-brand">☕ Noir Cafe</div>
    <ul class="nav-menu">
        <li><a href="dashboard.php">🏠 Dashboard</a></li>
        <li><a href="menu.php" class="aktif">🍽️ Daftar Menu</a></li>
        <li><a href="pesanan.php">📋 Kelola Pesanan</a></li>
        <li><a href="index.php" target="_blank">🌐 Lihat Web</a></li>
        <li><a href="logout.php">🚪 Logout</a></li>
    </ul>
    <div class="nav-auth">
        <!-- Tombol dark mode disuntikkan di sini oleh dark-mode.js -->
    </div>
</nav>

<div class="konten">
    <div class="header-halaman">
        <h2>🍽️ Daftar Menu Cafe</h2>
        <button type="button" class="tombol-utama" onclick="bukaModal()">+ Tambah Menu</button>
    </div>

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
        <a href="menu.php?cari=<?= urlencode($cari) ?>" class="tombol-filter <?= empty($kategori_filter) ? 'aktif' : '' ?>">Semua</a>
        <a href="menu.php?kategori=Makanan&cari=<?= urlencode($cari) ?>" class="tombol-filter <?= $kategori_filter == 'Makanan' ? 'aktif' : '' ?>">🍛 Makanan</a>
        <a href="menu.php?kategori=Minuman&cari=<?= urlencode($cari) ?>" class="tombol-filter <?= $kategori_filter == 'Minuman' ? 'aktif' : '' ?>">🥤 Minuman</a>
        <a href="menu.php?kategori=Snack&cari=<?= urlencode($cari) ?>" class="tombol-filter <?= $kategori_filter == 'Snack' ? 'aktif' : '' ?>">🍟 Snack</a>
    </div>

    <div class="kotak">
        <table class="tabel-data">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Menu</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Status Stok</th>
                    <th>Ubah Stok</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                while ($baris = mysqli_fetch_assoc($hasil)) :
                    $class_baris = ($baris["stok"] == "Habis") ? "baris-habis" : "";
                ?>
                <tr class="<?= $class_baris ?>">
                    <td><?= $no++ ?></td>
                    <td><strong><?= $baris["nama_menu"] ?></strong></td>
                    <td><span class="badge badge-<?= strtolower($baris["kategori"]) ?>"><?= $baris["kategori"] ?></span></td>
                    <td>Rp <?= number_format($baris["harga"], 0, ',', '.') ?></td>
                    <td><?= ($baris["stok"] == "Tersedia") ? '<span class="badge-tersedia">✅ Tersedia</span>' : '<span class="badge-habis">❌ Habis</span>' ?></td>
                    <td>
                        <a href="menu.php?ubah_stok=<?= $baris["id"] ?>&stok=<?= ($baris["stok"] == "Tersedia") ? 'Habis' : 'Tersedia' ?>"
                           class="<?= ($baris["stok"] == "Tersedia") ? 'tombol-stok-habis' : 'tombol-stok-tersedia' ?> tombol-konfirmasi-stok"
                           data-pesan="Ubah status menu ini?">
                           Tandai <?= ($baris["stok"] == "Tersedia") ? 'Habis' : 'Tersedia' ?>
                        </a>
                    </td>
                    <td class="kolom-aksi">
                        <a href="edit_menu.php?id=<?= $baris["id"] ?>" class="tombol-edit">✏️ Edit</a>
                        <a href="hapus_menu.php?id=<?= $baris["id"] ?>" class="tombol-hapus tombol-konfirmasi-hapus">🗑️ Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL BOX -->
<div class="modal-overlay" id="modalTambahMenu">
    <div class="modal-kotak">
        <div class="modal-header">
            <h3>➕ Tambah Menu Baru</h3>
            <button type="button" class="modal-close" onclick="tutupModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="submit_tambah_menu" value="1">
                <div class="grup-form"><label>Nama Menu *</label><input type="text" name="nama_menu" required></div>
                <div class="grup-form">
                    <label>Kategori *</label>
                    <select name="kategori" required>
                        <option value="">-- Pilih Kategori --</option>
                        <option value="Makanan">🍛 Makanan</option>
                        <option value="Minuman">🥤 Minuman</option>
                        <option value="Snack">🍟 Snack</option>
                    </select>
                </div>
                <div class="grup-form"><label>Harga *</label><input type="number" name="harga" required></div>
                <div class="grup-form">
                    <label>Status Stok *</label>
                    <select name="stok" required>
                        <option value="Tersedia">✅ Tersedia</option>
                        <option value="Habis">❌ Habis</option>
                    </select>
                </div>
                <div class="grup-form"><label>Foto Menu</label><input type="file" name="foto" accept="image/*"></div>
                <div class="grup-form"><label>Deskripsi</label><textarea name="deskripsi" rows="3"></textarea></div>
                <div class="tombol-grup">
                    <button type="submit" class="tombol-utama">💾 Simpan Menu</button>
                    <button type="button" class="tombol-batal" onclick="tutupModal()">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function bukaModal() { document.getElementById('modalTambahMenu').classList.add('aktif'); }
function tutupModal() { document.getElementById('modalTambahMenu').classList.remove('aktif'); }

document.addEventListener("DOMContentLoaded", function() {
    <?php if ($pesan == "tambah") : ?>
        Swal.fire({ title: "Berhasil!", text: "Menu baru berhasil ditambahkan!", icon: "success", confirmButtonColor: "#6f4e37" });
    <?php elseif ($pesan == "stok") : ?>
        Swal.fire({ title: "Berhasil!", text: "Status stok berhasil diubah!", icon: "success", confirmButtonColor: "#6f4e37" });
    <?php endif; ?>
});
</script>
</body>
</html>