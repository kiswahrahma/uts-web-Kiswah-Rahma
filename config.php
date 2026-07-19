<?php
$host     = "localhost";
$user     = "root";
$password = "";
$database = "cafe";   // sesuai nama database kamu

$koneksi = mysqli_connect($host, $user, $password, $database);

if (!$koneksi) {
    die("Koneksi GAGAL: " . mysqli_connect_error());
}

// === SMTP MAIL CONFIGURATION ===
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your_email@gmail.com');      // Ganti dengan email Gmail Anda
define('SMTP_PASS', 'your_app_password');       // Ganti dengan App Password Gmail Anda
define('SMTP_FROM_EMAIL', 'your_email@gmail.com'); // Ganti dengan email Gmail Anda
define('SMTP_FROM_NAME', 'Noir Cafe Admin');
define('SMTP_DEBUG_MODE', true);                // Set ke true jika ingin testing tanpa SMTP (kode OTP akan dicetak langsung di halaman/log)
?>