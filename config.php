<?php
// ============================================
// FILE: config.php
// Konfigurasi Database & Constants System
// ============================================

$host     = "localhost";
$user     = "root";
$password = "";
$database = "cafe";

$koneksi = mysqli_connect($host, $user, $password, $database);

if (!$koneksi) {
    die("Koneksi GAGAL: " . mysqli_connect_error());
}

// === SMTP MAIL CONFIGURATION ===
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'wawiwa0311@gmail.com');
define('SMTP_PASS', 'wfmfntgjofzuatwl');
define('SMTP_FROM_EMAIL', 'wawiwa0311@gmail.com');
define('SMTP_FROM_NAME', 'Noir Cafe Admin');
define('SMTP_DEBUG_MODE', false);

// === GOOGLE SSO CONFIGURATION ===
define('GOOGLE_CLIENT_ID', '9491350571-d4qqvjcfn1lvo915qthr4osi0jp3gmd2.apps.googleusercontent.com');
?>