<?php
include 'config.php';
$res = mysqli_query($koneksi, "SELECT id, username, nama, role FROM users");
while($row = mysqli_fetch_assoc($res)) {
    print_r($row);
}

