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

// Tanggal dianggap valid kalau berbentuk YYYY-MM-DD dan benar-benar ada di kalender
$tglValid = function ($s) {
    $d = DateTime::createFromFormat('Y-m-d', $s);
    return $d && $d->format('Y-m-d') === $s;
};

$dari   = $_GET['dari'] ?? '';
$sampai = $_GET['sampai'] ?? '';
$error  = '';

if ($dari !== '' && !$tglValid($dari))     { $dari = '';   $error = 'Format tanggal awal tidak valid.'; }
if ($sampai !== '' && !$tglValid($sampai)) { $sampai = ''; $error = 'Format tanggal akhir tidak valid.'; }
if ($dari !== '' && $sampai !== '' && $dari > $sampai) {
    $error  = 'Tanggal awal tidak boleh lebih besar dari tanggal akhir.';
    $dari   = '';
    $sampai = '';
}

// Susun syarat tanggal (dipakai semua query di bawah)
$where  = '1 = 1';
$params = [];
if ($dari !== '') {
    $where   .= ' AND o.created_at >= ?';
    $params[] = $dari . ' 00:00:00';
}
if ($sampai !== '') {
    $where   .= ' AND o.created_at <= ?';
    $params[] = $sampai . ' 23:59:59';
}

// ===== Ringkasan =====
$stmt = $pdo->prepare(
    "SELECT COUNT(*) AS total_transaksi,
            COALESCE(SUM(CASE WHEN o.status <> 'Dibatalkan' THEN 1 ELSE 0 END), 0) AS transaksi_sah,
            COALESCE(SUM(CASE WHEN o.status <> 'Dibatalkan' THEN o.total ELSE 0 END), 0) AS pendapatan
     FROM orders o WHERE $where"
);
$stmt->execute($params);
$ringkas = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare(
    "SELECT COALESCE(SUM(d.qty), 0)
     FROM order_details d JOIN orders o ON o.id = d.order_id
     WHERE $where AND o.status <> 'Dibatalkan'"
);
$stmt->execute($params);
$bukuTerjual = (int)$stmt->fetchColumn();

// ===== Rekap per status =====
$stmt = $pdo->prepare(
    "SELECT o.status, COUNT(*) AS jumlah, COALESCE(SUM(o.total), 0) AS nilai
     FROM orders o WHERE $where GROUP BY o.status"
);
$stmt->execute($params);
$perStatus = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ===== Rekap per hari (tanpa pesanan batal) =====
$stmt = $pdo->prepare(
    "SELECT DATE(o.created_at) AS tgl, COUNT(*) AS jumlah, SUM(o.total) AS nilai
     FROM orders o WHERE $where AND o.status <> 'Dibatalkan'
     GROUP BY DATE(o.created_at)
     ORDER BY tgl DESC LIMIT 31"
);
$stmt->execute($params);
$perHari = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ===== 5 buku terlaris =====
$stmt = $pdo->prepare(
    "SELECT d.book_title, SUM(d.qty) AS terjual, SUM(d.subtotal) AS omzet
     FROM order_details d JOIN orders o ON o.id = d.order_id
     WHERE $where AND o.status <> 'Dibatalkan'
     GROUP BY d.book_title
     ORDER BY terjual DESC, d.book_title LIMIT 5"
);
$stmt->execute($params);
$terlaris = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ===== Daftar transaksi =====
$stmt = $pdo->prepare(
    "SELECT o.id, o.created_at, o.recipient_name, o.total, o.status, u.name AS customer
     FROM orders o LEFT JOIN users u ON u.id = o.user_id
     WHERE $where
     ORDER BY o.created_at DESC, o.id DESC LIMIT 200"
);
$stmt->execute($params);
$transaksi = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Laporan Penjualan';
$active    = 'reports';
require_once __DIR__ . '/../includes/admin_header.php';
?>

<!-- Filter tanggal -->
<form class="row g-2 align-items-end mb-3" method="get">
    <div class="col-auto">
        <label class="form-label small mb-1">Dari tanggal</label>
        <input type="date" name="dari" class="form-control" value="<?= htmlspecialchars($dari) ?>">
    </div>
    <div class="col-auto">
        <label class="form-label small mb-1">Sampai tanggal</label>
        <input type="date" name="sampai" class="form-control" value="<?= htmlspecialchars($sampai) ?>">
    </div>
    <div class="col-auto">
        <button class="btn btn-navy"><i class="bi bi-funnel"></i> Tampilkan</button>
        <?php if ($dari !== '' || $sampai !== ''): ?>
            <a href="reports.php" class="btn btn-outline-secondary">Reset</a>
        <?php endif; ?>
    </div>
</form>

<?php if ($error): ?>
    <div class="alert alert-warning"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<p class="text-muted small">
    Periode:
    <strong><?= $dari !== '' ? date('d M Y', strtotime($dari)) : 'awal' ?></strong> s/d
    <strong><?= $sampai !== '' ? date('d M Y', strtotime($sampai)) : 'sekarang' ?></strong>.
    Pendapatan tidak menghitung pesanan yang dibatalkan.
</p>

<!-- Kartu ringkasan -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100"><div class="card-body">
            <div class="text-muted small">Total Pendapatan</div>
            <div class="fs-5 fw-bold">Rp <?= number_format($ringkas['pendapatan'], 0, ',', '.') ?></div>
        </div></div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100"><div class="card-body">
            <div class="text-muted small">Jumlah Transaksi</div>
            <div class="fs-5 fw-bold"><?= (int)$ringkas['total_transaksi'] ?></div>
        </div></div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100"><div class="card-body">
            <div class="text-muted small">Transaksi Sah (tidak batal)</div>
            <div class="fs-5 fw-bold"><?= (int)$ringkas['transaksi_sah'] ?></div>
        </div></div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100"><div class="card-body">
            <div class="text-muted small">Buku Terjual</div>
            <div class="fs-5 fw-bold"><?= $bukuTerjual ?></div>
        </div></div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Rekap per status -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100"><div class="card-body">
            <h6 class="mb-3">Rekap per Status</h6>
            <?php if ($perStatus): ?>
                <table class="table table-sm align-middle mb-0">
                    <?php foreach ($perStatus as $s): ?>
                        <tr>
                            <td><span class="badge bg-<?= $warna[$s['status']] ?? 'secondary' ?>"><?= htmlspecialchars($s['status']) ?></span></td>
                            <td><?= (int)$s['jumlah'] ?> pesanan</td>
                            <td class="text-end">Rp <?= number_format($s['nilai'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p class="text-muted mb-0">Belum ada data.</p>
            <?php endif; ?>
        </div></div>
    </div>

    <!-- Buku terlaris -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100"><div class="card-body">
            <h6 class="mb-3">5 Buku Terlaris</h6>
            <?php if ($terlaris): ?>
                <table class="table table-sm align-middle mb-0">
                    <?php foreach ($terlaris as $t): ?>
                        <tr>
                            <td><?= htmlspecialchars($t['book_title']) ?></td>
                            <td class="text-end text-nowrap"><?= (int)$t['terjual'] ?> terjual</td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p class="text-muted mb-0">Belum ada penjualan.</p>
            <?php endif; ?>
        </div></div>
    </div>

    <!-- Rekap per hari -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100"><div class="card-body">
            <h6 class="mb-3">Rekap per Hari</h6>
            <?php if ($perHari): ?>
                <table class="table table-sm align-middle mb-0">
                    <?php foreach ($perHari as $h): ?>
                        <tr>
                            <td><?= date('d M Y', strtotime($h['tgl'])) ?></td>
                            <td><?= (int)$h['jumlah'] ?> pesanan</td>
                            <td class="text-end">Rp <?= number_format($h['nilai'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p class="text-muted mb-0">Belum ada data.</p>
            <?php endif; ?>
        </div></div>
    </div>
</div>

<!-- Daftar transaksi -->
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <h6 class="mb-3">Daftar Transaksi</h6>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr><th>No.</th><th>Tanggal</th><th>Pelanggan</th><th>Penerima</th><th>Total</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($transaksi as $o): ?>
                        <tr>
                            <td><a href="orders.php?id=<?= (int)$o['id'] ?>">#<?= (int)$o['id'] ?></a></td>
                            <td><?= date('d M Y, H:i', strtotime($o['created_at'])) ?></td>
                            <td><?= htmlspecialchars($o['customer'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($o['recipient_name']) ?></td>
                            <td>Rp <?= number_format($o['total'], 0, ',', '.') ?></td>
                            <td>
                                <span class="badge bg-<?= $warna[$o['status']] ?? 'secondary' ?>">
                                    <?= htmlspecialchars($o['status']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$transaksi): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada transaksi pada periode ini.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (count($transaksi) === 200): ?>
            <small class="text-muted">Menampilkan 200 transaksi terbaru. Persempit tanggal untuk melihat yang lain.</small>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
