<?php
// ============================================
// FILE: pesanan.php
// ============================================

session_start();
include "config.php";
include "auth.php";
require_admin();

// Filter Status & Pencarian
$status_filter = $_GET["status"] ?? "";
$cari = $_GET["cari"] ?? "";

$conditions = [];
if (!empty($status_filter)) {
    $status_escaped = mysqli_real_escape_string($koneksi, $status_filter);
    $conditions[] = "pesanan.status='$status_escaped'";
}
if (!empty($cari)) {
    $cari_escaped = mysqli_real_escape_string($koneksi, $cari);
    $conditions[] = "(users.nama LIKE '%$cari_escaped%' OR pesanan.id = '$cari_escaped')";
}

$filter = "";
if (count($conditions) > 0) {
    $filter = "WHERE " . implode(" AND ", $conditions);
}

// Ambil semua pesanan dan join dengan tabel users untuk mendapatkan nama pemesan
$query = "SELECT pesanan.*, users.nama AS nama_user 
          FROM pesanan 
          JOIN users ON pesanan.user_id = users.id 
          $filter 
          ORDER BY pesanan.tanggal DESC";
$hasil = mysqli_query($koneksi, $query);

$pesan = $_GET["pesan"] ?? "";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - Noir Cafe</title>
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
        .tombol-detail {
            background: #546e7a;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            transition: background 0.3s;
            display: inline-block;
        }
        .tombol-detail:hover {
            background: #37474f;
        }
        
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
        
        /* Dark mode overrides for modal inside pesanan.php */
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
        <li><a href="menu.php">🍽️ Daftar Menu</a></li>
        <li><a href="pesanan.php" class="aktif">📋 Kelola Pesanan</a></li>
        <li><a href="index.php" target="_blank">🌐 Lihat Web</a></li>
        <li><a href="logout.php">🚪 Logout</a></li>
    </ul>
    <div class="nav-auth">
        <!-- Tombol dark mode disuntikkan di sini oleh dark-mode.js -->
    </div>
</nav>

<div class="konten">

    <div class="header-halaman">
        <h2>📋 Daftar Pesanan Cafe</h2>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="export_pesanan_excel.php" class="tombol-edit" style="background: #2e7d32; padding: 10px 15px; font-size: 14px; display: inline-flex; align-items: center; gap: 4px;">📥 Ekspor Semua (Excel)</a>
            <button onclick="bukaModalImport()" class="tombol-utama" style="background: #ef6c00; width: auto; padding: 10px 15px; margin: 0; font-size: 14px; display: inline-flex; align-items: center; gap: 4px;">📤 Impor Excel</button>
            <a href="tambah_pesanan.php" class="tombol-utama" style="width: auto; padding: 10px 15px; margin: 0; font-size: 14px; display: inline-flex; align-items: center; gap: 4px;">+ Tambah Pesanan</a>
        </div>
    </div>

    <?php if ($pesan == "tambah") : ?>
        <div class="pesan sukses">✅ Pesanan baru berhasil ditambahkan!</div>
    <?php elseif ($pesan == "edit") : ?>
        <div class="pesan sukses">✅ Pesanan berhasil diperbarui!</div>
    <?php elseif ($pesan == "hapus") : ?>
        <div class="pesan error">🗑️ Pesanan berhasil dihapus!</div>
    <?php elseif ($pesan == "import_sukses") : ?>
        <div class="pesan sukses">✅ Data pesanan berhasil diimpor dari Excel!</div>
    <?php elseif ($pesan == "import_gagal") : ?>
        <div class="pesan error">❌ Gagal mengimpor pesanan: <?= htmlspecialchars($_GET["error"] ?? "") ?></div>
    <?php endif; ?>

    <!-- Form Pencarian -->
    <form method="GET" action="" class="form-cari-wadah" style="margin-bottom: 20px; display: flex; gap: 10px; align-items: center;">
        <?php if (!empty($status_filter)) : ?>
            <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>">
        <?php endif; ?>
        <input type="text" name="cari" placeholder="🔍 Cari nama pelanggan atau ID pesanan..." value="<?= htmlspecialchars($cari) ?>" 
               style="flex: 1; padding: 10px 15px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;">
        <button type="submit" class="tombol-utama" style="width: auto; padding: 10px 20px; margin: 0;">Cari</button>
        <?php if (!empty($cari)) : ?>
            <a href="pesanan.php?status=<?= urlencode($status_filter) ?>" class="tombol-batal" style="padding: 10px 20px; text-decoration: none; line-height: 1.5;">Reset</a>
        <?php endif; ?>
    </form>

    <div class="filter-kategori">
        <a href="pesanan.php?cari=<?= urlencode($cari) ?>" class="tombol-filter <?= empty($status_filter) ? 'aktif' : '' ?>">Semua</a>
        <a href="pesanan.php?status=Pending&cari=<?= urlencode($cari) ?>" class="tombol-filter <?= $status_filter == 'Pending' ? 'aktif' : '' ?>">⏳ Pending</a>
        <a href="pesanan.php?status=Diproses&cari=<?= urlencode($cari) ?>" class="tombol-filter <?= $status_filter == 'Diproses' ? 'aktif' : '' ?>">🔄 Diproses</a>
        <a href="pesanan.php?status=Selesai&cari=<?= urlencode($cari) ?>" class="tombol-filter <?= $status_filter == 'Selesai' ? 'aktif' : '' ?>">✅ Selesai</a>
        <a href="pesanan.php?status=Dibatalkan&cari=<?= urlencode($cari) ?>" class="tombol-filter <?= $status_filter == 'Dibatalkan' ? 'aktif' : '' ?>">❌ Dibatalkan</a>
    </div>

    <div class="kotak">
        <table class="tabel-data">
            <thead>
                <tr>
                    <th>No</th>
                    <th>ID Pesanan</th>
                    <th>Tanggal & Waktu</th>
                    <th>Pelanggan</th>
                    <th>Status</th>
                    <th>Metode Bayar</th>
                    <th>Total Harga</th>
                    <th>Catatan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                while ($baris = mysqli_fetch_assoc($hasil)) :
                    $status_class = "badge-" . strtolower($baris["status"]);
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><strong>#<?= $baris["id"] ?></strong></td>
                    <td><?= date('d-m-Y H:i', strtotime($baris["tanggal"])) ?></td>
                    <td><?= htmlspecialchars($baris["nama_user"]) ?></td>
                    <td>
                        <span class="<?= $status_class ?>">
                            <?php
                            if ($baris["status"] == 'Pending') echo "⏳ Pending";
                            elseif ($baris["status"] == 'Diproses') echo "🔄 Diproses";
                            elseif ($baris["status"] == 'Selesai') echo "✅ Selesai";
                            elseif ($baris["status"] == 'Dibatalkan') echo "❌ Dibatalkan";
                            else echo $baris["status"];
                            ?>
                        </span>
                    </td>
                    <td>
                        <?php
                        $mp = $baris['metode_pembayaran'] ?? 'Tunai';
                        $mp_icon = '';
                        if ($mp === 'Tunai') $mp_icon = '💵';
                        elseif ($mp === 'Transfer Bank') $mp_icon = '🏦';
                        elseif ($mp === 'QRIS') $mp_icon = '📱';
                        elseif ($mp === 'Dompet Digital') $mp_icon = '👛';
                        ?>
                        <span style="font-size:12px; background:#fdf3e3; color:#6f4e37; padding:3px 10px; border-radius:20px; font-weight:600;">
                            <?= $mp_icon ?> <?= htmlspecialchars($mp) ?>
                        </span>
                    </td>
                    <td><strong>Rp <?= number_format($baris["total_harga"], 0, ',', '.') ?></strong></td>
                    <td><?= htmlspecialchars($baris["catatan"] ?? '-') ?></td>
                    <td class="kolom-aksi">
                        <a href="detail_pesanan.php?id=<?= $baris["id"] ?>" class="tombol-detail">👁️ Detail</a>
                        <a href="edit_pesanan.php?id=<?= $baris["id"] ?>" class="tombol-edit">✏️ Edit</a>
                        <a href="hapus_pesanan.php?id=<?= $baris["id"] ?>" 
                           class="tombol-hapus"
                           onclick="return confirm('Yakin ingin menghapus pesanan ini beserta seluruh detailnya?')">
                           🗑️ Hapus
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>

                <?php if (mysqli_num_rows($hasil) == 0) : ?>
                <tr>
                    <td colspan="9" style="text-align:center; color:#999; padding:30px;">
                       Belum ada data pesanan. <a href="tambah_pesanan.php">Tambah pesanan sekarang!</a>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<!-- MODAL IMPORT EXCEL -->
<div class="modal-overlay" id="modalImportExcel">
    <div class="modal-kotak">
        <div class="modal-header">
            <h3>📤 Impor Pesanan dari Excel</h3>
            <button type="button" class="modal-close" onclick="tutupModalImport()">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" action="import_pesanan_excel.php" enctype="multipart/form-data">
                <div class="grup-form">
                    <label>Pilih File Excel (.xlsx) <span class="wajib">*</span></label>
                    <input type="file" name="file_excel" accept=".xlsx" required>
                </div>
                <p style="font-size: 12px; color: #666; margin-bottom: 20px; line-height: 1.4;">
                    * Pastikan struktur kolom file Excel Anda sama dengan format hasil ekspor:<br>
                    <strong>ID Pesanan | Username Pelanggan | Tanggal | Status | Catatan | Nama Menu | Jumlah | Harga Satuan | Subtotal</strong>
                </p>
                <div class="tombol-grup">
                    <button type="submit" class="tombol-utama">💾 Mulai Impor</button>
                    <button type="button" class="tombol-batal" onclick="tutupModalImport()">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function bukaModalImport() { document.getElementById('modalImportExcel').classList.add('aktif'); }
function tutupModalImport() { document.getElementById('modalImportExcel').classList.remove('aktif'); }
</script>
</body>
</html>
