<?php
session_start();
include 'config.php'; // Koneksi database

$message = '';
$type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'forgot') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    // Cek email ada di database
    $check = mysqli_query($conn, "SELECT id, email FROM users WHERE email='$email'");
    
    if (mysqli_num_rows($check) > 0) {
        // Generate secure token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 15*60); // 15 menit
        
        // Hapus token lama untuk email ini
        mysqli_query($conn, "DELETE FROM password_resets WHERE email='$email'");
        
        // Simpan token baru
        $sql = "INSERT INTO password_resets (email, token, expires_at) 
                VALUES ('$email', '$token', '$expires')";
        
        if (mysqli_query($conn, $sql)) {
            // Kirim email (ganti dengan EmailJS atau SMTP nanti)
            $resetLink = "http://localhost/reset-password.php?token=" . $token;
            $subject = "Reset Password - Your App";
            $body = "Klik link ini untuk reset password: " . $resetLink . "\n\nLink expired dalam 15 menit.";
            
            // Untuk testing, tampilkan link di browser
            $message = "✅ Link reset password: <strong>$resetLink</strong><br>Cek console atau copy link di atas!";
            $type = 'success';
            
            // Uncomment untuk kirim email beneran
            // mail($email, $subject, $body);
        } else {
            $message = "❌ Gagal menyimpan reset token!";
            $type = 'error';
        }
    } else {
        $message = "❌ Email tidak ditemukan!";
        $type = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="forgot-container">
        <h1 class="forgot-title">🔒 Lupa Password?</h1>
        <p class="forgot-subtitle">Masukkan email kamu, kami akan kirim link reset password</p>
        
        <?php if ($message): ?>
            <div class="message <?= $type == 'success' ? 'success-msg' : 'error-msg' ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" id="forgotForm">
            <input type="hidden" name="action" value="forgot">
            <div class="input-group">
                <input type="email" name="email" id="resetEmail" placeholder="Email kamu" required 
                       value="<?= isset($_POST['email']) ? $_POST['email'] : '' ?>">
            </div>
            <button type="submit" class="btn-primary" id="submitBtn">
                <span id="btnText">Kirim Link Reset</span>
                <span id="loadingSpinner" class="loading" style="display:none;"></span>
            </button>
        </form>
        
        <a href="login.php" class="back-link">← Kembali ke Login</a>
    </div>

    <script>
    document.getElementById('forgotForm').onsubmit = function(e) {
        const btn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const spinner = document.getElementById('loadingSpinner');
        
        btn.disabled = true;
        btnText.style.display = 'none';
        spinner.style.display = 'inline-block';
    }
    </script>
</body>
</html>