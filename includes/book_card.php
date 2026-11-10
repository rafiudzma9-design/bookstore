<?php
/** @var array $book */
// Kartu satu buku. Memakai variabel $book dari halaman yang memanggilnya.
$file     = !empty($book['cover']) ? 'uploads/covers/' . basename($book['cover']) : '';
$adaCover = $file !== '' && file_exists(__DIR__ . '/../' . $file);
?>
<div class="col-6 col-md-4 col-lg-3">
    <div class="card h-100 border-0 shadow-sm">
        <?php if ($adaCover): ?>
            <img src="<?= htmlspecialchars($file) ?>" class="card-img-top book-cover"
                 alt="<?= htmlspecialchars($book['title']) ?>">
        <?php else: ?>
            <div class="book-cover d-flex align-items-center justify-content-center text-secondary">
                <i class="bi bi-book fs-1"></i>
            </div>
        <?php endif; ?>

        <div class="card-body d-flex flex-column">
            <small class="text-muted"><?= htmlspecialchars($book['category'] ?? 'Tanpa kategori') ?></small>
            <h6 class="card-title mb-1"><?= htmlspecialchars($book['title']) ?></h6>
            <small class="text-muted mb-2"><?= htmlspecialchars($book['author']) ?></small>

            <div class="mt-auto">
                <div class="fw-bold text-navy mb-2">
                    Rp <?= number_format($book['price'], 0, ',', '.') ?>
                </div>
                <?php if ($book['stock'] > 0): ?>
                    <a href="book_detail.php?id=<?= (int)$book['id'] ?>" class="btn btn-navy btn-sm w-100">
                        Lihat Detail
                    </a>
                <?php else: ?>
                    <span class="badge bg-secondary w-100 py-2">Stok Habis</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>