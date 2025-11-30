
<?php
// photo/index.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/app.php';
include __DIR__ . '/../layout/header.php';

// Ambil daftar foto + info profile
$sql = "
SELECT ph.photoid, ph.profileid, ph.storageurl, ph.isprimary, ph.verifiedbyfaceid, ph.hash, ph.createdat,
       p.displayname, a.email
FROM cantabu.photo ph
JOIN cantabu.profile p ON p.profileid = ph.profileid
JOIN cantabu.account a ON a.accountid = p.accountid
ORDER BY ph.createdat DESC";
$photos = $pdo->query($sql)->fetchAll();

$profiles = $pdo->query("
SELECT p.profileid, a.email, p.displayname
FROM cantabu.profile p
JOIN cantabu.account a ON a.accountid = p.accountid
ORDER BY p.profileid")->fetchAll();
?>
<style>
  table { border-collapse: collapse; width: 100%; }
  th, td { border: 1px solid #ddd; padding: 8px; }
  th { background: #f5f5f5; text-align: left; }
  .btn { display:inline-block; padding:6px 10px; border:1px solid #333; border-radius:6px; text-decoration:none; }
  .btn-primary { background:#1976d2; color:#fff; border-color:#1976d2; }
  .btn-danger  { background:#d32f2f; color:#fff; border-color:#d32f2f; }
</style>

<h1>Foto Profile</h1>
<p>Kelola foto untuk masing-masing profile (schema: <code>cantabu</code>)</p>

<p>
  <a class="btn btn-primary" href="upload.php">+ Upload Foto</a>
</p>

  <div class="photos-grid">
    <?php if (!$photos): ?>
      <p>Belum ada foto. Coba unggah lewat <a class="btn btn-primary" href="upload.php">Upload Foto</a>.</p>
    <?php else: ?>
      <?php foreach ($photos as $ph): ?>
        <div class="photo-card">
          <?php if (!empty($ph['storageurl'])): ?>
            <a href="<?= htmlspecialchars($ph['storageurl']) ?>" target="_blank" rel="noopener">
              <img src="<?= htmlspecialchars($ph['storageurl']) ?>" alt="foto" class="photo-img">
            </a>
          <?php else: ?>
            <div class="photo-empty">No image</div>
          <?php endif; ?>
          <div class="photo-caption">
            <strong class="photo-name"><?= htmlspecialchars($ph['displayname']) ?></strong>
            <div class="photo-actions">
              <form action="delete.php" method="POST" onsubmit="return confirm('Yakin hapus foto ini?');" style="display:inline">
                <input type="hidden" name="id" value="<?= intval($ph['photoid']) ?>">
                <button class="btn btn-danger" type="submit">Delete</button>
              </form>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <style>
    .photos-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:14px;margin-top:14px}
    .photo-card{background:#fff;border:1px solid #eee;border-radius:12px;overflow:hidden;display:flex;flex-direction:column}
    .photo-img{width:100%;height:180px;object-fit:cover;display:block}
    .photo-empty{width:100%;height:180px;display:flex;align-items:center;justify-content:center;background:#f3f3f3;color:#777}
    .photo-meta{display:flex;justify-content:space-between;padding:10px}
    .photo-foot{display:flex;justify-content:space-between;align-items:center;padding:10px;border-top:1px solid #fafafa}
    .badge{display:inline-block;padding:4px 8px;border-radius:999px;background:#f3f3f3;font-size:12px;margin-left:6px}
    .badge.verified{background:linear-gradient(90deg,#a5d6a7,#66bb6a);color:#fff}
    .actions .btn{margin-left:8px}
  </style>

<?php include __DIR__ . '/../layout/footer.php'; ?>
