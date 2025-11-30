
<?php
// profile/index.php
require_once __DIR__ . '/../config/db.php';
include __DIR__ . '/../layout/header.php';

// Query ambil daftar profile + email akun
$sql = "
SELECT p.ProfileID, a.Email, p.DisplayName, p.Gender, p.Birthdate,
       p.LocationAt, p.LocationOn, p.PrefGender, p.PrefAgeMin, p.PrefAgeMax
FROM Profile p
JOIN Account a ON a.AccountID = p.AccountID
ORDER BY p.ProfileID;
";
$stmt = $pdo->query($sql);
$rows = $stmt->fetchAll();
?>
<h2>Daftar Profile</h2>

<p>
  create.php+ Tambah Profile</a>
</p>

<table>
  <thead>
    <tr>
      <th>ID</th>
      <th>Email Akun</th>
      <th>Nama Tampilan</th>
      <th>Gender</th>
      <th>Tgl Lahir</th>
      <th>Lokasi</th>
      <th>Negara</th>
      <th>Pref. Gender</th>
      <th>Usia Min</th>
      <th>Usia Max</th>
      <th>Aksi</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= htmlspecialchars($r['ProfileID']) ?></td>
        <td><?= htmlspecialchars($r['Email']) ?></td>
        <td><?= htmlspecialchars($r['DisplayName']) ?></td>
        <td><?= htmlspecialchars($r['Gender']) ?></td>
        <td><?= htmlspecialchars($r['Birthdate']) ?></td>
        <td><?= htmlspecialchars($r['LocationAt']) ?></td>
        <td><?= htmlspecialchars($r['LocationOn']) ?></td>
        <td><?= htmlspecialchars($r['PrefGender']) ?></td>
        <td><?= htmlspecialchars($r['PrefAgeMin']) ?></td>
        <td><?= htmlspecialchars($r['PrefAgeMax']) ?></td>
        <td>
          edit.php?id=<?= urlencode($r[">Edit</a>
          delete.php
            <input type="hidden" name="id" value="<?= htmlspecialchars($r['ProfileID']) ?>">
            <button class="btn btn-danger" type="submit">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php include __DIR__ . '/../layout/footer.php'; ?>
