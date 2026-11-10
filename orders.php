<?php
require_once __DIR__ . '/includes/init.php';

// Wajib login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$userId = (int)$_SESSION['user_id'];

// Warna label untuk tiap status
$warna = [
    'Pending'    => 'warning text-dark',
    'Diproses'   => 'info text-dark',
    'Dikirim'    => 'primary',
    'Selesai'    => 'success',
    'Dibatalkan' => 'danger',
];

$id     = (int)($_GET['id'] ?? 0);
$order  = null;
$detail = [];
$daftar = [];

if ($id > 0) {
    // ===== Detail satu pesanan (hanya milik sendiri) =====
    $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $userId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        header('Location: orders.php');
        exit;
    }

    $stmt = $pdo->prepare('SELECT * FROM order_details WHERE order_id = ? ORDER BY id');
    $stmt->execute([$id]);
    $detail = $stmt->fetchAll(PDO::FETCH_ASSOC);

} else {
    // ===== Daftar semua pesanan milik sendiri =====
    $stmt = $pdo->prepare(
        'SELECT o.*, COALESCE(SUM(d.qty), 0) AS jumlah_buku
         FROM orders o
         LEFT JOIN order_details d ON d.order_id = o.id
         WHERE o.user_id = ?
         GROUP BY o.id
         ORDER BY o.created_at DESC, o.id DESC'
    );
    $stmt->execute([$userId]);
    $daftar = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$pageTitle = 'Pesanan Saya';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container py-4">

<?php if ($order): ?>
    <!-- ===== DETAIL PESANAN ===== -->
    <a href="orders.php" class="text-decoration-none">
        <i class="bi bi-arrow-left"></i> Kembali ke daftar pesanan
    </a>

    <div class="d-flex flex-wrap justify-content-between align-items-center my-3">
        <h3 class="text-navy mb-0">Pesanan #<?= (int)$order['id'] ?></h3>
        <span class="badge bg-<?= $warna[$order['status']] ?? 'secondary' ?> fs-6">
            <?= htmlspecialchars($order['status']) ?>
        </span>
    </div>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">Info Pengiriman</h5>
                    <p class="mb-1 text-muted small">Tanggal</p>
                    <p><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></p>
                    <p class="mb-1 text-muted small">Penerima</p>
                    <p><?= htmlspecialchars($order['recipient_name']) ?></p>
                    <p class="mb-1 text-muted small">Telepon</p>
                    <p><?= htmlspecialchars($order['phone']) ?></p>
                    <p class="mb-1 text-muted small">Alamat</p>
                    <p><?= nl2br(htmlspecialchars($order['address'])) ?></p>
                    <p class="mb-1 text-muted small">Pembayaran</p>
                    <p class="mb-0"><?= htmlspecialchars($order['payment_method']) ?></p>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">Buku yang Dibeli</h5>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Judul</th>
                                    <th>Harga</th>
                                    <th>Jumlah</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($detail as $d): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($d['book_title']) ?></td>
                                        <td>Rp <?= number_format($d['price'], 0, ',', '.') ?></td>
                                        <td><?= (int)$d['qty'] ?></td>
                                        <td class="text-end">Rp <?= number_format($d['subtotal'], 0, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">Total</th>
                                    <th class="text-end text-navy">
                                        Rp <?= number_format($order['total'], 0, ',', '.') ?>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- ===== DAFTAR PESANAN ===== -->
    <h3 class="text-navy mb-3">Pesanan Saya</h3>

    <?php if ($daftar): ?>
        <div class="table-responsive bg-white shadow-sm rounded">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No. Pesanan</th>
                        <th>Tanggal</th>
                        <th>Jumlah Buku</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($daftar as $o): ?>
                        <tr>
                            <td class="fw-semibold">#<?= (int)$o['id'] ?></td>
                            <td><?= date('d M Y, H:i', strtotime($o['created_at'])) ?></td>
                            <td><?= (int)$o['jumlah_buku'] ?></td>
                            <td>Rp <?= number_format($o['total'], 0, ',', '.') ?></td>
                            <td>
                                <span class="badge bg-<?= $warna[$o['status']] ?? 'secondary' ?>">
                                    <?= htmlspecialchars($o['status']) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="orders.php?id=<?= (int)$o['id'] ?>" class="btn btn-sm btn-outline-dark">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
                    </div>
    <?php else: ?>
        <div class="text-center text-muted py-5">
            <i class="bi bi-receipt display-3"></i>
            <p class="mt-2">Kamu belum punya pesanan.</p>
            <a href="books.php" class="btn btn-navy">Mulai Belanja</a>
        </div>
    <?php endif; ?>
<?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>