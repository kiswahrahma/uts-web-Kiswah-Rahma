<?php
session_start();          
include "config.php";    

$pesan = "";  // Variabel untuk menyimpan pesan error/sukses

// Cek apakah form sudah dikirim (tombol Register diklik)
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Ambil data dari form dan bersihkan dari karakter berbahaya
    $nama     = trim($_POST["nama"]);
    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    $konfirm  = $_POST["konfirm_password"];

    // === VALIDASI FORM ===
    if (empty($nama) || empty($username) || empty($password)) {
        $pesan = "error|Semua kolom wajib diisi!";

    } elseif ($password !== $konfirm) {
        $pesan = "error|Password dan konfirmasi password tidak sama!";

    } elseif (strlen($password) < 6) {
        $pesan = "error|Password minimal 6 karakter!";

    } else {
        // Cek apakah username sudah dipakai orang lain
        $cek = mysqli_query($koneksi, "SELECT id FROM users WHERE username='$username'");

        if (mysqli_num_rows($cek) > 0) {
            $pesan = "error|Username '$username' sudah digunakan! Pilih username lain.";
        } else {
            // Enkripsi password agar tidak tersimpan polos di database
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

<<<<<<< HEAD
            // Simpan user baru ke database (akun baru selalu jadi pelanggan)
            $sql = "INSERT INTO users (nama, username, password, role) VALUES ('$nama', '$username', '$password_hash', 'pelanggan')";
=======
            // Simpan user baru ke database
            $sql = "INSERT INTO users (nama, username, password) VALUES ('$nama', '$username', '$password_hash')";
>>>>>>> a5ebe0b1735c3f14f69185f4b1a313b582a1a213

            if (mysqli_query($koneksi, $sql)) {
                $pesan = "sukses|Akun berhasil dibuat! Silakan login.";
            } else {
                $pesan = "error|Terjadi kesalahan saat mendaftar. Coba lagi.";
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
</head>
<body class="halaman-auth">

<div class="kotak-auth">
<<<<<<< HEAD
    <a href="index.php" style="text-decoration: none; color: inherit; display: inline-block; margin-bottom: 5px;">
        <div class="logo-auth" style="margin-bottom: 0;">☕</div>
        <h1 class="judul-auth" style="margin-top: 5px; margin-bottom: 0;">Noir Cafe</h1>
    </a>
    <p class="sub-auth" style="margin-top: 5px;">Buat akun baru</p>
=======
    <div class="logo-auth">☕</div>
    <h1 class="judul-auth">Noir Cafe</h1>
    <p class="sub-auth">Buat akun baru</p>
>>>>>>> a5ebe0b1735c3f14f69185f4b1a313b582a1a213

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