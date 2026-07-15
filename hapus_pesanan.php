<?php
// ============================================
// FILE: hapus_pesanan.php
// ============================================

session_start();
include "config.php";
<<<<<<< HEAD
include "auth.php";
require_admin();
=======

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
>>>>>>> a5ebe0b1735c3f14f69185f4b1a313b582a1a213

$id = $_GET["id"] ?? 0;
$id = mysqli_real_escape_string($koneksi, $id);

if ($id > 0) {
    // Mulai penghapusan
    // 1. Hapus data di pesanan_detail dulu karena ada foreign key reference ke pesanan
    mysqli_query($koneksi, "DELETE FROM pesanan_detail WHERE pesanan_id='$id'");

    // 2. Hapus data di pesanan
    mysqli_query($koneksi, "DELETE FROM pesanan WHERE id='$id'");

    header("Location: pesanan.php?pesan=hapus");
    exit();
} else {
    header("Location: pesanan.php");
    exit();
}
?>
