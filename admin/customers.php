<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/admin_guard.php';

$warna = [
    'Pending'    => 'warning text-dark',
    'Diproses'   => 'info text-dark',
    'Dikirim'    => 'primary',
    'Selesai'    => 'success',
    'Dibatalkan' => 'danger',
];

$id       = (int)($_GET['id'] ?? 0);
$q        = trim($_GET['q'] ?? '');
$customer = null;
$pesanan  = [];
$daftar   = [];

if ($id > 0) {
    // ===== Detail satu pelanggan + riwayat transaksinya =====
    $stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE id = ? AND role = 'customer'");
    $stmt->execute([$id]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$customer) {
        header('Location: customers.php');
        exit;
    }

    $stmt = $pdo->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC, id DESC');
    $stmt->execute([$id]);
    $pesanan = $stmt->fetchAll(PDO::FETCH_ASSOC);

} else {
    // ===== Daftar semua pelanggan =====
    $sql = "SELECT u.id, u.name, u.email,
                   COUNT(o.id) AS jumlah_pesanan,
                   COALESCE(SUM(CASE WHEN o.status <> 'Dibatalkan' THEN o.total END), 0) AS total_belanja
            FROM users u
            LEFT JOIN orders o ON o.user_id = u.id
            WHERE u.role = 'customer'";
    $params = [];

    if ($q !== '') {
        $sql .= ' AND (u.name LIKE ? OR u.email LIKE ?)';
        $params = ['%' . $q . '%', '%' . $q . '%'];
    }
    $sql .= ' GROUP BY u.id, u.name, u.email ORDER BY u.id DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $daftar = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$pageTitle = $customer ? 'Detail Pelanggan' : 'Pelanggan';
$active    = 'customers';
require_once __DIR__ . '/../includes/admin_header.php';
?>

<?php if ($customer): ?>
    <!-- ===== DETAIL PELANGGAN ===== -->
    <a href="customers.php" class="text-decoration-none"><i class="bi bi-arrow-left"></i> Kembali ke daftar pelanggan</a>

    <div class="card border-0 shadow-sm my-3">
        <div class="card-body">
            <h5 class="mb-3">Informasi Pelanggan</h5>
            <div class="row">
                <div class="col-md-4 mb-2">
                    <div class="text-muted small">Nama</div>
                    <div class="fw-semibold"><?= htmlspecialchars($customer['name']) ?></div>
                </div>
                <div class="col-md-4 mb-2">
                    <div class="text-muted small">Email</div>
                    <div class="fw-semibold"><?= htmlspecialchars($customer['email']) ?></div>
                </div>
                <div class="col-md-4 mb-2">
                    <div class="text-muted small">Jumlah Pesanan</div>
                    <div class="fw-semibold"><?= count($pesanan) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h6 class="mb-3">Riwayat Transaksi</h6>
            <?php if ($pesanan): ?>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>No.</th><th>Tanggal</th><th>Total</th><th>Status</th><th></th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pesanan as $o): ?>
                                <tr>
                                    <td>#<?= (int)$o['id'] ?></td>
                                    <td><?= date('d M Y, H:i', strtotime($o['created_at'])) ?></td>
                                    <td>Rp <?= number_format($o['total'], 0, ',', '.') ?></td>
                                    <td>
                                        <span class="badge bg-<?= $warna[$o['status']] ?? 'secondary' ?>">
                                            <?= htmlspecialchars($o['status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="orders.php?id=<?= (int)$o['id'] ?>" class="btn btn-sm btn-outline-dark">Detail</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted mb-0">Pelanggan ini belum pernah memesan.</p>
            <?php endif; ?>
        </div>
    </div>

<?php else: ?>
    <!-- ===== DAFTAR PELANGGAN ===== -->
    <form class="d-flex gap-2 mb-3" method="get">
        <input type="search" name="q" class="form-control" placeholder="Cari nama atau email..."
               value="<?= htmlspecialchars($q) ?>" style="max-width:350px;">
        <button class="btn btn-outline-dark"><i class="bi bi-search"></i></button>
        <?php if ($q !== ''): ?>
            <a href="customers.php" class="btn btn-outline-secondary">Reset</a>
        <?php endif; ?>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Jumlah Pesanan</th>
                        <th>Total Belanja</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($daftar as $c): ?>
                        <tr>
                            <td class="fw-semibold"><?= htmlspecialchars($c['name']) ?></td>
                            <td><?= htmlspecialchars($c['email']) ?></td>
                            <td><?= (int)$c['jumlah_pesanan'] ?></td>
                            <td>Rp <?= number_format($c['total_belanja'], 0, ',', '.') ?></td>
                            <td class="text-end">
                                <a href="customers.php?id=<?= (int)$c['id'] ?>" class="btn btn-sm btn-outline-dark">Detail</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$daftar): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">Tidak ada pelanggan ditemukan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
