
<?php
require_once __DIR__ . '/../config/db.php';
include __DIR__ . '/../layout/header.php';
$errors = []; $okmsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $profile_a = trim($_POST['profile_a'] ?? '');
    $profile_b = trim($_POST['profile_b'] ?? '');
    $status    = trim($_POST['status'] ?? 'Active');

    if ($profile_a === '' || !ctype_digit($profile_a)) $errors[] = 'Profile A wajib dipilih';
    if ($profile_b === '' || !ctype_digit($profile_b)) $errors[] = 'Profile B wajib dipilih';
    if (!in_array($status, ['Active','Unmatched','Blocked'])) $errors[] = 'Status tidak valid';
    if (empty($errors) && $profile_a === $profile_b) $errors[] = 'Profile A dan B tidak boleh sama';

    if (empty($errors)) {
        try {
            $sql = "INSERT INTO matches (profilea_id, profileb_id, status) VALUES (:a, :b, :status)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':a' => (int)$profile_a, ':b' => (int)$profile_b, ':status' => $status]);
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            $msg = $e->getMessage();
            if (strpos($msg, 'ck_match_pair') !== false) {
                $errors[] = 'Profile A dan B tidak boleh sama.';
            } elseif (strpos($msg, 'uq_match_unordered_pair') !== false) {
                $errors[] = 'Pasangan ini sudah ada (unik tanpa memperhatikan urutan).';
            } else {
                $errors[] = 'DB error: ' . htmlspecialchars($msg);
            }
        }
    }
}

// Ambil profil untuk dropdown
$profiles = $pdo->query("SELECT p.profileid, p.displayname, a.email FROM profile p JOIN account a ON a.accountid = p.accountid ORDER BY p.profileid")->fetchAll();
?>
<div class="card">
  <h2 style="margin-top:0">Buat Match</h2>
  <?php if ($errors): ?>
    <div style="background:#ffebee;color:#b00020;padding:10px;margin-bottom:12px;border-radius:8px;">
      <ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
    </div>
  <?php endif; ?>
  <form method="POST">
    <label>Profile A</label><br>
    <select name="profile_a" required>
      <option value="">-- pilih --</option>
      <?php foreach ($profiles as $p): ?>
        <option value="<?= htmlspecialchars($p['profileid']) ?>"><?= htmlspecialchars($p['profileid'] . ' — ' . $p['displayname'] . ' (' . $p['email'] . ')') ?></option>
      <?php endforeach; ?>
    </select><br><br>

    <label>Profile B</label><br>
    <select name="profile_b" required>
      <option value="">-- pilih --</option>
      <?php foreach ($profiles as $p): ?>
        <option value="<?= htmlspecialchars($p['profileid']) ?>"><?= htmlspecialchars($p['profileid'] . ' — ' . $p['displayname'] . ' (' . $p['email'] . ')') ?></option>
      <?php endforeach; ?>
    </select><br><br>

    <label>Status</label><br>
    <select name="status" required>
      <option value="Active">Active</option>
      <option value="Unmatched">Unmatched</option>
      <option value="Blocked">Blocked</option>
    </select><br><br>

    <button class="btn btn-primary" type="submit">Simpan</button>
    <a class="btn" href="index.php">Batal</a>
  </form>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>
