<?php
// ============================================
// FILE: login.php
// Fungsi: Halaman login pengguna
// ============================================

session_start();       // Mulai session
include "config.php"; // Sambungkan ke database

// Jika sudah login, langsung ke dashboard
if (isset($_SESSION["user_id"])) {
    header("Location: dashboard.php");
    exit();
}

$pesan = "";

// Cek apakah form login sudah dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    // === VALIDASI FORM ===
    if (empty($username) || empty($password)) {
        $pesan = "error|Username dan password wajib diisi!";
    } else {
        // Cari user dengan username tersebut di database
        $sql  = "SELECT * FROM users WHERE username='$username'";
        $hasil = mysqli_query($koneksi, $sql);

        if (mysqli_num_rows($hasil) == 1) {
            // Username ditemukan, sekarang cek passwordnya
            $user = mysqli_fetch_assoc($hasil);

            if (password_verify($password, $user["password"])) {
                // Password cocok! Simpan info user ke SESSION
                $_SESSION["user_id"]   = $user["id"];
                $_SESSION["user_nama"] = $user["nama"];
                $_SESSION["username"]  = $user["username"];

                // Arahkan ke dashboard
                header("Location: dashboard.php");
                exit();
            } else {
                $pesan = "error|Password salah! Coba lagi.";
            }
        } else {
            $pesan = "error|Username tidak ditemukan!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Cafe Kiswah</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="halaman-auth">

<div class="kotak-auth">
    <div class="logo-auth">☕</div>
    <h1 class="judul-auth">Cafe Kiswah</h1>
    <p class="sub-auth">Masuk ke akun kamu</p>

    <?php
    if (!empty($pesan)) {
        $bagian = explode("|", $pesan);
        echo "<div class='pesan {$bagian[0]}'>{$bagian[1]}</div>";
    }
    ?>

    <form method="POST" action="">
        <div class="grup-form">
            <label>Username</label>
            <input type="text" name="username" placeholder="Masukkan username" required>
        </div>

        <div class="grup-form">
            <label>Password</label>
            <input type="password" name="password" placeholder="Masukkan password" required>
        </div>

        <button type="submit" class="tombol-utama">Login</button>
    </form>

    <p class="link-auth">Belum punya akun? <a href="register.php">Daftar di sini</a></p>

    
</div>

</body>
</html>