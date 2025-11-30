
<?php
require_once __DIR__ . '/../config/db.php';
include __DIR__ . '/../layout/header.php';
$id = $_GET['id'] ?? null; if (!$id) { header('Location: index.php'); exit; }
$errors = [];

$stmt = $pdo->prepare('SELECT matchid, profilea_id, profileb_id, status, matchedat FROM matches WHERE matchid = :id');
$stmt->execute([':id' => $id]);
$match = $stmt->fetch();
if (!$match) { echo '<div class="card"><p>Data tidak ditemukan.</p></div>'; include __DIR__ . '/../layout/footer.php'; exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = trim($_POST['status'] ?? 'Active');
    if (!in_array($status, ['Active','Unmatched','Blocked'])) $errors[] = 'Status tidak valid';

    if (empty($errors)) {
        try {
            $pdo->prepare('UPDATE matches SET status=:status WHERE matchid=:id')->execute([':status' => $status, ':id' => $id]);
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'DB error: ' . htmlspecialchars($e->getMessage());
        }
    }
}
?>
<div class="card">
  <h2 style="margin-top:0">Edit Match</h2>
  <p class="muted">ID: <?= htmlspecialchars($match['matchid']) ?> • MatchedAt: <?= htmlspecialchars($match['matchedat']) ?></p>
  <p>Profile A: <?= htmlspecialchars($match['profilea_id']) ?> • Profile B: <?= htmlspecialchars($match['profileb_id']) ?></p>
  <?php if ($errors): ?>
    <div style="background:#ffebee;color:#b00020;padding:10px;margin-bottom:12px;border-radius:8px;">
      <ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
    </div>
  <?php endif; ?>
  <form method="POST">
    <label>Status</label><br>
    <select name="status" required>
      <option value="Active" <?= $match['status']==='Active'?'selected':'' ?>>Active</option>
      <option value="Unmatched" <?= $match['status']==='Unmatched'?'selected':'' ?>>Unmatched</option>
      <option value="Blocked" <?= $match['status']==='Blocked'?'selected':'' ?>>Blocked</option>
    </select><br><br>

    <button class="btn btn-primary" type="submit">Simpan</button>
    <a class="btn" href="index.php">Batal</a>
  </form>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>
