<?php
// ============================================
// FILE: import_pesanan_excel.php
// ============================================

session_start();
include "config.php";
include "auth.php";
require_admin();

require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["file_excel"])) {
    $file_tmp = $_FILES["file_excel"]["tmp_name"];
    
    if (empty($file_tmp)) {
        header("Location: pesanan.php?pesan=import_error_empty");
        exit();
    }
    
    try {
        $spreadsheet = IOFactory::load($file_tmp);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        
        $orders = [];
        
        for ($row = 2; $row <= $highestRow; $row++) {
            $id_pesanan   = $sheet->getCell("A{$row}")->getValue();
            $username     = trim($sheet->getCell("B{$row}")->getValue() ?? '');
            $tanggal      = $sheet->getCell("C{$row}")->getValue();
            $status       = trim($sheet->getCell("D{$row}")->getValue() ?? 'Pending');
            $catatan      = trim($sheet->getCell("E{$row}")->getValue() ?? '');
            $nama_menu    = trim($sheet->getCell("F{$row}")->getValue() ?? '');
            $jumlah       = (int)$sheet->getCell("G{$row}")->getValue();
            $harga_satuan = (float)$sheet->getCell("H{$row}")->getValue();
            
            if (empty($username)) {
                continue; // Lewati baris kosong
            }
            
            $key = $id_pesanan ?: 'new_' . count($orders);
            if (!isset($orders[$key])) {
                $orders[$key] = [
                    'id' => $id_pesanan,
                    'username' => $username,
                    'tanggal' => $tanggal,
                    'status' => $status,
                    'catatan' => $catatan,
                    'items' => []
                ];
            }
            
            if (!empty($nama_menu) && $jumlah > 0) {
                $orders[$key]['items'][] = [
                    'nama_menu' => $nama_menu,
                    'jumlah' => $jumlah,
                    'harga_satuan' => $harga_satuan
                ];
            }
        }
        
        mysqli_begin_transaction($koneksi);
        
        foreach ($orders as $key => $o) {
            // 1. Dapatkan user_id dari username
            $username_db = mysqli_real_escape_string($koneksi, $o['username']);
            $user_query = mysqli_query($koneksi, "SELECT id FROM users WHERE username='$username_db'");
            if (mysqli_num_rows($user_query) > 0) {
                $user_data = mysqli_fetch_assoc($user_query);
                $user_id = $user_data['id'];
            } else {
                // Buat user baru otomatis jika belum ada
                $nama_baru = ucwords(str_replace('_', ' ', $username_db));
                $pass_hash = password_hash('password123', PASSWORD_DEFAULT);
                mysqli_query($koneksi, "INSERT INTO users (nama, username, password) VALUES ('$nama_baru', '$username_db', '$pass_hash')");
                $user_id = mysqli_insert_id($koneksi);
            }
            
            $status  = $o['status'] ?: 'Pending';
            $catatan = mysqli_real_escape_string($koneksi, $o['catatan']);
            $tanggal = $o['tanggal'] ? mysqli_real_escape_string($koneksi, $o['tanggal']) : date('Y-m-d H:i:s');
            
            $order_id = $o['id'];
            $exists = false;
            if (!empty($order_id)) {
                $check_order = mysqli_query($koneksi, "SELECT id FROM pesanan WHERE id='$order_id'");
                if (mysqli_num_rows($check_order) > 0) {
                    $exists = true;
                }
            }
            
            if ($exists) {
                // Update
                mysqli_query($koneksi, "UPDATE pesanan SET user_id='$user_id', tanggal='$tanggal', status='$status', catatan='$catatan' WHERE id='$order_id'");
                mysqli_query($koneksi, "DELETE FROM pesanan_detail WHERE pesanan_id='$order_id'");
            } else {
                // Insert
                if (!empty($order_id)) {
                    mysqli_query($koneksi, "INSERT INTO pesanan (id, user_id, tanggal, status, total_harga, catatan) VALUES ('$order_id', '$user_id', '$tanggal', '$status', 0, '$catatan')");
                } else {
                    mysqli_query($koneksi, "INSERT INTO pesanan (user_id, tanggal, status, total_harga, catatan) VALUES ('$user_id', '$tanggal', '$status', 0, '$catatan')");
                    $order_id = mysqli_insert_id($koneksi);
                }
            }
            
            $total_harga = 0;
            foreach ($o['items'] as $item) {
                $nama_menu_db = mysqli_real_escape_string($koneksi, $item['nama_menu']);
                $menu_query = mysqli_query($koneksi, "SELECT id, harga FROM menu WHERE nama_menu='$nama_menu_db'");
                
                if (mysqli_num_rows($menu_query) > 0) {
                    $menu_data = mysqli_fetch_assoc($menu_query);
                    $menu_id = $menu_data['id'];
                    $harga_satuan = $item['harga_satuan'] ?: $menu_data['harga'];
                } else {
                    // Buat menu baru jika tidak ada
                    $harga_satuan = $item['harga_satuan'] ?: 15000;
                    mysqli_query($koneksi, "INSERT INTO menu (nama_menu, kategori, harga, stok) VALUES ('$nama_menu_db', 'Makanan', '$harga_satuan', 'Tersedia')");
                    $menu_id = mysqli_insert_id($koneksi);
                }
                
                $jumlah = $item['jumlah'];
                $subtotal = $harga_satuan * $jumlah;
                $total_harga += $subtotal;
                
                mysqli_query($koneksi, "INSERT INTO pesanan_detail (pesanan_id, menu_id, jumlah, harga_satuan, subtotal) VALUES ('$order_id', '$menu_id', '$jumlah', '$harga_satuan', '$subtotal')");
            }
            
            mysqli_query($koneksi, "UPDATE pesanan SET total_harga='$total_harga' WHERE id='$order_id'");
        }
        
        mysqli_commit($koneksi);
        header("Location: pesanan.php?pesan=import_sukses");
        exit();
        
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        header("Location: pesanan.php?pesan=import_gagal&error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: pesanan.php");
    exit();
}
