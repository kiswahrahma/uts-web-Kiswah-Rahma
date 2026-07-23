<?php
// ============================================
// FILE: google-callback.php
// Fungsi: Handler callback untuk Google SSO (Masuk/Daftar)
// ============================================

session_start();
include "config.php";

$pesan_error = "";

// 1. Dapatkan data pengguna (Email, Nama, Google ID)
$email = "";
$nama = "";
$google_id = "";

// Cek apakah ini simulasi login (untuk mempermudah testing lokal/offline)
if (isset($_POST['simulated_sso']) && $_POST['simulated_sso'] == '1') {
    // Validasi apakah mode debug aktif atau Google Client ID belum dikonfigurasi
    if ((defined('SMTP_DEBUG_MODE') && SMTP_DEBUG_MODE) || GOOGLE_CLIENT_ID === 'YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com') {
        $email = trim($_POST['email'] ?? '');
        $nama = trim($_POST['nama'] ?? '');
        $google_id = "google_simulated_" . md5($email);
        
        if (empty($email) || empty($nama)) {
            $_SESSION['pesan_sso_error'] = "Email dan Nama wajib diisi untuk simulasi!";
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['pesan_sso_error'] = "Simulasi Google SSO hanya diperbolehkan dalam Mode Debug atau jika Google Client ID belum diatur!";
        header("Location: login.php");
        exit();
    }
} else {
    // Jalur resmi Google SSO: menerima 'credential' (JWT ID Token) dari Google
    $id_token = $_POST['credential'] ?? '';
    
    if (empty($id_token)) {
        $_SESSION['pesan_sso_error'] = "Token kredensial Google tidak ditemukan!";
        header("Location: login.php");
        exit();
    }
    
    // Verifikasi Token JWT Google
    $client_id = GOOGLE_CLIENT_ID;
    $verified_data = null;
    
    // Alur A: Coba panggil Endpoint TokenInfo Google
    $url = "https://oauth2.googleapis.com/tokeninfo?id_token=" . urlencode($id_token);
    
    $ctx = stream_context_create([
        'http' => [
            'timeout' => 5 // 5 detik
        ]
    ]);
    
    $response = @file_get_contents($url, false, $ctx);
    if ($response) {
        $data = json_decode($response, true);
        if ($data && isset($data['email']) && $data['aud'] === $client_id) {
            $verified_data = $data;
        }
    }
    
    // Alur B: Fallback ke Dekode Lokal
    if (!$verified_data && ((defined('SMTP_DEBUG_MODE') && SMTP_DEBUG_MODE) || GOOGLE_CLIENT_ID === 'YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com')) {
        $parts = explode('.', $id_token);
        if (count($parts) === 3) {
            $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1])), true);
            if ($payload && ($payload['aud'] === $client_id || $client_id === 'YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com') && time() <= $payload['exp']) {
                $verified_data = $payload;
            }
        }
    }
    
    if ($verified_data) {
        $email = trim($verified_data['email'] ?? '');
        $nama = trim($verified_data['name'] ?? '');
        $google_id = trim($verified_data['sub'] ?? '');
    } else {
        $_SESSION['pesan_sso_error'] = "Gagal memverifikasi akun Google! Periksa konfigurasi Client ID Anda.";
        header("Location: login.php");
        exit();
    }
}

// 2. Jalankan Logika Autentikasi / Registrasi di Database
if (!empty($email)) {
    $email_esc = mysqli_real_escape_string($koneksi, $email);
    
    // Cari user dengan email tersebut
    $sql = "SELECT * FROM users WHERE email = '$email_esc'";
    $hasil = mysqli_query($koneksi, $sql);
    
    if (mysqli_num_rows($hasil) == 1) {
        // --- CASE 1: User Sudah Terdaftar ---
        $user = mysqli_fetch_assoc($hasil);
        
        // Update google_id jika sebelumnya kosong
        if (empty($user['google_id']) && !empty($google_id)) {
            $google_id_esc = mysqli_real_escape_string($koneksi, $google_id);
            mysqli_query($koneksi, "UPDATE users SET google_id = '$google_id_esc' WHERE id = '{$user['id']}'");
        }
        
        // Set Session langsung masuk
        $_SESSION["user_id"]   = $user["id"];
        $_SESSION["user_nama"] = $user["nama"];
        $_SESSION["username"]  = $user["username"];
        $_SESSION["role"]      = $user["role"] ?? "pelanggan";
        
        // Arahkan ke halaman tujuan sesuai role
        if ($_SESSION["role"] === "admin") {
            header("Location: dashboard.php");
        } else {
            header("Location: index.php");
        }
        exit();
        
    } else {
        // --- CASE 2: User Belum Terdaftar (Daftar Otomatis Tanpa OTP) ---
        
        // Buat username unik berdasarkan email prefix
        $email_parts = explode('@', $email);
        $username_base = preg_replace('/[^a-zA-Z0-9]/', '', $email_parts[0]);
        if (empty($username_base)) {
            $username_base = "user";
        }
        
        $username_candidate = $username_base;
        $counter = 1;
        
        while (true) {
            $usr_esc = mysqli_real_escape_string($koneksi, $username_candidate);
            $cek_uname = mysqli_query($koneksi, "SELECT id FROM users WHERE username = '$usr_esc'");
            if (mysqli_num_rows($cek_uname) == 0) {
                break;
            }
            $username_candidate = $username_base . rand(100, 999);
            $counter++;
            if ($counter > 10) {
                $username_candidate = $username_base . time();
                break;
            }
        }
        
        $nama_esc = mysqli_real_escape_string($koneksi, $nama);
        $google_id_esc = mysqli_real_escape_string($koneksi, $google_id);
        $username_final = mysqli_real_escape_string($koneksi, $username_candidate);
        
        $random_password = bin2hex(random_bytes(16));
        $password_hash = password_hash($random_password, PASSWORD_DEFAULT);
        
        // Simpan ke database
        $sql_insert = "INSERT INTO users (nama, email, google_id, username, password, role) 
                       VALUES ('$nama_esc', '$email_esc', '$google_id_esc', '$username_final', '$password_hash', 'pelanggan')";
        
        if (mysqli_query($koneksi, $sql_insert)) {
            $new_id = mysqli_insert_id($koneksi);
            
            // Set session
            $_SESSION["user_id"]   = $new_id;
            $_SESSION["user_nama"] = $nama;
            $_SESSION["username"]  = $username_final;
            $_SESSION["role"]      = "pelanggan";
            
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['pesan_sso_error'] = "Gagal mendaftarkan akun baru secara otomatis: " . mysqli_error($koneksi);
            header("Location: login.php");
            exit();
        }
    }
} else {
    $_SESSION['pesan_sso_error'] = "Data profil Google tidak valid!";
    header("Location: login.php");
    exit();
}
?>