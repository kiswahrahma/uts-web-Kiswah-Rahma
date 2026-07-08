<?php
$host     = "localhost";
$user     = "root";
$password = "";
$database = "cafe";   // sesuai nama database kamu

$koneksi = mysqli_connect($host, $user, $password, $database);

if (!$koneksi) {
    die("Koneksi GAGAL: " . mysqli_connect_error());
}
?>