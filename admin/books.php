<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/admin_guard.php';

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

// ===== Proses tombol (ubah stok / hapus) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);

    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        $_SESSION['flash'] = ['danger', 'Permintaan tidak valid. Muat ulang halaman.'];

    } elseif ($action === 'stock') {
        $stok = trim($_POST['stock'] ?? '');
        if (!ctype_digit($stok)) {                     // hanya angka 0 ke atas
            $_SESSION['flash'] = ['danger', 'Stok harus berupa angka 0 atau lebih.'];
        } else {
            $pdo->prepare('UPDATE books SET stock = ? WHERE id = ?')->execute([(int)$stok, $id]);
            $_SESSION['flash'] = ['success', 'Stok berhasil diperbarui.'];
        }

    } elseif ($action === 'delete') {
        $stmt = $pdo->prepare('SELECT cover FROM books WHERE id = ?');
        $stmt->execute([$id]);
        $cover = $stmt->fetchColumn();

        try {
            $pdo->prepare('DELETE FROM books WHERE id = ?')->execute([$id]);

            // Hapus juga file gambarnya
            if ($cover) {
                $path = __DIR__ . '/../uploads/covers/' . basename($cover);
                if (is_file($path)) {
                    unlink($path);
                }
            }
            $_SESSION['flash'] = ['success', 'Buku berhasil dihapus.'];
        } catch (PDOException $e) {
            $_SESSION['flash'] = ['danger', 'Buku tidak bisa dihapus karena masih terkait data lain.'];
        }
    }

    header('Location: books.php');
    exit;
}

// ===== Ambil daftar buku =====
$q = trim($_GET['q'] ?? '');

$sql = 'SELECT b.*, c.name AS category
        FROM books b
        LEFT JOIN categories c ON c.id = b.category_id';
$params = [];

if ($q !== '') {
    $sql .= ' WHERE b.title LIKE ? OR b.author LIKE ?';
    $params = ['%' . $q . '%', '%' . $q . '%'];
}
$sql .= ' ORDER BY b.id DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pesan sekali tampil
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$pageTitle = 'Buku & Stok';
$active    = 'books';
require_once __DIR__ . '/../includes/admin_header.php';
?>

<?php if ($flash): ?>
    <div class="alert alert-<?= $flash[0] ?>"><?= htmlspecialchars($flash[1]) ?></div>
<?php endif; ?>

<div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
    <form class="d-flex gap-2" method="get">
        <input type="search" name="q" class="form-control" placeholder="Cari judul atau penulis..."
               value="<?= htmlspecialchars($q) ?>">
        <button class="btn btn-outline-dark"><i class="bi bi-search"></i></button>
        <?php if ($q !== ''): ?>
            <a href="books.php" class="btn btn-outline-secondary">Reset</a>
        <?php endif; ?>
    </form>
    <a href="book_add.php" class="btn btn-navy"><i class="bi bi-plus-lg"></i> Tambah Buku</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Cover</th>
                    <th>Judul</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th style="min-width:170px;">Stok</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($books as $b): ?>
                    <?php
                    $file     = !empty($b['cover']) ? 'uploads/covers/' . basename($b['cover']) : '';
                    $adaCover = $file !== '' && is_file(__DIR__ . '/../' . $file);
                    ?>
                    <tr>
                        <td>
                            <?php if ($adaCover): ?>
                                <img src="../<?= htmlspecialchars($file) ?>" alt=""
                                     style="width:45px;height:64px;object-fit:cover;border-radius:4px;">
                            <?php else: ?>
                                <div class="d-flex align-items-center justify-content-center bg-light text-secondary"
                                     style="width:45px;height:64px;border-radius:4px;">
                                    <i class="bi bi-book"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="fw-semibold"><?= htmlspecialchars($b['title']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars($b['author']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($b['category'] ?? '-') ?></td>
                        <td>Rp <?= number_format($b['price'], 0, ',', '.') ?></td>
                        <td>
                            <form method="post" class="d-flex gap-1">
                                <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
                                <input type="hidden" name="action" value="stock">
                                <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
                                <input type="number" name="stock" value="<?= (int)$b['stock'] ?>" min="0"
                                       class="form-control form-control-sm" style="width:80px;">
                                <button class="btn btn-sm btn-outline-dark" title="Simpan stok">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                            </form>
                            <?php if ($b['stock'] == 0): ?>
                                <span class="badge bg-danger mt-1">Stok habis</span>
                            <?php elseif ($b['stock'] <= 5): ?>
                                <span class="badge bg-warning text-dark mt-1">Stok menipis</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="book_edit.php?id=<?= (int)$b['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="post" class="d-inline"
                                  onsubmit="return confirm('Hapus buku ini? Tindakan tidak bisa dibatalkan.')">
                                <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$books): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada buku ditemukan.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>