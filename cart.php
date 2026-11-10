<?php
require_once __DIR__ . '/includes/init.php';

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$pesan = '';
$error = '';

// ===== Proses tombol (hapus / perbarui) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        $error = 'Permintaan tidak valid. Muat ulang halaman.';

    } elseif (isset($_POST['remove'])) {
        // Hapus satu buku dari keranjang
        unset($_SESSION['cart'][(int)$_POST['remove']]);
        $pesan = 'Buku dihapus dari keranjang.';

    } else {
        // Perbarui jumlah semua buku
        $cekStok = $pdo->prepare('SELECT stock FROM books WHERE id = ?');

        foreach ($_POST['qty'] ?? [] as $bookId => $qty) {
            $bookId = (int)$bookId;
            $qty    = (int)$qty;

            if (!isset($_SESSION['cart'][$bookId])) {
                continue;
            }

            $cekStok->execute([$bookId]);
            $stok = $cekStok->fetchColumn();

            if ($stok === false || $qty < 1) {
                unset($_SESSION['cart'][$bookId]);      // buku hilang atau jumlah 0
            } elseif ($qty > $stok) {
                $_SESSION['cart'][$bookId] = (int)$stok; // dibatasi sesuai stok
                $error = 'Sebagian jumlah disesuaikan dengan stok yang tersedia.';
            } else {
                $_SESSION['cart'][$bookId] = $qty;
            }
        }
        if ($error === '') {
            $pesan = 'Keranjang diperbarui.';
        }
    }
}

// ===== Ambil data buku untuk ditampilkan =====
$items = [];
$total = 0;
$cart  = $_SESSION['cart'] ?? [];

if ($cart) {
    $ids = array_keys($cart);
    $placeholder = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $pdo->prepare("SELECT * FROM books WHERE id IN ($placeholder)");
    $stmt->execute($ids);
    $ditemukan = [];

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $b) {
        $ditemukan[] = (int)$b['id'];
        $qty = $cart[$b['id']];

        if ($b['stock'] < 1) {                  // stok habis: keluarkan dari keranjang
            unset($_SESSION['cart'][$b['id']]);
            $error = 'Ada buku yang stoknya habis dan dikeluarkan dari keranjang.';
            continue;
        }
        if ($qty > $b['stock']) {               // jumlah melebihi stok: sesuaikan
            $qty = (int)$b['stock'];
            $_SESSION['cart'][$b['id']] = $qty;
            $error = 'Sebagian jumlah disesuaikan dengan stok yang tersedia.';
        }

        $b['qty']      = $qty;
        $b['subtotal'] = $qty * $b['price'];
        $total        += $b['subtotal'];
        $items[]       = $b;
    }

    // Buang buku yang sudah tidak ada di database
    foreach ($ids as $id) {
        if (!in_array((int)$id, $ditemukan, true)) {
            unset($_SESSION['cart'][$id]);
        }
    }
}

$pageTitle = 'Keranjang';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container py-4">
    <h3 class="text-navy mb-3">Keranjang Belanja</h3>

    <?php if ($pesan): ?>
        <div class="alert alert-success"><?= htmlspecialchars($pesan) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-warning"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($items): ?>
        <form method="post">
            <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">

            <div class="table-responsive bg-white shadow-sm rounded">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Buku</th>
                            <th>Harga</th>
                            <th style="width:110px;">Jumlah</th>
                            <th>Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $b): ?>
                            <tr>
                                <td>
                                    <a href="book_detail.php?id=<?= (int)$b['id'] ?>" class="fw-semibold text-decoration-none">
                                        <?= htmlspecialchars($b['title']) ?>
                                    </a>
                                    <div class="small text-muted"><?= htmlspecialchars($b['author']) ?></div>
                                </td>
                                <td>Rp <?= number_format($b['price'], 0, ',', '.') ?></td>
                                <td>
                                    <input type="number" name="qty[<?= (int)$b['id'] ?>]"
                                           value="<?= (int)$b['qty'] ?>" min="1" max="<?= (int)$b['stock'] ?>"
                                           class="form-control form-control-sm">
                                    <div class="small text-muted">Stok: <?= (int)$b['stock'] ?></div>
                                </td>
                                <td class="fw-semibold">Rp <?= number_format($b['subtotal'], 0, ',', '.') ?></td>
                                <td>
                                    <button type="submit" name="remove" value="<?= (int)$b['id'] ?>"
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Hapus buku ini dari keranjang?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2">
                <button type="submit" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-repeat"></i> Perbarui Keranjang
                </button>
                <div class="text-end">
                    <div class="text-muted">Total Pembayaran</div>
                    <div class="fs-4 fw-bold text-navy">Rp <?= number_format($total, 0, ',', '.') ?></div>
                </div>
            </div>
        </form>

        <div class="d-flex justify-content-between mt-4">
            <a href="books.php" class="btn btn-outline-dark">Lanjut Belanja</a>
            <a href="checkout.php" class="btn btn-navy">Checkout <i class="bi bi-arrow-right"></i></a>
        </div>

    <?php else: ?>
        <div class="text-center text-muted py-5">
            <i class="bi bi-cart-x display-3"></i>
            <p class="mt-2">Keranjangmu masih kosong.</p>
            <a href="books.php" class="btn btn-navy">Mulai Belanja</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>