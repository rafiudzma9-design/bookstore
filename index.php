<?php
require_once __DIR__ . '/includes/init.php';

// Buku terbaru
$terbaru = $pdo->query(
    'SELECT b.*, c.name AS category
     FROM books b
     LEFT JOIN categories c ON c.id = b.category_id
     ORDER BY b.created_at DESC, b.id DESC
     LIMIT 4'
)->fetchAll(PDO::FETCH_ASSOC);

// Buku populer = yang paling banyak terjual (kalau belum ada penjualan, urut dari yang terbaru)
$populer = $pdo->query(
    'SELECT b.*, c.name AS category, COALESCE(SUM(od.qty), 0) AS sold
     FROM books b
     LEFT JOIN categories c ON c.id = b.category_id
     LEFT JOIN order_details od ON od.book_id = b.id
     GROUP BY b.id, c.name
     ORDER BY sold DESC, b.id DESC
     LIMIT 4'
)->fetchAll(PDO::FETCH_ASSOC);

// Kategori beserta jumlah bukunya
$kategori = $pdo->query(
    'SELECT c.id, c.name, COUNT(b.id) AS total
     FROM categories c
     LEFT JOIN books b ON b.category_id = c.id
     GROUP BY c.id, c.name
     ORDER BY c.name'
)->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Beranda';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<!-- Hero -->
<section class="hero text-white py-5" style="background: linear-gradient(135deg, #0a1f44 0%, #13306b 100%);">
    <div class="container py-4">
        <h1 class="display-5 fw-bold">Temukan Buku Favoritmu</h1>
        <p class="lead mb-4">Koleksi buku pilihan dengan harga terjangkau, dikirim sampai ke rumah.</p>
        <a href="books.php" class="btn btn-light btn-lg">
            <i class="bi bi-book"></i> Lihat Katalog
        </a>
    </div>
</section>

<div class="container py-5">

    <!-- Kategori -->
    <h4 class="text-navy mb-3">Kategori</h4>
    <div class="row g-3 mb-5">
        <?php foreach ($kategori as $k): ?>
            <div class="col-6 col-md-3">
                <a href="books.php?category=<?= (int)$k['id'] ?>"
                   class="card border-0 shadow-sm text-decoration-none text-center h-100">
                    <div class="card-body">
                        <i class="bi bi-bookmark-star fs-3 text-navy"></i>
                        <div class="fw-semibold text-dark"><?= htmlspecialchars($k['name']) ?></div>
                        <small class="text-muted"><?= (int)$k['total'] ?> buku</small>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Buku terbaru -->
    <h4 class="text-navy mb-3">Buku Terbaru</h4>
    <div class="row g-3 mb-5">
        <?php foreach ($terbaru as $book): ?>
            <?php include __DIR__ . '/includes/book_card.php'; ?>
        <?php endforeach; ?>
    </div>

    <!-- Buku populer -->
    <h4 class="text-navy mb-3">Buku Populer</h4>
    <div class="row g-3 mb-4">
        <?php foreach ($populer as $book): ?>
            <?php include __DIR__ . '/includes/book_card.php'; ?>
        <?php endforeach; ?>
    </div>

    <div class="text-center">
        <a href="books.php" class="btn btn-outline-dark">Lihat Semua Buku</a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
