<?php
session_start();       // Mulai session
include "config.php"; // Sambungkan ke database
include "mail_helper.php"; // Helper untuk mengirim email OTP

// Jika sudah login, langsung ke halaman sesuai role
if (isset($_SESSION["user_id"])) {
    header("Location: " . (($_SESSION["role"] ?? "") === "admin" ? "dashboard.php" : "index.php"));
    exit();
}

$pesan = "";
if (!empty($_SESSION["register_sukses"])) {
    $pesan = "sukses|Akun berhasil dibuat dan diverifikasi! Silakan login.";
    unset($_SESSION["register_sukses"]);
}
$redirect = $_GET["redirect"] ?? ($_POST["redirect"] ?? "");

// Cek apakah form login sudah dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // === VALIDASI FORM ===
    if (empty($email) || empty($password)) {
        $pesan = "error|Email dan password wajib diisi!";
    } else {
        // Cari user dengan email tersebut di database
        $sql  = "SELECT * FROM users WHERE email='$email'";
        $hasil = mysqli_query($koneksi, $sql);

        if (mysqli_num_rows($hasil) == 1) {
            // User ditemukan, sekarang cek passwordnya
            $user = mysqli_fetch_assoc($hasil);

            if (password_verify($password, $user["password"])) {
                // Password cocok! Langsung login tanpa OTP
                $_SESSION["user_id"]   = $user["id"];
                $_SESSION["user_nama"] = $user["nama"];
                $_SESSION["username"]  = $user["username"];
                $_SESSION["role"]      = $user["role"] ?? "pelanggan";

                // Arahkan ke halaman tujuan, atau dashboard (jika admin), atau index (jika pelanggan)
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
            $pesan = "error|Email tidak ditemukan!";
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
    <script src="js/dark-mode.js" defer></script>
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
            <label>Alamat Email</label>
            <input type="email" name="email" placeholder="Masukkan alamat email" required>
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