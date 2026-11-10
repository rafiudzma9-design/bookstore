<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/admin_guard.php';

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$statuses = ['Pending', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan'];
$warna = [
    'Pending'    => 'warning text-dark',
    'Diproses'   => 'info text-dark',
    'Dikirim'    => 'primary',
    'Selesai'    => 'success',
    'Dibatalkan' => 'danger',
];

// ===== Ubah status pesanan =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id   = (int)($_POST['id'] ?? 0);
    $baru = $_POST['status'] ?? '';

    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        $_SESSION['flash'] = ['danger', 'Permintaan tidak valid. Muat ulang halaman.'];

    } elseif (!in_array($baru, $statuses, true)) {
        $_SESSION['flash'] = ['danger', 'Status tidak valid.'];

    } else {
        try {
            $pdo->beginTransaction();

            $s = $pdo->prepare('SELECT status FROM orders WHERE id = ? FOR UPDATE');
            $s->execute([$id]);
            $lama = $s->fetchColumn();

            if ($lama === false) {
                throw new RuntimeException('Pesanan tidak ditemukan.');
            }

            if ($lama !== $baru) {
                if ($baru === 'Dibatalkan') {
                    // Pesanan dibatalkan: stok buku dikembalikan
                    $pdo->prepare(
                        'UPDATE books b JOIN order_details d ON d.book_id = b.id
                         SET b.stock = b.stock + d.qty WHERE d.order_id = ?'
                    )->execute([$id]);

                } elseif ($lama === 'Dibatalkan') {
                    // Pesanan diaktifkan lagi: stok harus cukup, lalu dikurangi lagi
                    $cek = $pdo->prepare(
                        'SELECT d.book_title FROM order_details d
                         JOIN books b ON b.id = d.book_id
                         WHERE d.order_id = ? AND b.stock < d.qty LIMIT 1'
                    );
                    $cek->execute([$id]);
                    $kurang = $cek->fetchColumn();
                    if ($kurang !== false) {
                        throw new RuntimeException('Stok buku "' . $kurang . '" tidak cukup untuk mengaktifkan kembali pesanan.');
                    }
                    $pdo->prepare(
                        'UPDATE books b JOIN order_details d ON d.book_id = b.id
                         SET b.stock = b.stock - d.qty WHERE d.order_id = ?'
                    )->execute([$id]);
                }

                $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute([$baru, $id]);
            }

            $pdo->commit();
            $_SESSION['flash'] = ['success', 'Status pesanan berhasil diperbarui.'];

        } catch (RuntimeException $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            $_SESSION['flash'] = ['danger', $e->getMessage()];
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            $_SESSION['flash'] = ['danger', 'Gagal memperbarui status. Coba lagi.'];
        }
    }

    header('Location: orders.php?id=' . $id);
    exit;
}

// ===== Ambil data =====
$id     = (int)($_GET['id'] ?? 0);
$order  = null;
$detail = [];
$daftar = [];
$fStatus = $_GET['status'] ?? '';
$q       = trim($_GET['q'] ?? '');

if ($id > 0) {
    // Detail satu pesanan
    $stmt = $pdo->prepare(
        'SELECT o.*, u.name AS customer, u.email
         FROM orders o LEFT JOIN users u ON u.id = o.user_id
         WHERE o.id = ?'
    );
    $stmt->execute([$id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        header('Location: orders.php');
        exit;
    }

    $stmt = $pdo->prepare('SELECT * FROM order_details WHERE order_id = ? ORDER BY id');
    $stmt->execute([$id]);
    $detail = $stmt->fetchAll(PDO::FETCH_ASSOC);

} else {
    // Daftar semua pesanan (bisa difilter)
    $sql = 'SELECT o.*, u.name AS customer
            FROM orders o LEFT JOIN users u ON u.id = o.user_id
            WHERE 1 = 1';
    $params = [];

    if (in_array($fStatus, $statuses, true)) {
        $sql .= ' AND o.status = ?';
        $params[] = $fStatus;
    } else {
        $fStatus = '';
    }

    if ($q !== '') {
        $sql .= ' AND (o.recipient_name LIKE ? OR u.name LIKE ?';
        $params[] = '%' . $q . '%';
        $params[] = '%' . $q . '%';
        if (ctype_digit($q)) {                 // angka = boleh cari berdasarkan nomor pesanan
            $sql .= ' OR o.id = ?';
            $params[] = (int)$q;
        }
        $sql .= ')';
    }
    $sql .= ' ORDER BY o.created_at DESC, o.id DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $daftar = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$pageTitle = $order ? 'Detail Pesanan #' . (int)$order['id'] : 'Transaksi';
$active    = 'orders';
require_once __DIR__ . '/../includes/admin_header.php';
?>

<?php if ($flash): ?>
    <div class="alert alert-<?= $flash[0] ?>"><?= htmlspecialchars($flash[1]) ?></div>
<?php endif; ?>

<?php if ($order): ?>
    <!-- ===== DETAIL PESANAN ===== -->
    <a href="orders.php" class="text-decoration-none"><i class="bi bi-arrow-left"></i> Kembali ke daftar transaksi</a>

    <div class="row g-4 mt-1">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="mb-3">Ubah Status</h6>
                    <form method="post" class="d-flex gap-2">
                        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
                        <input type="hidden" name="id" value="<?= (int)$order['id'] ?>">
                        <select name="status" class="form-select">
                            <?php foreach ($statuses as $st): ?>
                                <option value="<?= $st ?>" <?= $order['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-navy">Simpan</button>
                    </form>
                    <small class="text-muted d-block mt-2">
                        Membatalkan pesanan akan mengembalikan stok buku secara otomatis.
                    </small>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="mb-3">Info Pesanan</h6>
                    <p class="mb-1 text-muted small">Status</p>
                    <p><span class="badge bg-<?= $warna[$order['status']] ?? 'secondary' ?>"><?= htmlspecialchars($order['status']) ?></span></p>
                    <p class="mb-1 text-muted small">Tanggal</p>
                    <p><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></p>
                    <p class="mb-1 text-muted small">Akun Pelanggan</p>
                    <p>
                        <?php if ($order['customer']): ?>
                            <a href="customers.php?id=<?= (int)$order['user_id'] ?>">
                                <?= htmlspecialchars($order['customer']) ?>
                            </a>
                            (<?= htmlspecialchars($order['email']) ?>)
                        <?php else: ?>-<?php endif; ?>
                    </p>
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
                    <h6 class="mb-3">Buku yang Dibeli</h6>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>Judul</th><th>Harga</th><th>Jumlah</th><th class="text-end">Subtotal</th></tr>
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
                                    <th class="text-end text-navy">Rp <?= number_format($order['total'], 0, ',', '.') ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- ===== DAFTAR TRANSAKSI ===== -->
    <form class="row g-2 mb-3" method="get">
        <div class="col-md-4">
            <input type="search" name="q" class="form-control" placeholder="Cari no. pesanan, penerima, atau pelanggan..."
                   value="<?= htmlspecialchars($q) ?>">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">Semua status</option>
                <?php foreach ($statuses as $st): ?>
                    <option value="<?= $st ?>" <?= $fStatus === $st ? 'selected' : '' ?>><?= $st ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto">
            <button class="btn btn-outline-dark"><i class="bi bi-funnel"></i> Filter</button>
            <?php if ($q !== '' || $fStatus !== ''): ?>
                <a href="orders.php" class="btn btn-outline-secondary">Reset</a>
            <?php endif; ?>
        </div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No.</th><th>Tanggal</th><th>Pelanggan</th><th>Penerima</th>
                        <th>Total</th><th>Status</th><th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($daftar as $o): ?>
                        <tr>
                            <td class="fw-semibold">#<?= (int)$o['id'] ?></td>
                            <td><?= date('d M Y, H:i', strtotime($o['created_at'])) ?></td>
                            <td><?= htmlspecialchars($o['customer'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($o['recipient_name']) ?></td>
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

                    <?php if (!$daftar): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada transaksi ditemukan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
