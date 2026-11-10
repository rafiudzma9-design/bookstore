<?php
require_once __DIR__ . '/../includes/init.php';

// Kalau sudah login sebagai admin, langsung ke dashboard
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        $error = 'Permintaan tidak valid. Muat ulang halaman.';
    } elseif ($email === '' || $password === '') {
        $error = 'Email dan password wajib diisi.';
    } else {
        // Hanya akun dengan role admin yang boleh masuk
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
        $stmt->execute([$email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];

            header('Location: dashboard.php');
            exit;
        }
        $error = 'Email atau password salah.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Admin - Bookstore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-navy">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow border-0">
                <div class="card-body p-4">
                    <h4 class="text-center text-navy mb-1">Admin Bookstore</h4>
                    <p class="text-center text-muted small mb-4">Masuk ke dashboard administrator</p>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="post">
                        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control"
                                   value="<?= htmlspecialchars($email) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-navy w-100">Login</button>
                    </form>

                    <p class="text-center mt-3 mb-0 small">
                        <a href="../index.php">Kembali ke website</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>