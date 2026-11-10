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

$pesan  = '';
$errors = [];

// Ambil data pengguna dari database
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {            // akun sudah tidak ada
    header('Location: logout.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        $errors[] = 'Permintaan tidak valid. Muat ulang halaman.';

    } elseif ($action === 'profile') {
        // ===== Ubah nama dan email =====
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if ($name === '') {
            $errors[] = 'Nama tidak boleh kosong.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format email tidak valid.';
        }

        // Email tidak boleh dipakai akun lain (akun sendiri dikecualikan)
        if (!$errors) {
            $cek = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id <> ?');
            $cek->execute([$email, $userId]);
            if ($cek->fetch()) {
                $errors[] = 'Email sudah dipakai akun lain.';
            }
        }

        if (!$errors) {
            $pdo->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?')
                ->execute([$name, $email, $userId]);
            $_SESSION['user_name'] = $name;       // perbarui nama di navbar
            $user['name']  = $name;
            $user['email'] = $email;
            $pesan = 'Profil berhasil diperbarui.';
        } else {
            $user['name']  = $name;               // tampilkan lagi isian pengguna
            $user['email'] = $email;
        }

    } elseif ($action === 'password') {
        // ===== Ubah password =====
        $lama    = $_POST['old_password'] ?? '';
        $baru    = $_POST['new_password'] ?? '';
        $konfirm = $_POST['confirm_password'] ?? '';

        if (!password_verify($lama, $user['password'])) {
            $errors[] = 'Password lama salah.';
        }
        if (strlen($baru) < 6) {
            $errors[] = 'Password baru minimal 6 karakter.';
        }
        if ($baru !== $konfirm) {
            $errors[] = 'Konfirmasi password baru tidak sama.';
        }

        if (!$errors) {
            $hash = password_hash($baru, PASSWORD_DEFAULT);
            $pdo->prepare('UPDATE users SET password = ? WHERE id = ?')
                ->execute([$hash, $userId]);
            session_regenerate_id(true);
            $pesan = 'Password berhasil diubah.';
        }
    }
}

$pageTitle = 'Profil Saya';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container py-4">
    <h3 class="text-navy mb-3">Profil Saya</h3>

    <?php if ($pesan): ?>
        <div class="alert alert-success"><?= htmlspecialchars($pesan) ?></div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $err): ?>
                <div><?= htmlspecialchars($err) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Data diri -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">Data Diri</h5>
                    <form method="post">
                        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
                        <input type="hidden" name="action" value="profile">

                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="name" class="form-control"
                                   value="<?= htmlspecialchars($user['name']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control"
                                   value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        <button type="submit" class="btn btn-navy">Simpan Perubahan</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Ubah password -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">Ubah Password</h5>
                    <form method="post">
                        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
                        <input type="hidden" name="action" value="password">

                        <div class="mb-3">
                            <label class="form-label">Password Lama</label>
                            <input type="password" name="old_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password Baru</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-navy">Ubah Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
