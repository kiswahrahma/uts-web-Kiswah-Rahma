<?php
session_start();       // Mulai session
include "config.php"; // Sambungkan ke database

// Jika sudah login, langsung ke halaman sesuai role
if (isset($_SESSION["user_id"])) {
    header("Location: " . (($_SESSION["role"] ?? "") === "admin" ? "dashboard.php" : "index.php"));
    exit();
}

$pesan = "";
$redirect = $_GET["redirect"] ?? ($_POST["redirect"] ?? "");

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
                $_SESSION["role"]      = $user["role"] ?? "pelanggan";

                // Kalau ada halaman tujuan sebelumnya (misal mau pesan), balik ke situ.
                // Kalau tidak, arahkan sesuai role: admin -> dashboard, pelanggan -> beranda.
                if (!empty($redirect)) {
                    header("Location: " . $redirect);
                } elseif ($_SESSION["role"] === "admin") {
                    header("Location: dashboard.php");
                } else {
                    header("Location: index.php");
                }
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
    <title>Login - Noir Cafe</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="halaman-auth">

<div class="kotak-auth">
    <a href="index.php" style="text-decoration: none; color: inherit; display: inline-block; margin-bottom: 5px;">
        <div class="logo-auth" style="margin-bottom: 0;">☕</div>
        <h1 class="judul-auth" style="margin-top: 5px; margin-bottom: 0;">Noir Cafe</h1>
    </a>
    <p class="sub-auth" style="margin-top: 5px;">Masuk ke akun kamu</p>

    <?php
    if (!empty($pesan)) {
        $bagian = explode("|", $pesan);
        echo "<div class='pesan {$bagian[0]}'>{$bagian[1]}</div>";
    }
    ?>

    <form method="POST" action="">
        <?php if (!empty($redirect)) : ?>
            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
        <?php endif; ?>
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
    <p class="link-auth">Lupa password? <a href="lupa_password.php">Reset di sini</a></p>
    
</div>

</body>
</html>