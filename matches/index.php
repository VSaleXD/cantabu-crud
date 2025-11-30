
<?php
require_once __DIR__ . '/../config/db.php';
include __DIR__ . '/../layout/header.php';

$sql = "SELECT m.matchid, m.status, m.matchedat,
               pa.profileid AS a_id, pa.displayname AS a_name, aa.email AS a_email,
               pb.profileid AS b_id, pb.displayname AS b_name, ab.email AS b_email
        FROM matches m
        JOIN profile pa ON pa.profileid = m.profilea_id
        JOIN account aa ON aa.accountid = pa.accountid
        JOIN profile pb ON pb.profileid = m.profileb_id
        JOIN account ab ON ab.accountid = pb.accountid
        ORDER BY m.matchedat DESC";
$rows = $pdo->query($sql)->fetchAll();
?>
<div class="card">
  <h2 style="margin-top:0">Daftar Matches</h2>
  <p class="muted">Pencocokan dua profil (schema: cantabu)</p>
  <p><a class="btn btn-primary" href="create.php">+ Buat Match</a></p>
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Profile A</th>
        <th>Profile B</th>
        <th>Status</th>
        <th>MatchedAt</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= htmlspecialchars($r['matchid']) ?></td>
          <td><?= htmlspecialchars($r['a_id']) ?> — <?= htmlspecialchars($r['a_name']) ?> (<?= htmlspecialchars($r['a_email']) ?>)</td>
          <td><?= htmlspecialchars($r['b_id']) ?> — <?= htmlspecialchars($r['b_name']) ?> (<?= htmlspecialchars($r['b_email']) ?>)</td>
          <td><?= htmlspecialchars($r['status']) ?></td>
          <td><?= htmlspecialchars($r['matchedat']) ?></td>
          <td>
            <a class="btn" href="edit.php?id=<?= urlencode($r['matchid']) ?>">Edit</a>
            <form action="delete.php" method="POST" style="display:inline" onsubmit="return confirm('Yakin hapus match ini?');">
              <input type="hidden" name="id" value="<?= htmlspecialchars($r['matchid']) ?>">
              <button class="btn btn-danger" type="submit">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>
