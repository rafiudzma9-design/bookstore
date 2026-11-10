<?php
// Variabel dari halaman yang memanggil: $pageTitle (judul tab) dan $active (menu yang menyala)
$active = $active ?? '';

// Daftar menu sidebar: kunci => [alamat, ikon, tulisan]
$menu = [
    'dashboard'  => ['dashboard.php',  'bi-speedometer2', 'Dashboard'],
    'books'      => ['books.php',      'bi-book',         'Buku & Stok'],
    'categories' => ['categories.php', 'bi-tags',         'Kategori'],
    'customers'  => ['customers.php',  'bi-people',       'Pelanggan'],
    'orders'     => ['orders.php',     'bi-receipt',      'Transaksi'],
    'reports'    => ['reports.php',    'bi-bar-chart',    'Laporan'],
    'messages'   => ['messages.php',   'bi-envelope',     'Pesan Masuk'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle ?? 'Admin') ?> - Admin Bookstore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="d-lg-flex min-vh-100">

    <!-- Sidebar -->
    <aside class="admin-sidebar bg-navy text-white">
        <div class="d-flex justify-content-between align-items-center p-3">
            <a href="dashboard.php" class="text-white fw-bold text-decoration-none">
                <i class="bi bi-book"></i> Admin Bookstore
            </a>
            <button class="btn btn-sm btn-outline-light d-lg-none" type="button"
                    data-bs-toggle="collapse" data-bs-target="#adminMenu">
                <i class="bi bi-list"></i>
            </button>
        </div>

        <div class="collapse d-lg-block" id="adminMenu">
            <nav class="nav flex-column px-2 pb-3">
                <?php foreach ($menu as $key => [$url, $icon, $label]): ?>
                    <a class="nav-link <?= $active === $key ? 'active' : '' ?>" href="<?= $url ?>">
                        <i class="bi <?= $icon ?> me-2"></i><?= $label ?>
                    </a>
                <?php endforeach; ?>

                <hr class="border-secondary">
                <a class="nav-link" href="../index.php" target="_blank">
                    <i class="bi bi-box-arrow-up-right me-2"></i>Lihat Website
                </a>
                <a class="nav-link" href="logout.php">
                    <i class="bi bi-box-arrow-left me-2"></i>Logout
                </a>
            </nav>
        </div>
    </aside>

    <!-- Isi halaman -->
    <main class="admin-main flex-grow-1 p-3 p-lg-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="text-navy mb-0"><?= htmlspecialchars($pageTitle ?? 'Admin') ?></h4>
            <span class="text-muted small">
                <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['admin_name'] ?? '') ?>
            </span>
        </div>