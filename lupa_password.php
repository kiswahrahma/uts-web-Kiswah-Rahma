<?php
// ============================================
// FILE: lupa_password.php
// Fungsi: Reset password dengan verifikasi email OTP
// ============================================

session_start();
include "config.php";
include "mail_helper.php";

if (isset($_GET['batal'])) {
    unset($_SESSION['reset_email']);
    header("Location: lupa_password.php");
    exit();
}

$pesan = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "send_otp") {
        $email = trim($_POST["email"]);

        if (empty($email)) {
            $pesan = "error|Alamat email wajib diisi!";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $pesan = "error|Format email tidak valid!";
        } else {
            // Cek apakah email ada di database
            $cek = mysqli_query($koneksi, "SELECT id FROM users WHERE email='$email'");

            if (mysqli_num_rows($cek) == 0) {
                $pesan = "error|Alamat email tidak ditemukan!";
            } else {
                // Buat OTP code
                $otp = strval(rand(100000, 999999));
                $expiry = date('Y-m-d H:i:s', time() + 300); // 5 menit

                // Update OTP di database
                $update = mysqli_query($koneksi, "UPDATE users SET otp_code='$otp', otp_expiry='$expiry' WHERE email='$email'");

                if ($update) {
                    $_SESSION['reset_email'] = $email;
                    // Kirim OTP
                    $kirim = sendOTP($email, $otp, 'reset_password');
                    if ($kirim['success']) {
                        $pesan = "sukses|Kode OTP reset password telah dikirim ke email Anda.";
                    } else {
                        $pesan = "error|" . $kirim['message'];
                    }
                } else {
                    $pesan = "error|Gagal membuat kode OTP. Coba lagi.";
                }
            }
        }
    } elseif ($action === "reset_password") {
        $email = $_SESSION['reset_email'] ?? "";
        $otp_input = trim($_POST["otp_code"]);
        $password_baru = $_POST["password_baru"];
        $konfirm = $_POST["konfirm_password"];

        if (empty($email)) {
            $pesan = "error|Sesi reset password tidak valid. Silakan mulai kembali.";
        } elseif (empty($otp_input) || empty($password_baru) || empty($konfirm)) {
            $pesan = "error|Semua kolom wajib diisi!";
        } elseif ($password_baru !== $konfirm) {
            $pesan = "error|Password baru dan konfirmasi tidak sama!";
        } elseif (strlen($password_baru) < 6) {
            $pesan = "error|Password minimal 6 karakter!";
        } else {
            // Ambil data user
            $cek = mysqli_query($koneksi, "SELECT * FROM users WHERE email='$email'");
            if (mysqli_num_rows($cek) == 1) {
                $user = mysqli_fetch_assoc($cek);
                $db_otp = $user["otp_code"];
                $db_expiry = $user["otp_expiry"];

                if ($db_otp === $otp_input) {
                    if (strtotime($db_expiry) > time()) {
                        // OTP cocok dan belum kadaluarsa
                        $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);
                        // Update password & hapus OTP
                        $sql = "UPDATE users SET password='$password_hash', otp_code=NULL, otp_expiry=NULL WHERE email='$email'";
                        if (mysqli_query($koneksi, $sql)) {
                            $pesan = "sukses|Password berhasil direset! Silakan login dengan password baru.";
                            unset($_SESSION['reset_email']);
                        } else {
                            $pesan = "error|Gagal mereset password. Coba lagi.";
                        }
                    } else {
                        $pesan = "error|Kode OTP sudah kadaluarsa! Silakan minta kode baru.";
                    }
                } else {
                    $pesan = "error|Kode OTP salah! Periksa kembali kode Anda.";
                }
            } else {
                $pesan = "error|User tidak ditemukan.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Cafe Kiswah</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/dark-mode.js" defer></script>
</head>
<body class="halaman-auth">

<div class="kotak-auth">
    <a href="index.php" style="text-decoration: none; color: inherit; display: inline-block; margin-bottom: 5px;">
        <div class="logo-auth" style="margin-bottom: 0;">🔑</div>
        <h1 class="judul-auth" style="margin-top: 5px; margin-bottom: 0;">Lupa Password</h1>
    </a>
    <p class="sub-auth" style="margin-top: 5px;">Reset password akun kamu</p>

    <?php
    if (!empty($pesan)) {
        $bagian = explode("|", $pesan);
        echo "<div class='pesan {$bagian[0]}'>{$bagian[1]}</div>";
    }

    // Tampilkan bantuan Developer/Debug OTP di UI jika SMTP_DEBUG_MODE aktif
    if (defined('SMTP_DEBUG_MODE') && SMTP_DEBUG_MODE && isset($_SESSION['last_otp_debug']) && isset($_SESSION['reset_email']) && $_SESSION['last_otp_debug']['email'] === $_SESSION['reset_email'] && $_SESSION['last_otp_debug']['purpose'] === 'reset_password') {
        echo "<div class='pesan sukses' style='background-color: #fce4d6; color: #c65911; border: 1px solid #f8cbad; margin-bottom: 15px; font-size:14px; text-align:center;'>
                [DEV MODE] Kode OTP Anda:<br><strong style='font-size:20px; letter-spacing: 2px;'>" . htmlspecialchars($_SESSION['last_otp_debug']['code']) . "</strong>
              </div>";
    }
    ?>

    <?php if (strpos($pesan, "berhasil direset") !== false) : ?>
        <!-- Kalau sukses, tampilkan tombol ke login -->
        <a href="login.php" class="tombol-utama" style="display:block; text-align:center; margin-top:10px; text-decoration: none; line-height: 40px; height: 40px;">
            Ke Halaman Login
        </a>

    <?php elseif (isset($_SESSION['reset_email'])) : ?>
        <!-- Form Step 2: Masukkan OTP & Password Baru -->
        <p style="font-size: 14px; color: #666; margin-bottom: 15px; text-align: center;">
            Kode OTP dikirim ke <strong><?= htmlspecialchars($_SESSION['reset_email']) ?></strong>
        </p>
        <form method="POST" action="">
            <input type="hidden" name="action" value="reset_password">

            <div class="grup-form">
                <label>Kode OTP (6 Digit)</label>
                <input type="text"
                       name="otp_code"
                       maxlength="6"
                       placeholder="Masukkan kode OTP"
                       style="text-align: center; letter-spacing: 4px; font-weight: bold;"
                       required
                       autocomplete="off">
            </div>

            <div class="grup-form">
                <label>Password Baru</label>
                <input type="password"
                       name="password_baru"
                       placeholder="Minimal 6 karakter"
                       required>
            </div>

            <div class="grup-form">
                <label>Konfirmasi Password Baru</label>
                <input type="password"
                       name="konfirm_password"
                       placeholder="Ulangi password baru"
                       required>
            </div>

            <button type="submit" class="tombol-utama">Reset Password</button>
            <a href="lupa_password.php?batal=1" class="tombol-sekunder" style="display:block; text-align:center; margin-top:10px; text-decoration:none; color:#8c7853; font-size:14px;">Batal & Kembali</a>
        </form>

    <?php else : ?>
        <!-- Form Step 1: Masukkan Email -->
        <form method="POST" action="">
            <input type="hidden" name="action" value="send_otp">

            <div class="grup-form">
                <label>Alamat Email</label>
                <input type="email"
                       name="email"
                       placeholder="Masukkan email terdaftar"
                       value="<?= $_POST['email'] ?? '' ?>"
                       required>
            </div>

            <button type="submit" class="tombol-utama">Kirim Kode OTP</button>
        </form>

    <?php endif; ?>

    <p class="link-auth" style="margin-top: 20px;">Ingat password? <a href="login.php">Login di sini</a></p>
</div>

</body>
</html>