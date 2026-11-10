<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/admin_guard.php';
require_once __DIR__ . '/../includes/upload_cover.php';

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

// Ambil buku yang akan diedit
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM books WHERE id = ?');
$stmt->execute([$id]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$book) {
    $_SESSION['flash'] = ['danger', 'Buku tidak ditemukan.'];
    header('Location: books.php');
    exit;
}

$kategori = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);

$errors      = [];
$title       = $book['title'];
$author      = $book['author'];
$description = $book['description'] ?? '';
$price       = (string)$book['price'];
$stock       = (string)$book['stock'];
$catId       = (int)$book['category_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $author      = trim($_POST['author'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = trim($_POST['price'] ?? '');
    $stock       = trim($_POST['stock'] ?? '');
    $catId       = (int)($_POST['category_id'] ?? 0);

    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        $errors[] = 'Permintaan tidak valid. Muat ulang halaman.';
    }
    if ($title === '' || strlen($title) > 200) {
        $errors[] = 'Judul wajib diisi (maksimal 200 karakter).';
    }
    if ($author === '' || strlen($author) > 100) {
        $errors[] = 'Penulis wajib diisi (maksimal 100 karakter).';
    }
    if (!in_array($catId, array_map('intval', array_column($kategori, 'id')), true)) {
        $errors[] = 'Pilih kategori yang valid.';
    }
    if (!ctype_digit($price) || (int)$price < 1) {
        $errors[] = 'Harga harus berupa angka lebih dari 0.';
    }
    if (!ctype_digit($stock)) {
        $errors[] = 'Stok harus berupa angka 0 atau lebih.';
    }

    // Cover baru (boleh dikosongkan = tetap pakai cover lama)
    $namaFile = null;
    if (!$errors) {
        [$namaFile, $errUpload] = uploadCover($_FILES['cover'] ?? ['error' => UPLOAD_ERR_NO_FILE]);
        if ($errUpload !== '') {
            $errors[] = $errUpload;
        }
    }

    if (!$errors) {
        try {
            $coverBaru = $namaFile ?? $book['cover'];

            $pdo->prepare(
                'UPDATE books
                 SET category_id = ?, title = ?, author = ?, description = ?, price = ?, stock = ?, cover = ?
                 WHERE id = ?'
            )->execute([$catId, $title, $author, $description, (int)$price, (int)$stock, $coverBaru, $id]);

            // Cover lama dihapus kalau sudah diganti
            if ($namaFile && !empty($book['cover'])) {
                @unlink(__DIR__ . '/../uploads/covers/' . basename($book['cover']));
            }

            $_SESSION['flash'] = ['success', 'Buku berhasil diperbarui.'];
            header('Location: books.php');
            exit;
        } catch (PDOException $e) {
            if ($namaFile) {
                @unlink(__DIR__ . '/../uploads/covers/' . $namaFile);
            }
            $errors[] = 'Gagal menyimpan perubahan. Coba lagi.';
        }
    }
}

$file     = !empty($book['cover']) ? 'uploads/covers/' . basename($book['cover']) : '';
$adaCover = $file !== '' && is_file(__DIR__ . '/../' . $file);

$pageTitle = 'Edit Buku';
$active    = 'books';
require_once __DIR__ . '/../includes/admin_header.php';
?>

<?php if ($errors): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $err): ?>
            <div><?= htmlspecialchars($err) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">

            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Judul Buku</label>
                    <input type="text" name="title" class="form-control"
                           value="<?= htmlspecialchars($title) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Kategori</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">-- Pilih --</option>
                        <?php foreach ($kategori as $k): ?>
                            <option value="<?= (int)$k['id'] ?>" <?= $catId === (int)$k['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($k['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Penulis</label>
                    <input type="text" name="author" class="form-control"
                           value="<?= htmlspecialchars($author) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Harga (Rp)</label>
                    <input type="number" name="price" min="1" class="form-control"
                           value="<?= htmlspecialchars($price) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Stok</label>
                    <input type="number" name="stock" min="0" class="form-control"
                           value="<?= htmlspecialchars($stock) ?>" required>
                </div>

                <div class="col-12">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" rows="4" class="form-control"><?= htmlspecialchars($description) ?></textarea>
                </div>

                <div class="col-12">
                    <label class="form-label">Cover Buku</label>
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <?php if ($adaCover): ?>
                            <img src="../<?= htmlspecialchars($file) ?>" alt=""
                                 style="width:60px;height:86px;object-fit:cover;border-radius:4px;">
                            <small class="text-muted">Cover saat ini. Pilih file baru hanya jika ingin menggantinya.</small>
                        <?php else: ?>
                            <small class="text-muted">Belum ada cover.</small>
                        <?php endif; ?>
                    </div>
                    <input type="file" name="cover" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                    <small class="text-muted">JPG, PNG, atau WEBP, maksimal 2 MB.</small>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-navy">Simpan Perubahan</button>
                <a href="books.php" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
