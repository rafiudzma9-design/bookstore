<?php
require_once '../includes/init.php';
require_once '../includes/admin_guard.php';


// Simpan balasan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_id'])) {
    $reply = trim($_POST['reply']);
    if ($reply !== '') {
        $stmt = $pdo->prepare(
            "UPDATE messages SET reply = ?, replied_at = NOW(), is_read = 1 WHERE id = ?"
        );
        $stmt->execute([$reply, (int) $_POST['reply_id']]);
    }
    header('Location: messages.php');
    exit;
}

// Tandai sudah dibaca
if (isset($_GET['read'])) {
    $stmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE id = ?");
    $stmt->execute([(int) $_GET['read']]);
    header('Location: messages.php');
    exit;
}

// Hapus pesan
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
    $stmt->execute([(int) $_GET['delete']]);
    header('Location: messages.php');
    exit;
}

$messages = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pesan Masuk - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">

  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0"><i class="bi bi-envelope me-2"></i>Pesan Masuk</h3>
    <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-arrow-left"></i> Dashboard
    </a>
  </div>

  <?php if (empty($messages)): ?>
    <div class="alert alert-info">Belum ada pesan.</div>
  <?php endif; ?>

  <?php foreach ($messages as $m): ?>
  <div class="card shadow-sm border-0 mb-3">
    <div class="card-body">

      <div class="d-flex justify-content-between">
        <div>
          <h5 class="mb-1"><?= htmlspecialchars($m['subject']) ?></h5>
          <small class="text-muted">
            Dari <strong><?= htmlspecialchars($m['name']) ?></strong>
            (<?= htmlspecialchars($m['email']) ?>) &middot; <?= htmlspecialchars($m['created_at']) ?>
          </small>
        </div>
        <div>
          <?php if ($m['reply']): ?>
            <span class="badge bg-success">Sudah dibalas</span>
          <?php elseif (!$m['is_read']): ?>
            <span class="badge bg-danger">Baru</span>
          <?php else: ?>
            <span class="badge bg-secondary">Dibaca</span>
          <?php endif; ?>
        </div>
      </div>

      <p class="mt-3 mb-3"><?= nl2br(htmlspecialchars($m['message'])) ?></p>

      <?php if ($m['reply']): ?>
        <div class="border-start border-success border-3 ps-3 mb-3 bg-white">
          <small class="text-success fw-bold">
            Balasan admin &middot; <?= htmlspecialchars($m['replied_at']) ?>
          </small>
          <p class="mb-0"><?= nl2br(htmlspecialchars($m['reply'])) ?></p>
        </div>
      <?php endif; ?>

      <button class="btn btn-primary btn-sm" type="button"
              data-bs-toggle="collapse" data-bs-target="#reply<?= $m['id'] ?>">
        <i class="bi bi-reply"></i> <?= $m['reply'] ? 'Ubah Balasan' : 'Balas' ?>
      </button>

      <?php if (!$m['is_read']): ?>
        <a href="?read=<?= $m['id'] ?>" class="btn btn-outline-secondary btn-sm">Tandai dibaca</a>
      <?php endif; ?>

      <a href="?delete=<?= $m['id'] ?>" class="btn btn-outline-danger btn-sm"
         onclick="return confirm('Hapus pesan ini?')">
        <i class="bi bi-trash"></i> Hapus
      </a>

      <div class="collapse mt-3" id="reply<?= $m['id'] ?>">
        <form method="POST">
          <input type="hidden" name="reply_id" value="<?= $m['id'] ?>">
          <textarea name="reply" rows="3" class="form-control mb-2"
                    placeholder="Tulis balasan untuk <?= htmlspecialchars($m['name']) ?>..."
                    required><?= htmlspecialchars($m['reply'] ?? '') ?></textarea>
          <button type="submit" class="btn btn-success btn-sm">
            <i class="bi bi-send"></i> Kirim Balasan
          </button>
        </form>
      </div>

    </div>
  </div>
  <?php endforeach; ?>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>