<?php
// Cek apakah pengguna sudah login
function sudah_login() {
    return isset($_SESSION['user_id']);
}

// Halaman khusus pelanggan yang sudah login
function wajib_login() {
    if (!sudah_login()) {
        redirect('login.php');
    }
}

// Halaman khusus admin
function wajib_admin() {
    if (!sudah_login() || $_SESSION['role'] !== 'admin') {
        redirect('admin/login.php');
    }
}