<?php
require_once __DIR__ . '/includes/init.php';

// Wajib login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$userId = (int)$_SESSION['user_id'];

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$error = '';
$order = null;

// ===== Halaman sukses (setelah pesanan tersimpan) =====
if (isset($_GET['done'])) {
    $stmt = $pdo->prepare('SELECT id, total FROM orders WHERE id = ? AND user_id = ?');
    $stmt->execute([(int)$_GET['done'], $userId]);   // hanya pesanan milik sendiri
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$order) {
        header('Location: index.php');
        exit;
    }
}

$items = [];
$total = 0;
$nama = $_SESSION['user_name'] ?? '';
$telp = '';
$alamat = '';

if (!$order) {
    // ===== Ambil isi keranjang =====
    $cart = $_SESSION['cart'] ?? [];
    if (!$cart) {
        header('Location: cart.php');
        exit;
    }

    $ids = array_keys($cart);
    $placeholder = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM books WHERE id IN ($placeholder)");
    $stmt->execute($ids);

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $b) {
        $qty = (int)$cart[$b['id']];
        if ($b['stock'] < $qty) {            // stok tidak cukup: kembali ke keranjang
            header('Location: cart.php');
            exit;
        }
        $b['qty']      = $qty;
        $b['subtotal'] = $qty * $b['price'];
        $total        += $b['subtotal'];
        $items[]       = $b;
    }
    if (count($items) !== count($cart)) {    // ada buku yang sudah dihapus dari database
        header('Location: cart.php');
        exit;
    }

    // ===== Proses simpan pesanan =====
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nama   = trim($_POST['recipient_name'] ?? '');
        $telp   = trim($_POST['phone'] ?? '');
        $alamat = trim($_POST['address'] ?? '');

        if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
            $error = 'Permintaan tidak valid. Muat ulang halaman.';
        } elseif ($nama === '' || $alamat === '') {
            $error = 'Nama penerima dan alamat wajib diisi.';
        } elseif (!preg_match('/^[0-9+\- ]{8,20}$/', $telp)) {
            $error = 'Nomor telepon tidak valid (8-20 angka).';
        } else {
            try {
                $pdo->beginTransaction();

                // Kunci baris buku lalu cek stok sekali lagi
                $kunci  = $pdo->prepare('SELECT id, title, price, stock FROM books WHERE id = ? FOR UPDATE');
                $baris  = [];
                $hitung = 0;

                foreach ($cart as $bookId => $qty) {
                    $kunci->execute([(int)$bookId]);
                    $b = $kunci->fetch(PDO::FETCH_ASSOC);

                    if (!$b || $b['stock'] < $qty) {
                        throw new RuntimeException('Stok buku tidak cukup. Silakan cek keranjang kamu.');
                    }
                    $sub     = $qty * $b['price'];
                    $hitung += $sub;
                    $baris[] = ['id' => $b['id'], 'title' => $b['title'], 'price' => $b['price'], 'qty' => $qty, 'sub' => $sub];
                }

                // 1. Simpan data pesanan
                $pdo->prepare(
                    'INSERT INTO orders (user_id, recipient_name, address, phone, total, payment_method, status)
                     VALUES (?, ?, ?, ?, ?, ?, ?)'
                )->execute([$userId, $nama, $alamat, $telp, $hitung, 'COD', 'Pending']);
                $orderId = (int)$pdo->lastInsertId();

                // 2. Simpan rincian buku + 3. kurangi stok
                $simpan = $pdo->prepare(
                    'INSERT INTO order_details (order_id, book_id, book_title, price, qty, subtotal)
                     VALUES (?, ?, ?, ?, ?, ?)'
                );
                $kurang = $pdo->prepare('UPDATE books SET stock = stock - ? WHERE id = ? AND stock >= ?');

                foreach ($baris as $r) {
                    $simpan->execute([$orderId, $r['id'], $r['title'], $r['price'], $r['qty'], $r['sub']]);
                    $kurang->execute([$r['qty'], $r['id'], $r['qty']]);
                    if ($kurang->rowCount() !== 1) {
                        throw new RuntimeException('Stok buku tidak cukup. Silakan cek keranjang kamu.');
                    }
                }

                $pdo->commit();                 // semua berhasil: simpan permanen
                $_SESSION['cart'] = [];         // kosongkan keranjang
                header('Location: checkout.php?done=' . $orderId);
                exit;

            } catch (PDOException $e) {
                if ($pdo->inTransaction()) { $pdo->rollBack(); }
                $error = 'Terjadi kesalahan saat menyimpan pesanan. Coba lagi.';
            } catch (RuntimeException $e) {
                if ($pdo->inTransaction()) { $pdo->rollBack(); }
                $error = $e->getMessage();
            }
        }
    }
}

$pageTitle = 'Checkout';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container py-4">

<?php if ($order): ?>
    <!-- Pesanan berhasil -->
    <div class="text-center py-5">
        <i class="bi bi-check-circle text-success display-3"></i>
        <h3 class="text-navy mt-3">Pesanan Berhasil Dibuat</h3>
        <p class="text-muted mb-1">Nomor pesanan: <strong>#<?= (int)$order['id'] ?></strong></p>
        <p class="text-muted">Total bayar (COD): <strong>Rp <?= number_format($order['total'], 0, ',', '.') ?></strong></p>
        <a href="orders.php" class="btn btn-navy">Lihat Pesanan Saya</a>
        <a href="books.php" class="btn btn-outline-dark">Lanjut Belanja</a>
    </div>

<?php else: ?>
    <h3 class="text-navy mb-3">Checkout</h3>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error) ?>
            <a href="cart.php" class="alert-link">Kembali ke keranjang</a>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Form pengiriman -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">Data Penerima</h5>
                    <form method="post">
                        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">

                        <div class="mb-3">
                            <label class="form-label">Nama Penerima</label>
                            <input type="text" name="recipient_name" class="form-control"
                                   value="<?= htmlspecialchars($nama) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nomor Telepon</label>
                            <input type="text" name="phone" class="form-control"
                                   value="<?= htmlspecialchars($telp) ?>" placeholder="08xxxxxxxxxx" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat Pengiriman</label>
                            <textarea name="address" rows="3" class="form-control" required><?= htmlspecialchars($alamat) ?></textarea>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Metode Pembayaran</label>
                            <input type="text" class="form-control" value="COD (Bayar di Tempat)" disabled>
                        </div>

                        <button type="submit" class="btn btn-navy w-100"
                                onclick="return confirm('Buat pesanan sekarang?')">
                            Konfirmasi Pesanan
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Ringkasan pesanan -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">Ringkasan Pesanan</h5>
                    <?php foreach ($items as $b): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <div>
                                <div class="fw-semibold"><?= htmlspecialchars($b['title']) ?></div>
                                <small class="text-muted">
                                    <?= (int)$b['qty'] ?> x Rp <?= number_format($b['price'], 0, ',', '.') ?>
                                </small>
                            </div>
                            <div>Rp <?= number_format($b['subtotal'], 0, ',', '.') ?></div>
                        </div>
                    <?php endforeach; ?>
                    <hr>
                    <div class="d-flex justify-content-between fs-5 fw-bold text-navy">
                        <span>Total</span>
                        <span>Rp <?= number_format($total, 0, ',', '.') ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>