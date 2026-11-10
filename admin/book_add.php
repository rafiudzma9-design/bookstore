<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/admin_guard.php';
require_once __DIR__ . '/../includes/upload_cover.php';

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$kategori = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);

$errors = [];
$title = $author = $description = $price = $stock = '';
$catId = 0;

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

    // Upload cover (boleh dikosongkan)
    $namaFile = null;
    if (!$errors) {
        [$namaFile, $errUpload] = uploadCover($_FILES['cover'] ?? ['error' => UPLOAD_ERR_NO_FILE]);
        if ($errUpload !== '') {
            $errors[] = $errUpload;
        }
    }

    // Simpan ke database
    if (!$errors) {
        try {
            $pdo->prepare(
                'INSERT INTO books (category_id, title, author, description, price, stock, cover)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            )->execute([$catId, $title, $author, $description, (int)$price, (int)$stock, $namaFile]);

            $_SESSION['flash'] = ['success', 'Buku berhasil ditambahkan.'];
            header('Location: books.php');
            exit;
        } catch (PDOException $e) {
            if ($namaFile) {                                   // batalkan file yang sudah terunggah
                @unlink(__DIR__ . '/../uploads/covers/' . $namaFile);
            }
            $errors[] = 'Gagal menyimpan buku: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Tambah Buku';
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
                </div

                <div class="col-12">
                    <label class="form-label">Cover Buku <small class="text-muted">(JPG, PNG, WEBP, maks. 2 MB)</small></label>
                    <input type="file" name="cover" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-navy">Simpan Buku</button>
                <a href="books.php" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>