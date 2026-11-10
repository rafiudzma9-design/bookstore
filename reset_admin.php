<?php
require_once 'config/database.php';

$email_admin   = 'admin@bookstore.com';
$password_baru = ''; // ubah sesuai keinginan Anda

$hash = password_hash($password_baru, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ? AND role = 'admin'");
$stmt->execute([$hash, $email_admin]);

if ($stmt->rowCount() > 0) {
    echo 'Password admin berhasil direset. Segera hapus file ini!';
} else {
    echo 'Tidak ada yang berubah. Cek kembali email admin.';
}