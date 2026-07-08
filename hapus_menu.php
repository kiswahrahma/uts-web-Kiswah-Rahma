<?php

session_start();
include "config.php";
include "auth.php";
require_admin();

// Ambil ID dari URL (?id=5)
$id = $_GET["id"] ?? 0;

// Pastikan ID valid (angka dan lebih dari 0)
if ($id > 0) {
    // Cek apakah menu sudah pernah dipesan (ada di pesanan_detail)
    $cek_pesanan = mysqli_query($koneksi, "SELECT id FROM pesanan_detail WHERE menu_id='$id' LIMIT 1");
    if (mysqli_num_rows($cek_pesanan) > 0) {
        // Jika sudah ada di pesanan, gagalkan penghapusan demi menjaga integritas data riwayat pesanan
        header("Location: menu.php?pesan=gagal_hapus");
        exit();
    } else {
        // Hapus menu dengan ID tersebut dari database (DELETE)
        mysqli_query($koneksi, "DELETE FROM menu WHERE id='$id'");
        header("Location: menu.php?pesan=hapus");
        exit();
    }
}

// Kembali ke halaman menu jika ID tidak valid
header("Location: menu.php");
exit();
?>