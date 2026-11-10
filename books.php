<?php
require_once __DIR__ . '/includes/init.php';

// Ambil input dari URL
$q     = trim($_GET['q'] ?? '');
$catId = (int)($_GET['category'] ?? 0);

// Daftar kategori untuk tombol filter
$kategori = $pdo->query('SELECT id, name FROM categories ORDER BY name')
                ->fetchAll(PDO::FETCH_ASSOC);

// Susun query secara bertahap sesuai filter yang dipakai
$sql = 'SELECT b.*, c.name AS category
        FROM books b
        LEFT JOIN categories c ON c.id = b.category_id
        WHERE 1 = 1';
$params = [];

if ($q !== '') {
    $sql .= ' AND (b.title LIKE :q1 OR b.author LIKE :q2 OR c.name LIKE :q3)';
    $like = '%' . $q . '%';
    $params[':q1'] = $like;
    $params[':q2'] = $like;
    $params[':q3'] = $like;
}

if ($catId > 0) {
    $sql .= ' AND b.category_id = :cat';
    $params[':cat'] = $catId;
}

$sql .= ' ORDER BY b.created_at DESC, b.id DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Katalog Buku';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container py-4">
    <h3 class="text-navy mb-3">Katalog Buku</h3>

    <!-- Filter kategori -->
    <div class="mb-3 d-flex flex-wrap gap-2">
        <a href="books.php<?= $q !== '' ? '?q=' . urlencode($q) : '' ?>"
           class="btn btn-sm <?= $catId === 0 ? 'btn-navy' : 'btn-outline-secondary' ?>">
            Semua
        </a>
        <?php foreach ($kategori as $k): ?>
            <a href="books.php?category=<?= (int)$k['id'] ?><?= $q !== '' ? '&q=' . urlencode($q) : '' ?>"
               class="btn btn-sm <?= $catId === (int)$k['id'] ? 'btn-navy' : 'btn-outline-secondary' ?>">
                <?= htmlspecialchars($k['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Info hasil -->
    <p class="text-muted">
        Menampilkan <strong><?= count($books) ?></strong> buku
        <?php if ($q !== ''): ?>
            untuk pencarian "<strong><?= htmlspecialchars($q) ?></strong>"
            <a href="books.php<?= $catId > 0 ? '?category=' . $catId : '' ?>" class="ms-2">Hapus pencarian</a>
        <?php endif; ?>
    </p>

    <!-- Daftar buku -->
    <?php if ($books): ?>
        <div class="row g-3">
            <?php foreach ($books as $book): ?>
                <?php include __DIR__ . '/includes/book_card.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center text-muted py-5">
            <i class="bi bi-search fs-1"></i>
            <p class="mt-2">Buku tidak ditemukan. Coba kata kunci atau kategori lain.</p>
            <a href="books.php" class="btn btn-navy">Lihat Semua Buku</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>