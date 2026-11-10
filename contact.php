<?php
session_start();
require_once 'config/database.php';

$success = '';
$error   = '';
$name = $email = $subject = $message = '';

// Isi otomatis nama & email jika sudah login
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    if ($u = $stmt->fetch()) {
        $name  = $u['name'];
        $email = $u['email'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name']);
    $email   = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    $user_id = $_SESSION['user_id'] ?? null;

    if ($name === '' || $email === '' || $subject === '' || $message === '') {
        $error = 'Semua kolom wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO messages (user_id, name, email, subject, message)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$user_id, $name, $email, $subject, $message]);
        $success = 'Pesan berhasil dikirim. Admin akan segera menghubungi Anda.';
        $subject = $message = ''; // kosongkan form setelah terkirim
    }
}

$pageTitle = 'Hubungi Admin';
include 'includes/header.php'; // GANTI jika nama file header Anda berbeda
include 'includes/navbar.php';
?>

<main class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-6 col-md-8">

      <div class="text-center mb-4">
        <i class="bi bi-envelope-paper fs-1 text-primary"></i>
        <h2 class="fw-bold mt-2">Hubungi Admin</h2>
        <p class="text-muted">Ada pertanyaan atau kendala? Kirim pesan dan kami akan membalasnya.</p>
      </div>

      <div class="card shadow-sm border-0">
        <div class="card-body p-4">

          <?php if ($success): ?>
            <div class="alert alert-success">
              <i class="bi bi-check-circle me-1"></i><?= htmlspecialchars($success) ?>
            </div>
          <?php endif; ?>

          <?php if ($error): ?>
            <div class="alert alert-danger">
              <i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($error) ?>
            </div>
          <?php endif; ?>

          <form method="POST">
            <div class="mb-3">
              <label class="form-label">Nama</label>
              <input type="text" name="name" class="form-control"
                     value="<?= htmlspecialchars($name) ?>" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control"
                     value="<?= htmlspecialchars($email) ?>" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Subjek</label>
              <input type="text" name="subject" class="form-control"
                     value="<?= htmlspecialchars($subject) ?>" required>
            </div>

            <div class="mb-4">
              <label class="form-label">Pesan</label>
              <textarea name="message" rows="5" class="form-control" required><?= htmlspecialchars($message) ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary w-100">
              <i class="bi bi-send me-1"></i> Kirim Pesan
            </button>
          </form>

        </div>
      </div>

    </div>
  </div>
</main>

<?php include 'includes/footer.php'; // GANTI jika perlu ?>