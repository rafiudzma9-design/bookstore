<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/admin_guard.php';

// ===== Statistik =====
$totalBuku      = (int)$pdo->query('SELECT COUNT(*) FROM books')->fetchColumn();
$totalKategori  = (int)$pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
$totalPelanggan = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
$totalTransaksi = (int)$pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$pendapatan     = (int)$pdo->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status <> 'Dibatalkan'")->fetchColumn();

// ===== 5 pesanan terbaru =====
$terbaru = $pdo->query(
    'SELECT id, recipient_name, total, status, created_at
     FROM orders
     ORDER BY created_at DESC, id DESC
     LIMIT 5'
)->fetchAll(PDO::FETCH_ASSOC);

// ===== Buku yang stoknya menipis atau habis =====
$stokTipis = $pdo->query(
    'SELECT id, title, stock FROM books WHERE stock <= 5 ORDER BY stock ASC, title LIMIT 5'
)->fetchAll(PDO::FETCH_ASSOC);

// Warna label status
$warna = [
    'Pending'    => 'warning text-dark',
    'Diproses'   => 'info text-dark',
    'Dikirim'    => 'primary',
    'Selesai'    => 'success',
    'Dibatalkan' => 'danger',
];

// Kartu statistik: [judul, nilai, ikon, tautan]
$kartu = [
    ['Total Buku',      $totalBuku,                                   'bi-book',     'books.php'],
    ['Total Kategori',  $totalKategori,                               'bi-tags',     'categories.php'],
    ['Total Pelanggan', $totalPelanggan,                              'bi-people',   'customers.php'],
    ['Total Transaksi', $totalTransaksi,                              'bi-receipt',  'orders.php'],
    ['Total Pendapatan', 'Rp ' . number_format($pendapatan, 0, ',', '.'), 'bi-cash-coin', 'reports.php'],
];

$pageTitle = 'Dashboard';
$active    = 'dashboard';
require_once __DIR__ . '/../includes/admin_header.php';
?>

<!-- Kartu statistik -->
<div class="row g-3 mb-4">
    <?php foreach ($kartu as [$judul, $nilai, $ikon, $url]): ?>
        <div class="col-6 col-lg">
            <a href="<?= $url ?>" class="card border-0 shadow-sm text-decoration-none h-100">
                <div class="card-body">
                    <i class="bi <?= $ikon ?> fs-3 text-navy"></i>
                    <div class="text-muted small mt-2"><?= $judul ?></div>
                    <div class="fs-5 fw-bold text-dark"><?= $nilai ?></div>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-4">
    <!-- Pesanan terbaru -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <h6 class="mb-0">Pesanan Terbaru</h6>
                    <a href="orders.php" class="small">Lihat semua</a>
                </div>
                <?php if ($terbaru): ?>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>No.</th><th>Penerima</th><th>Total</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($terbaru as $o): ?>
                                    <tr>
                                        <td>#<?= (int)$o['id'] ?></td>
                                        <td><?= htmlspecialchars($o['recipient_name']) ?></td>
                                        <td>Rp <?= number_format($o['total'], 0, ',', '.') ?></td>
                                        <td>
                                            <span class="badge bg-<?= $warna[$o['status']] ?? 'secondary' ?>">
                                                <?= htmlspecialchars($o['status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">Belum ada pesanan.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Stok menipis -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <h6 class="mb-0">Stok Menipis</h6>
                    <a href="books.php" class="small">Kelola stok</a>
                </div>
                <?php if ($stokTipis): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($stokTipis as $b): ?>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span><?= htmlspecialchars($b['title']) ?></span>
                                <?php if ($b['stock'] == 0): ?>
                                    <span class="badge bg-danger">Habis</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Sisa <?= (int)$b['stock'] ?></span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted mb-0">Semua stok aman.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>