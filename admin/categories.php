<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/admin_guard.php';

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

// ===== Proses tambah / ubah / hapus =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);
    $name   = trim($_POST['name'] ?? '');
    $back   = 'categories.php';

    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        $_SESSION['flash'] = ['danger', 'Permintaan tidak valid. Muat ulang halaman.'];

    } elseif ($action === 'add' || $action === 'update') {
        if ($action === 'update') {
            $back = 'categories.php?edit=' . $id;
        }

        if ($name === '' || strlen($name) > 100) {
            $_SESSION['flash'] = ['danger', 'Nama kategori wajib diisi (maksimal 100 karakter).'];
        } else {
            // Nama tidak boleh kembar (kategori sendiri dikecualikan saat ubah)
            $cek = $pdo->prepare('SELECT id FROM categories WHERE name = ? AND id <> ?');
            $cek->execute([$name, $action === 'update' ? $id : 0]);

            if ($cek->fetch()) {
                $_SESSION['flash'] = ['danger', 'Nama kategori sudah dipakai.'];
            } elseif ($action === 'add') {
                $pdo->prepare('INSERT INTO categories (name) VALUES (?)')->execute([$name]);
                $_SESSION['flash'] = ['success', 'Kategori berhasil ditambahkan.'];
                $back = 'categories.php';
            } else {
                $pdo->prepare('UPDATE categories SET name = ? WHERE id = ?')->execute([$name, $id]);
                $_SESSION['flash'] = ['success', 'Kategori berhasil diperbarui.'];
                $back = 'categories.php';
            }
        }

    } elseif ($action === 'delete') {
        // Kategori yang masih punya buku tidak boleh dihapus
        $cek = $pdo->prepare('SELECT COUNT(*) FROM books WHERE category_id = ?');
        $cek->execute([$id]);

        if ((int)$cek->fetchColumn() > 0) {
            $_SESSION['flash'] = ['danger', 'Kategori tidak bisa dihapus karena masih memiliki buku.'];
        } else {
            try {
                $pdo->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
                $_SESSION['flash'] = ['success', 'Kategori berhasil dihapus.'];
            } catch (PDOException $e) {
                $_SESSION['flash'] = ['danger', 'Kategori tidak bisa dihapus karena masih terkait data lain.'];
            }
        }
    }

    header('Location: ' . $back);
    exit;
}

// ===== Data untuk tampilan =====
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT id, name FROM categories WHERE id = ?');
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

$daftar = $pdo->query(
    'SELECT c.id, c.name, COUNT(b.id) AS total
     FROM categories c
     LEFT JOIN books b ON b.category_id = c.id
     GROUP BY c.id, c.name
     ORDER BY c.name'
)->fetchAll(PDO::FETCH_ASSOC);

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$pageTitle = 'Kategori';
$active    = 'categories';
require_once __DIR__ . '/../includes/admin_header.php';
?>

<?php if ($flash): ?>
    <div class="alert alert-<?= $flash[0] ?>"><?= htmlspecialchars($flash[1]) ?></div>
<?php endif; ?>

<div class="row g-4">
    <!-- Form tambah / ubah -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="mb-3"><?= $edit ? 'Ubah Kategori' : 'Tambah Kategori' ?></h6>
                <form method="post">
                    <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
                    <input type="hidden" name="action" value="<?= $edit ? 'update' : 'add' ?>">
                    <?php if ($edit): ?>
                        <input type="hidden" name="id" value="<?= (int)$edit['id'] ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label">Nama Kategori</label>
                        <input type="text" name="name" class="form-control" maxlength="100"
                               value="<?= htmlspecialchars($edit['name'] ?? '') ?>" required>
                    </div>
                    <button type="submit" class="btn btn-navy">
                        <?= $edit ? 'Simpan Perubahan' : 'Tambah' ?>
                    </button>
                    <?php if ($edit): ?>
                        <a href="categories.php" class="btn btn-outline-secondary">Batal</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <!-- Daftar kategori -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:60px;">No.</th>
                            <th>Nama Kategori</th>
                            <th>Jumlah Buku</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($daftar as $i => $c): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($c['name']) ?></td>
                                <td><?= (int)$c['total'] ?></td>
                                <td class="text-end text-nowrap">
                                    <a href="categories.php?edit=<?= (int)$c['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="post" class="d-inline"
                                          onsubmit="return confirm('Hapus kategori ini?')">
                                        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (!$daftar): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">Belum ada kategori.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
