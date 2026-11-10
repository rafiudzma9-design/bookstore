<?php
require_once __DIR__ . '/includes/init.php';

// Ambil id buku dari URL, paksa jadi angka
$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare(
    'SELECT b.*, c.name AS category
     FROM books b
     LEFT JOIN categories c ON c.id = b.category_id
     WHERE b.id = ?'
);
$stmt->execute([$id]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

// Kalau buku tidak ada, kembali ke katalog
if (!$book) {
    header('Location: books.php');
    exit;
}

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$error = '';

// Proses tombol "Tambah ke Keranjang"
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $qty      = max(1, (int)($_POST['qty'] ?? 1));
    $sudahAda = $_SESSION['cart'][$id] ?? 0;

    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        $error = 'Permintaan tidak valid. Muat ulang halaman.';
    } elseif ($book['stock'] < 1) {
        $error = 'Maaf, stok buku ini habis.';
    } elseif ($sudahAda + $qty > $book['stock']) {
        $error = "Stok tidak cukup. Di keranjang sudah ada $sudahAda, stok tersedia {$book['stock']}.";
    } else {
        $_SESSION['cart'][$id] = $sudahAda + $qty;
        header('Location: book_detail.php?id=' . $id . '&added=1');
        exit;
    }
}

// Cover
$file     = !empty($book['cover']) ? 'uploads/covers/' . basename($book['cover']) : '';
$adaCover = $file !== '' && file_exists(__DIR__ . '/' . $file);

$pageTitle = $book['title'];
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container py-4">
    <nav class="mb-3">
        <a href="books.php" class="text-decoration-none"><i class="bi bi-arrow-left"></i> Kembali ke katalog</a>
    </nav>

    <?php if (isset($_GET['added'])): ?>
        <div class="alert alert-success">
            Buku berhasil ditambahkan ke keranjang.
            <a href="cart.php" class="alert-link">Lihat keranjang</a>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Cover -->
        <div class="col-md-4">
            <?php if ($adaCover): ?>
                <img src="<?= htmlspecialchars($file) ?>" class="book-cover rounded shadow-sm"
                     alt="<?= htmlspecialchars($book['title']) ?>">
            <?php else: ?>
                <div class="book-cover rounded shadow-sm d-flex align-items-center justify-content-center text-secondary">
                    <i class="bi bi-book display-1"></i>
                </div>
            <?php endif; ?>
        </div>

        <!-- Informasi buku -->
        <div class="col-md-8">
            <span class="badge bg-navy mb-2"><?= htmlspecialchars($book['category'] ?? 'Tanpa kategori') ?></span>
            <h2 class="text-navy"><?= htmlspecialchars($book['title']) ?></h2>
            <p class="text-muted">Penulis: <?= htmlspecialchars($book['author']) ?></p>

            <h3 class="fw-bold text-navy mb-3">
                Rp <?= number_format($book['price'], 0, ',', '.') ?>
            </h3>

            <p>
                Stok:
                <?php if ($book['stock'] > 0): ?>
                    <span class="badge bg-success"><?= (int)$book['stock'] ?> tersedia</span>
                <?php else: ?>
                    <span class="badge bg-secondary">Habis</span>
                <?php endif; ?>
            </p>

            <h6>Deskripsi</h6>
            <p class="text-secondary"><?= nl2br(htmlspecialchars($book['description'] ?? '')) ?></p>

            <?php if ($book['stock'] > 0): ?>
                <form method="post" class="d-flex gap-2 align-items-center mt-4">
                    <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
                    <input type="number" name="qty" value="1" min="1" max="<?= (int)$book['stock'] ?>"
                           class="form-control" style="width:90px;">
                    <button type="submit" class="btn btn-navy">
                        <i class="bi bi-cart-plus"></i> Tambah ke Keranjang
                    </button>
                </form>
            <?php else: ?>
                <button class="btn btn-secondary mt-4" disabled>Stok Habis</button>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>