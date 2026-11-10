<?php
// Dipakai di setiap halaman admin: kalau belum login sebagai admin, kembali ke login
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}