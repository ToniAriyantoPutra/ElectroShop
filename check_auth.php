<?php
session_start();

// Daftar halaman yang hanya bisa diakses admin
$admin_only_pages = ['users.php', 'backup.php'];

// Daftar halaman yang bisa diakses semua role
$public_pages = ['index.php', 'login.php', 'logout.php', 'laporan.php'];

// Cek jika user belum login
if (!isset($_SESSION['user_id']) && !in_array(basename($_SERVER['PHP_SELF']), ['login.php'])) {
    header('Location: login.php');
    exit();
}

// Cek jika user mencoba akses halaman admin
if (in_array(basename($_SERVER['PHP_SELF']), $admin_only_pages) && $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// Cek jika user sudah login tapi mencoba akses halaman login
if (isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) === 'login.php') {
    header('Location: index.php');
    exit();
}
?>