<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        // Ubah path redirect
        header("Location: ../login.php");
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        // Ubah path redirect
        header("Location: ../index.php");
        exit();
    }
}

function redirectBasedOnRole() {
    $base_path = "/perpustakaan-digital"; // Sesuaikan dengan nama folder proyek Anda jika ada
    if (isAdmin()) {
        header("Location: $base_path/admin/dashboard.php");
    } else {
        header("Location: $base_path/user/beranda.php");
    }
    exit();
}
?>