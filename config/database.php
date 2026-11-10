<?php
// Alamat website. Nama folder harus sama dengan folder di htdocs.
define('BASE_URL', 'http://localhost/bookstorerafi');

// Data koneksi database XAMPP (bawaan)
$host = 'localhost';
$db   = 'bookstore';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Koneksi database gagal: ' . $e->getMessage());
}