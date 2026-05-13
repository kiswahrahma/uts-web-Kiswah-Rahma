<?php
session_start();
include 'config.php';

$token = isset($_GET['token']) ? mysqli_real_escape_string($conn, $_GET['token']) : '';
$message = '';
$type = '';

if (empty($token)) {
    $message = "❌ Token tidak valid!";
    $type = 'error';
} else {
    // Cek token valid dan belum expired
    $check = mysqli_query($conn, "
        SELECT pr.email, u.id 
        FROM password_resets pr 
        JOIN users u ON pr.email = u.email
        WHERE pr.token='$token' AND pr.expires_at > NOW()
    ");
    
    if (mysqli_num_rows($check) > 0) {
        $user = mysqli_fetch_assoc($check);
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $newPass = password_hash($_POST['new_pass'], PASSWORD_DEFAULT);
            
            // Update password
            $update = mysqli_query($conn, 
                "UPDATE users SET password='$newPass' WHERE id=" . $user['id']
            );
            
            // Hapus token
            mysqli_query($conn, "DELETE FROM password_resets WHERE token='$token'");
            
            if ($update) {
                $message = "✅ Password berhasil direset! Silakan login.";
                $type = 'success';
            } else {
                $message = "❌ Gagal update password!";
                $type = 'error';
            }
        }
    } else {
        $message = "❌ Token invalid atau sudah expired!";
        $type = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="forgot-container">
        <h1 class="forgot-title">🔐 Reset Password</h1>
        <p class="forgot-subtitle">Buat password baru untuk akun kamu</p>
        
        <?php if ($message): ?>
            <div class="message <?= $type == 'success' ? 'success-msg' : 'error-msg' ?>">
                <?= $message ?>
                <?php if ($type == 'success'): ?>
                    <br><a href="login.php" class="back-link">→ Login Sekarang</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($type != 'success' && mysqli_num_rows($check) > 0): ?>
        <form method="POST" id="resetForm">
            <div class="input-group">
                <input type="password" name="new_pass" id="newPass" 
                       placeholder="Password baru (min 6 karakter)" required minlength="6">
            </div>
            <div class="input-group">
                <input type="password" id="confirmPass" 
                       placeholder="Konfirmasi password" required minlength="6">
            </div>
            <button type="submit" class="btn-primary" id="submitBtn">
                <span id="btnText">Update Password</span>
                <span id="loadingSpinner" class="loading" style="display:none;"></span>
            </button>
        </form>
        <?php endif; ?>
        
        <a href="login.php" class="back-link">← Kembali ke Login</a>
    </div>

    <script>
    // Konfirmasi password
    document.getElementById('confirmPass').oninput = function() {
        const pass1 = document.getElementById('newPass').value;
        const pass2 = this.value;
        const btn = document.getElementById('submitBtn');
        
        if (pass1 !== pass2) {
            btn.style.opacity = '0.6';
            btn.disabled = true;
        } else {
            btn.style.opacity = '1';
            btn.disabled = false;
        }
    }

    // Loading button
    document.getElementById('resetForm').onsubmit = function(e) {
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