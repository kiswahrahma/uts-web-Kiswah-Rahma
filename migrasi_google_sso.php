<?php
// ============================================
// FILE: migrasi_google_sso.php
// Fungsi: Menjalankan migrasi database untuk Google SSO
// ============================================

include "config.php";

echo "<h3>Migrasi Database Google SSO</h3>";

// 1. Cek apakah kolom google_id sudah ada
$cek_kolom = mysqli_query($koneksi, "SHOW COLUMNS FROM users LIKE 'google_id'");
$ada = mysqli_num_rows($cek_kolom) > 0;

if ($ada) {
    echo "<p style='color: green;'>✔ Kolom 'google_id' sudah ada di tabel 'users'. Tidak perlu migrasi.</p>";
} else {
    // Jalankan ALTER TABLE
    $sql = "ALTER TABLE users ADD COLUMN google_id VARCHAR(255) NULL AFTER email";
    if (mysqli_query($koneksi, $sql)) {
        echo "<p style='color: green;'>✔ Sukses! Kolom 'google_id' berhasil ditambahkan ke tabel 'users'.</p>";
    } else {
        echo "<p style='color: red;'>❌ Gagal menambahkan kolom: " . mysqli_error($koneksi) . "</p>";
    }
}

// 2. Tampilkan struktur tabel saat ini untuk verifikasi
echo "<h4>Struktur Tabel 'users' saat ini:</h4>";
$hasil = mysqli_query($koneksi, "DESCRIBE users");
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>
        <thead>
            <tr style='background: #6f4e37; color: white;'>
                <th>Field</th>
                <th>Type</th>
                <th>Null</th>
                <th>Key</th>
                <th>Default</th>
                <th>Extra</th>
            </tr>
        </thead>
        <tbody>";
while ($row = mysqli_fetch_assoc($hasil)) {
    echo "<tr>
            <td>{$row['Field']}</td>
            <td>{$row['Type']}</td>
            <td>{$row['Null']}</td>
            <td>{$row['Key']}</td>
            <td>" . ($row['Default'] ?? 'NULL') . "</td>
            <td>{$row['Extra']}</td>
          </tr>";
}
echo "</tbody></table>";
?>
