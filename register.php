<?php
session_start();          
include "config.php";    
include "mail_helper.php"; // Helper untuk mengirim email OTP

$pesan = "";  // Variabel untuk menyimpan pesan error/sukses

// Cek apakah form sudah dikirim (tombol Register diklik)
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Ambil data dari form dan bersihkan dari karakter berbahaya
    $nama     = trim($_POST["nama"]);
    $email    = trim($_POST["email"]);
    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    $konfirm  = $_POST["konfirm_password"];

    // === VALIDASI FORM ===
    if (empty($nama) || empty($email) || empty($username) || empty($password)) {
        $pesan = "error|Semua kolom wajib diisi!";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $pesan = "error|Format email tidak valid!";

    } elseif ($password !== $konfirm) {
        $pesan = "error|Password dan konfirmasi password tidak sama!";

    } elseif (strlen($password) < 6) {
        $pesan = "error|Password minimal 6 karakter!";

    } else {
        // Cek apakah username sudah dipakai orang lain
        $cek_username = mysqli_query($koneksi, "SELECT id FROM users WHERE username='$username'");
        // Cek apakah email sudah dipakai orang lain
        $cek_email = mysqli_query($koneksi, "SELECT id FROM users WHERE email='$email'");

        if (mysqli_num_rows($cek_username) > 0) {
            $pesan = "error|Username '$username' sudah digunakan! Pilih username lain.";
        } elseif (mysqli_num_rows($cek_email) > 0) {
            $pesan = "error|Email '$email' sudah terdaftar! Gunakan email lain.";
        } else {
            // Belum langsung simpan ke database. Data disimpan sementara di SESSION,
            // lalu kirim OTP ke email untuk verifikasi dulu sebelum akun benar-benar dibuat.
            $otp = strval(rand(100000, 999999));
            $expiry = time() + 300; // 5 menit

            $_SESSION["pending_register"] = [
                "nama"     => $nama,
                "email"    => $email,
                "username" => $username,
                "password" => password_hash($password, PASSWORD_DEFAULT),
                "otp_code" => $otp,
                "otp_expiry" => $expiry,
            ];

            $kirim = sendOTP($email, $otp, 'register');

            if ($kirim['success']) {
                header("Location: verify_register.php");
                exit();
            } else {
                $pesan = "error|" . $kirim['message'];
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
    <title>Register - Cafe Kiswah</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/dark-mode.js" defer></script>
</head>
<body class="halaman-auth">

<div class="kotak-auth">
    <a href="index.php" style="text-decoration: none; color: inherit; display: inline-block; margin-bottom: 5px;">
        <div class="logo-auth" style="margin-bottom: 0;">☕</div>
        <h1 class="judul-auth" style="margin-top: 5px; margin-bottom: 0;">Noir Cafe</h1>
    </a>
    <p class="sub-auth" style="margin-top: 5px;">Buat akun baru</p>

    <?php
    // Tampilkan pesan error atau sukses jika ada
    if (!empty($pesan)) {
        $bagian = explode("|", $pesan);
        $tipe   = $bagian[0];  // "error" atau "sukses"
        $isi    = $bagian[1];  // isi pesannya
        echo "<div class='pesan $tipe'>$isi</div>";
    }
    ?>

    <form method="POST" action="">
        <div class="grup-form">
            <label>Nama Lengkap</label>
            <input type="text" name="nama" placeholder="Masukkan nama lengkap" required>
        </div>

        <div class="grup-form">
            <label>Alamat Email</label>
            <input type="email" name="email" placeholder="Masukkan alamat email" required>
        </div>

        <div class="grup-form">
            <label>Username</label>
            <input type="text" name="username" placeholder="Buat username unik" required>
        </div>

        <div class="grup-form">
            <label>Password</label>
            <input type="password" name="password" placeholder="Minimal 6 karakter" required>
        </div>

        <div class="grup-form">
            <label>Konfirmasi Password</label>
            <input type="password" name="konfirm_password" placeholder="Ulangi password" required>
        </div>

        <button type="submit" class="tombol-utama">Daftar Sekarang</button>
    </form>

    <p class="link-auth">Sudah punya akun? <a href="login.php">Login di sini</a></p>
</div>

</body>
</html>