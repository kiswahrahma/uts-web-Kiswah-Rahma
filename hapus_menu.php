<?php
// ============================================
// FILE: hapus_menu.php
// Fungsi: Menghapus menu dari database (DELETE)
// ============================================

session_start();
include "koneksi.php";

// Proteksi: harus login dulu
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Ambil ID dari URL (?id=5)
$id = $_GET["id"] ?? 0;

// Pastikan ID valid (angka dan lebih dari 0)
if ($id > 0) {
    // Hapus menu dengan ID tersebut dari database (DELETE)
    mysqli_query($koneksi, "DELETE FROM menu WHERE id='$id'");
}

// Kembali ke halaman menu dengan pesan
header("Location: menu.php?pesan=hapus");
exit();
?>