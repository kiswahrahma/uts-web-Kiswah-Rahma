<?php
// ============================================
// FILE: auth.php
// Fungsi bantuan untuk cek login & role user.
// Panggil session_start() dan include "config.php"
// SEBELUM include file ini.
// ============================================

// Wajib login (admin ATAU pelanggan), kalau belum -> lempar ke login
function require_login() {
    if (!isset($_SESSION["user_id"])) {
        header("Location: login.php");
        exit();
    }
}

// Halaman khusus ADMIN. Pelanggan yang nyasar ke sini dilempar ke menu pelanggan.
function require_admin() {
    require_login();
    if (($_SESSION["role"] ?? "") !== "admin") {
        header("Location: index.php");
        exit();
    }
}

// Halaman khusus PELANGGAN. Admin yang nyasar ke sini dilempar ke dashboard.
function require_pelanggan() {
    require_login();
    $role = $_SESSION["role"] ?? "";
    if ($role !== "pelanggan" && $role !== "user") {
        header("Location: dashboard.php");
        exit();
    }
}

function is_login() {
    return isset($_SESSION["user_id"]);
}

function is_admin() {
    return is_login() && ($_SESSION["role"] ?? "") === "admin";
}

function is_pelanggan() {
    if (!is_login()) {
        return false;
    }
    $role = $_SESSION["role"] ?? "";
    return $role === "pelanggan" || $role === "user";
}
?>
