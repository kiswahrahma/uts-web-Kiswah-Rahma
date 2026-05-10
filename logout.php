<?php
// ============================================
// FILE: logout.php
// Fungsi: Menghapus session dan keluar login
// ============================================

session_start();   // Mulai session yang ada
session_destroy(); // Hapus semua data session

// Kembali ke halaman login
header("Location: login.php");
exit();
?>