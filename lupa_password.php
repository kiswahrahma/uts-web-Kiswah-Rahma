<?php
// ============================================
// FILE: lupa_password.php
// Fungsi: Reset password dengan isi username + password baru
// ============================================

session_start();
include "config.php"; // ganti jadi include "config.php"; kalau file kamu config.php

$pesan = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username     = trim($_POST["username"]);
    $password_baru = $_POST["password_baru"];
    $konfirm      = $_POST["konfirm_password"];

    // === VALIDASI ===
    if (empty($username) || empty($password_baru) || empty($konfirm)) {
        $pesan = "error|Semua kolom wajib diisi!";

    } elseif ($password_baru !== $konfirm) {
        $pesan = "error|Password baru dan konfirmasi tidak sama!";

    } elseif (strlen($password_baru) < 6) {
        $pesan = "error|Password minimal 6 karakter!";

    } else {
        // Cek apakah username ada di database
        $cek = mysqli_query($koneksi, "SELECT id FROM users WHERE username='$username'");

        if (mysqli_num_rows($cek) == 0) {
            $pesan = "error|Username tidak ditemukan!";
        } else {
            // Enkripsi password baru
            $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);

            // Update password di database
            $sql = "UPDATE users SET password='$password_hash' WHERE username='$username'";

            if (mysqli_query($koneksi, $sql)) {
                $pesan = "sukses|Password berhasil direset! Silakan login dengan password baru.";
            } else {
                $pesan = "error|Gagal mereset password. Coba lagi.";
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
    <title>Lupa Password - Cafe Kiswah</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="halaman-auth">

<div class="kotak-auth">
    <a href="index.php" style="text-decoration: none; color: inherit; display: inline-block; margin-bottom: 5px;">
        <div class="logo-auth" style="margin-bottom: 0;">🔑</div>
        <h1 class="judul-auth" style="margin-top: 5px; margin-bottom: 0;">Lupa Password</h1>
    </a>
    <p class="sub-auth" style="margin-top: 5px;">Reset password akun kamu</p>

    <?php
    if (!empty($pesan)) {
        $bagian = explode("|", $pesan);
        echo "<div class='pesan {$bagian[0]}'>{$bagian[1]}</div>";
    }
    ?>

    <?php if (strpos($pesan, "sukses") !== false) : ?>
        <!-- Kalau sukses, tampilkan tombol ke login -->
        <a href="login.php" class="tombol-utama" style="display:block; text-align:center; margin-top:10px;">
            Ke Halaman Login
        </a>

    <?php else : ?>
        <!-- Form reset password -->
        <form method="POST" action="">

            <div class="grup-form">
                <label>Username</label>
                <input type="text"
                       name="username"
                       placeholder="Masukkan username kamu"
                       value="<?= $_POST['username'] ?? '' ?>"
                       required>
            </div>

            <div class="grup-form">
                <label>Password Baru</label>
                <input type="password"
                       name="password_baru"
                       placeholder="Minimal 6 karakter"
                       required>
            </div>

            <div class="grup-form">
                <label>Konfirmasi Password Baru</label>
                <input type="password"
                       name="konfirm_password"
                       placeholder="Ulangi password baru"
                       required>
            </div>

            <button type="submit" class="tombol-utama">Reset Password</button>
        </form>

    <?php endif; ?>

    <p class="link-auth">Ingat password? <a href="login.php">Login di sini</a></p>
</div>

</body>
</html>