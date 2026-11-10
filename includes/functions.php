<?php
// Menampilkan teks dengan aman (mencegah script jahat / XSS)
function e($teks) {
    return htmlspecialchars($teks ?? '', ENT_QUOTES, 'UTF-8');
}

// Mengubah angka jadi format rupiah: 85000 -> Rp 85.000
function rupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Pindah halaman
function redirect($url) {
    header('Location: ' . BASE_URL . '/' . $url);
    exit;
}

// Menghitung jumlah buku di keranjang (untuk badge di navbar)
function jumlah_keranjang() {
    if (empty($_SESSION['cart'])) {
        return 0;
    }
    return array_sum($_SESSION['cart']);
}