<?php
// ============================================
// FILE: verify_register.php
// Fungsi: Verifikasi OTP email sebelum akun baru benar-benar dibuat
// ============================================

session_start();
include "config.php";
include "mail_helper.php";

// Kalau sudah login, tidak perlu di sini
if (isset($_SESSION["user_id"])) {
    header("Location: " . (($_SESSION["role"] ?? "") === "admin" ? "dashboard.php" : "index.php"));
    exit();
}

// Kalau tidak ada proses registrasi tertunda, lempar ke halaman register
if (!isset($_SESSION["pending_register"])) {
    header("Location: register.php");
    exit();
}

$data = $_SESSION["pending_register"];
$pesan = "";

// === VERIFIKASI OTP ===
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"]) && $_POST["action"] === "verify") {
    $otp_input = trim($_POST["otp_code"]);

    if (empty($otp_input)) {
        $pesan = "error|Silakan masukkan kode OTP!";
    } elseif ($otp_input !== $data["otp_code"]) {
        $pesan = "error|Kode OTP salah! Coba lagi.";
    } elseif (time() > $data["otp_expiry"]) {
        $pesan = "error|Kode OTP sudah kadaluarsa! Silakan kirim ulang kode.";
    } else {
        // OTP cocok, cek lagi username/email belum dipakai orang lain (jaga-jaga race condition)
        $email    = mysqli_real_escape_string($koneksi, $data["email"]);
        $username = mysqli_real_escape_string($koneksi, $data["username"]);
        $cek = mysqli_query($koneksi, "SELECT id FROM users WHERE email='$email' OR username='$username'");

        if (mysqli_num_rows($cek) > 0) {
            $pesan = "error|Username atau email sudah terdaftar. Silakan daftar ulang.";
            unset($_SESSION["pending_register"]);
        } else {
            $nama = mysqli_real_escape_string($koneksi, $data["nama"]);
            $password_hash = $data["password"];

            $sql = "INSERT INTO users (nama, email, username, password, role) VALUES ('$nama', '$email', '$username', '$password_hash', 'pelanggan')";

            if (mysqli_query($koneksi, $sql)) {
                unset($_SESSION["pending_register"]);
                $_SESSION["register_sukses"] = true;
                header("Location: login.php");
                exit();
            } else {
                $pesan = "error|Terjadi kesalahan saat membuat akun. Coba lagi.";
            }
        }
    }
}

// === KIRIM ULANG OTP ===
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"]) && $_POST["action"] === "resend") {
    $otp = strval(rand(100000, 999999));
    $expiry = time() + 300;

    $data["otp_code"] = $otp;
    $data["otp_expiry"] = $expiry;
    $_SESSION["pending_register"] = $data;

    $kirim = sendOTP($data["email"], $otp, 'register');
    if ($kirim['success']) {
        $pesan = "sukses|Kode OTP baru telah dikirim ke email " . htmlspecialchars($data["email"]);
    } else {
        $pesan = "error|" . $kirim['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Akun - Noir Cafe</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/dark-mode.js" defer></script>
</head>
<body class="halaman-auth">

<div class="kotak-auth">
    <div class="logo-auth">✉️</div>
    <h1 class="judul-auth">Verifikasi Akun Baru</h1>
    <p class="sub-auth" style="margin-top: 5px; margin-bottom: 20px;">
        Kami telah mengirimkan 6 digit kode OTP ke email:<br>
        <strong><?= htmlspecialchars($data["email"]) ?></strong>
    </p>

    <?php
    if (!empty($pesan)) {
        $bagian = explode("|", $pesan);
        echo "<div class='pesan {$bagian[0]}'>{$bagian[1]}</div>";
    }

    // Tampilkan bantuan Developer/Debug OTP di UI jika SMTP_DEBUG_MODE aktif
    if (defined('SMTP_DEBUG_MODE') && SMTP_DEBUG_MODE && isset($_SESSION['last_otp_debug']) && $_SESSION['last_otp_debug']['email'] === $data['email'] && $_SESSION['last_otp_debug']['purpose'] === 'register') {
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

        <button type="submit" class="tombol-utama" style="margin-top: 15px;">Verifikasi & Buat Akun</button>
    </form>

    <!-- Form Kirim Ulang -->
    <form method="POST" action="" style="margin-top: 15px;">
        <input type="hidden" name="action" value="resend">
        <button type="submit" class="tombol-sekunder" style="background: none; border: none; color: #8c7853; text-decoration: underline; cursor: pointer; font-size: 14px; width: 100%;">
            Kirim Ulang Kode OTP
        </button>
    </form>

    <p class="link-auth" style="margin-top: 20px;">Salah data? <a href="register.php">Daftar ulang</a></p>
</div>

</body>
</html>
