<?php
// ============================================
// FILE: auth.php
// Fungsi Bantuan Autentikasi & Role Guard
// ============================================

function require_login() {
    if (!isset($_SESSION["user_id"])) {
        header("Location: login.php");
        exit();
    }
}

function require_admin() {
    require_login();
    if (($_SESSION["role"] ?? "") !== "admin") {
        header("Location: index.php");
        exit();
    }
}

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