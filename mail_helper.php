<?php
// Memasukkan Composer Autoloader
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Mengirimkan kode OTP ke email tertentu.
 * 
 * @param string $email Penerima email
 * @param string $otp Kode OTP 6-digit
 * @param string $purpose Tujuan pengiriman ('login' atau 'reset_password')
 * @return array Array berisi status kebersihan: ['success' => bool, 'message' => string]
 */
function sendOTP($email, $otp, $purpose = 'login') {
    // Jika debug mode aktif, catat OTP ke file log atau kembalikan success untuk simulasi
    if (defined('SMTP_DEBUG_MODE') && SMTP_DEBUG_MODE) {
        $log_message = "[" . date('Y-m-d H:i:s') . "] Kirim OTP ke $email: Kode=$otp, Tujuan=$purpose (DEBUG MODE ACTIVE)\n";
        file_put_contents(__DIR__ . '/otp_debug.log', $log_message, FILE_APPEND);
        
        // Simpan OTP terakhir ke session agar bisa ditampilkan di UI login untuk kemudahan testing
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['last_otp_debug'] = [
            'email' => $email,
            'code' => $otp,
            'purpose' => $purpose,
            'time' => time()
        ];
        
        return [
            'success' => true,
            'message' => 'Simulasi pengiriman email berhasil (Debug Mode aktif). Kode OTP dicatat ke log.'
        ];
    }

    // Menggunakan PHPMailer untuk pengiriman riil
    $mail = new PHPMailer(true);

    try {
        // Pengaturan Server SMTP
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // TLS
        $mail->Port       = SMTP_PORT;

        // Penerima
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($email);

        // Konten Email
        $mail->isHTML(true);
        if ($purpose === 'reset_password') {
            $mail->Subject = 'Reset Password OTP - Noir Cafe';
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #ddd; padding: 20px; border-radius: 8px;'>
                    <h2 style='color: #4A3E3D; text-align: center;'>☕ Reset Password Noir Cafe</h2>
                    <p>Halo,</p>
                    <p>Kami menerima permintaan untuk meriset password akun Anda. Silakan gunakan kode OTP di bawah ini untuk melanjutkan:</p>
                    <div style='background-color: #f7f7f7; padding: 15px; border-radius: 6px; text-align: center; margin: 20px 0;'>
                        <span style='font-size: 28px; font-weight: bold; letter-spacing: 5px; color: #b8860b;'>$otp</span>
                    </div>
                    <p style='color: #666; font-size: 14px;'>Kode OTP ini berlaku selama 5 menit. Jangan berikan kode ini kepada siapapun.</p>
                    <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>
                    <p style='font-size: 12px; color: #999; text-align: center;'>Email ini dikirim otomatis oleh Noir Cafe App.</p>
                </div>
            ";
        } else {
            $mail->Subject = 'Login OTP Verification - Noir Cafe';
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #ddd; padding: 20px; border-radius: 8px;'>
                    <h2 style='color: #4A3E3D; text-align: center;'>☕ Noir Cafe Login OTP</h2>
                    <p>Halo,</p>
                    <p>Silakan gunakan kode OTP di bawah ini untuk menyelesaikan proses login Anda:</p>
                    <div style='background-color: #f7f7f7; padding: 15px; border-radius: 6px; text-align: center; margin: 20px 0;'>
                        <span style='font-size: 28px; font-weight: bold; letter-spacing: 5px; color: #b8860b;'>$otp</span>
                    </div>
                    <p style='color: #666; font-size: 14px;'>Kode OTP ini berlaku selama 5 menit. Jangan berikan kode ini kepada siapapun.</p>
                    <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>
                    <p style='font-size: 12px; color: #999; text-align: center;'>Email ini dikirim otomatis oleh Noir Cafe App.</p>
                </div>
            ";
        }

        $mail->send();
        return ['success' => true, 'message' => 'Email OTP berhasil terkirim.'];

    } catch (Exception $e) {
        // Jika gagal kirim via SMTP, tapi debug mode tidak aktif, catat error dan kembalikan false
        error_log("Gagal mengirim email: " . $mail->ErrorInfo);
        return [
            'success' => false,
            'message' => 'Gagal mengirim email OTP: ' . $mail->ErrorInfo
        ];
    }
}
