<?php
session_start();
include "config.php";
include "mail_helper.php";

// Jika user sudah login penuh, arahkan ke index/dashboard
if (isset($_SESSION["user_id"])) {
    header("Location: " . (($_SESSION["role"] ?? "") === "admin" ? "dashboard.php" : "index.php"));
    exit();
}

// Cek apakah ada proses login tertunda
if (!isset($_SESSION["pending_otp_user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["pending_otp_user_id"];
$redirect = $_SESSION["pending_otp_redirect"] ?? "";

// Ambil info user yang sedang login tertunda
$sql = "SELECT * FROM users WHERE id = '$user_id'";
$hasil = mysqli_query($koneksi, $sql);
if (mysqli_num_rows($hasil) !== 1) {
    // Pengguna tidak ditemukan, reset session dan kembalikan ke login
    unset($_SESSION["pending_otp_user_id"]);
    unset($_SESSION["pending_otp_redirect"]);
    header("Location: login.php");
    exit();
}
$user = mysqli_fetch_assoc($hasil);
$pesan = "";

// === LOGIK VERIFIKASI OTP ===
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"]) && $_POST["action"] === "verify") {
    $otp_input = trim($_POST["otp_code"]);

    if (empty($otp_input)) {
        $pesan = "error|Silakan masukkan kode OTP!";
    } else {
        // Bandingkan kode OTP dan cek kadaluarsanya
        $db_otp = $user["otp_code"];
        $db_expiry = $user["otp_expiry"];
        $now = date('Y-m-d H:i:s');

        if ($db_otp === $otp_input) {
            if (strtotime($db_expiry) > time()) {
                // OTP COCOK DAN BELUM KADALUARSA!
                // Bersihkan kolom OTP di database
                mysqli_query($koneksi, "UPDATE users SET otp_code=NULL, otp_expiry=NULL WHERE id='$user_id'");

                // Set session login penuh
                $_SESSION["user_id"]   = $user["id"];
                $_SESSION["user_nama"] = $user["nama"];
                $_SESSION["username"]  = $user["username"];
                $_SESSION["role"]      = $user["role"] ?? "pelanggan";

                // Bersihkan session login tertunda
                unset($_SESSION["pending_otp_user_id"]);
                unset($_SESSION["pending_otp_redirect"]);

                // Arahkan ke halaman tujuan atau dashboard/index
                if (!empty($redirect)) {
                    header("Location: " . $redirect);
                } elseif ($_SESSION["role"] === "admin") {
                    header("Location: dashboard.php");
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                $pesan = "error|Kode OTP sudah kadaluarsa! Silakan kirim ulang kode.";
            }
        } else {
            $pesan = "error|Kode OTP salah! Coba lagi.";
        }
    }
}

// === LOGIK KIRIM ULANG OTP ===
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"]) && $_POST["action"] === "resend") {
    $otp = strval(rand(100000, 999999));
    $expiry = date('Y-m-d H:i:s', time() + 300); // 5 menit

    $update = mysqli_query($koneksi, "UPDATE users SET otp_code='$otp', otp_expiry='$expiry' WHERE id='$user_id'");
    if ($update) {
        $kirim = sendOTP($user["email"], $otp, 'login');
        if ($kirim['success']) {
            $pesan = "sukses|Kode OTP baru telah berhasil dikirim ke email " . htmlspecialchars($user["email"]);
            // Update user array agar data terbaru tampil
            $user["otp_code"] = $otp;
            $user["otp_expiry"] = $expiry;
        } else {
            $pesan = "error|" . $kirim['message'];
        }
    } else {
        $pesan = "error|Gagal memperbarui kode OTP di database.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi OTP - Noir Cafe</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="halaman-auth">

<div class="kotak-auth">
    <div class="logo-auth">✉️</div>
    <h1 class="judul-auth">Verifikasi Kode OTP</h1>
    <p class="sub-auth" style="margin-top: 5px; margin-bottom: 20px;">
        Kami telah mengirimkan 6 digit kode OTP ke email:<br>
        <strong><?= htmlspecialchars($user["email"]) ?></strong>
    </p>

    <?php
    // Tampilkan pesan error/sukses
    if (!empty($pesan)) {
        $bagian = explode("|", $pesan);
        echo "<div class='pesan {$bagian[0]}'>{$bagian[1]}</div>";
    }

    // Tampilkan bantuan Developer/Debug OTP di UI jika SMTP_DEBUG_MODE aktif
    if (defined('SMTP_DEBUG_MODE') && SMTP_DEBUG_MODE && isset($_SESSION['last_otp_debug']) && $_SESSION['last_otp_debug']['email'] === $user['email']) {
        echo "<div class='pesan sukses' style='background-color: #fce4d6; color: #c65911; border: 1px solid #f8cbad; margin-bottom: 15px; font-size:14px; text-align:center;'>
                [DEV MODE] Kode OTP Anda:<br><strong style='font-size:20px; letter-spacing: 2px;'>" . htmlspecialchars($_SESSION['last_otp_debug']['code']) . "</strong>
              </div>";
    }
    ?>

    <form method="POST" action="">
        <input type="hidden" name="action" value="verify">
        
        <div class="grup-form" style="text-align: center;">
            <label style="display:block; text-align:center; font-weight:bold; margin-bottom: 10px;">Kode OTP (6 Digit)</label>
            <input type="text" 
                   name="otp_code" 
                   maxlength="6" 
                   placeholder="------" 
                   style="font-size: 24px; text-align: center; letter-spacing: 8px; width: 100%; box-sizing: border-box;" 
                   required 
                   autocomplete="off">
        </div>

        <button type="submit" class="tombol-utama" style="margin-top: 15px;">Verifikasi & Masuk</button>
    </form>

    <!-- Form Kirim Ulang -->
    <form method="POST" action="" style="margin-top: 15px;">
        <input type="hidden" name="action" value="resend">
        <button type="submit" class="tombol-sekunder" style="background: none; border: none; color: #8c7853; text-decoration: underline; cursor: pointer; font-size: 14px; width: 100%;">
            Kirim Ulang Kode OTP
        </button>
    </form>

    <p class="link-auth" style="margin-top: 20px;">Bukan akun Anda? <a href="logout.php">Kembali ke Login</a></p>
</div>

</body>
</html>
