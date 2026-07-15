<?php
<<<<<<< HEAD
session_start();

// Hapus semua data session
$_SESSION = array();

// Hapus cookie session jika ada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hancurkan session
session_destroy(); 

// Redirect langsung ke halaman utama publik (index.php)
header("Location: index.php");
=======
session_start();   
session_destroy(); 

// Kembali ke halaman login
header("Location: login.php");
>>>>>>> a5ebe0b1735c3f14f69185f4b1a313b582a1a213
exit();
?>