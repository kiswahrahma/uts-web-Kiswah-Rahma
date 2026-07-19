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
define('SMTP_USER', 'wawiwa0311@gmail.com');      // Ganti dengan email Gmail Anda
define('SMTP_PASS', 'wfmfntgjofzuatwl');       // Ganti dengan App Password Gmail Anda
define('SMTP_FROM_EMAIL', 'wawiwa0311@gmail.com'); // Ganti dengan email Gmail Anda
define('SMTP_FROM_NAME', 'Noir Cafe Admin');
define('SMTP_DEBUG_MODE', false);                // Set ke true jika ingin testing tanpa SMTP (kode OTP akan dicetak langsung di halaman/log)
?>