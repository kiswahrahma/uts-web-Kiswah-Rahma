<?php
session_start();   
session_destroy(); 

// Kembali ke halaman login
header("Location: login.php");
exit();
?>