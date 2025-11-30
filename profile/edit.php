
<?php
require_once __DIR__ . '/../config/db.php';
include __DIR__ . '/../layout/header.php';
$id = $_GET['id'] ?? null;
if (!$id) { header('Location: index.php'); exit; }
$errors = [];
$stmt = $pdo->prepare('SELECT * FROM profile WHERE profileid = :id');
$stmt->execute([':id' => $id]);
$profile = $stmt->fetch();
if (!$profile) { echo '<p>Data tidak ditemukan.</p>'; include __DIR__ . '/../layout/footer.php'; exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $display_name = trim($_POST['display_name'] ?? '');
    $gender       = trim($_POST['gender'] ?? '');
    $birthdate    = trim($_POST['birthdate'] ?? '');
    $location_at  = trim($_POST['location_at'] ?? '');
    $location_on  = trim($_POST['location_on'] ?? '');
    $pref_gender  = trim($_POST['pref_gender'] ?? '');
    $pref_age_min = trim($_POST['pref_age_min'] ?? '');
    $pref_age_max = trim($_POST['pref_age_max'] ?? '');
    $remove_photo = !empty($_POST['remove_photo']);

    foreach ([
        'display_name' => $display_name,
        'gender' => $gender,
        'birthdate' => $birthdate,
        'location_at' => $location_at,
        'location_on' => $location_on,
        'pref_gender' => $pref_gender,
        'pref_age_min' => $pref_age_min,
        'pref_age_max' => $pref_age_max,
    ] as $field => $value) {
        if ($value === '') $errors[] = strtoupper($field) . ' wajib diisi';
    }
    if (!in_array($gender, ['L','P'])) $errors[] = 'Gender harus L/P';
    if (!in_array($pref_gender, ['L','P'])) $errors[] = 'PrefGender harus L/P';
    if (!ctype_digit($pref_age_min) || !ctype_digit($pref_age_max)) $errors[] = 'Usia min/max harus angka';
    if (empty($errors)) {
        $min = (int)$pref_age_min; $max = (int)$pref_age_max;
        if ($min < 18) $errors[] = 'Usia minimum ≥ 18';
        if ($max < $min) $errors[] = 'Usia maksimum ≥ usia minimum';
    }

    if (empty($errors)) {
      // Handle optional new photo upload
      $photoFilename = null;
      if (!empty($_FILES['photo']) && ($_FILES['photo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $f = $_FILES['photo'];
        if ($f['error'] !== UPLOAD_ERR_OK) {
          $errors[] = 'Gagal upload foto (kode: ' . $f['error'] . ')';
        } else {
          $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
          $finfo = finfo_open(FILEINFO_MIME_TYPE);
          $mime = finfo_file($finfo, $f['tmp_name']);
          finfo_close($finfo);
          if (!isset($allowed[$mime])) {
            $errors[] = 'Tipe file tidak diizinkan. Hanya JPG/PNG/GIF.';
          } elseif ($f['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Ukuran file terlalu besar (maks 2MB).';
          } else {
            $ext = $allowed[$mime];
            $photoFilename = uniqid('p_', true) . '.' . $ext;
            $destDir = __DIR__ . '/../uploads/avatars';
            if (!is_dir($destDir)) mkdir($destDir, 0755, true);
            $dest = $destDir . '/' . $photoFilename;
            if (!move_uploaded_file($f['tmp_name'], $dest)) {
              $errors[] = 'Gagal memindahkan file upload.';
              $photoFilename = null;
            }
          }
        }
      }
    }

    if (empty($errors)) {
      try {
        if ($photoFilename) {
          // remove previous file if exists
          if (!empty($profile['photo'])) {
            $old = __DIR__ . '/../uploads/avatars/' . $profile['photo'];
            if (is_file($old)) @unlink($old);
          }
          $sql = "UPDATE profile SET displayname=:displayname, gender=:gender, birthdate=:birthdate, locationat=:locationat, locationon=:locationon, prefgender=:prefgender, prefagemin=:prefagemin, prefagemax=:prefagemax, photo=:photo WHERE profileid=:id";
        } elseif ($remove_photo) {
          // delete existing photo and set column to NULL
          if (!empty($profile['photo'])) {
            $old = __DIR__ . '/../uploads/avatars/' . $profile['photo'];
            if (is_file($old)) @unlink($old);
          }
          $sql = "UPDATE profile SET displayname=:displayname, gender=:gender, birthdate=:birthdate, locationat=:locationat, locationon=:locationon, prefgender=:prefgender, prefagemin=:prefagemin, prefagemax=:prefagemax, photo=NULL WHERE profileid=:id";
        } else {
          $sql = "UPDATE profile SET displayname=:displayname, gender=:gender, birthdate=:birthdate, locationat=:locationat, locationon=:locationon, prefgender=:prefgender, prefagemin=:prefagemin, prefagemax=:prefagemax WHERE profileid=:id";
        }
        $stmt = $pdo->prepare($sql);
        $params = [
          ':displayname' => $display_name,
          ':gender' => $gender,
          ':birthdate' => $birthdate,
          ':locationat' => $location_at,
          ':locationon' => $location_on,
          ':prefgender' => $pref_gender,
          ':prefagemin' => (int)$pref_age_min,
          ':prefagemax' => (int)$pref_age_max,
          ':id' => $id,
        ];
        if ($photoFilename) $params[':photo'] = $photoFilename;
        $stmt->execute($params);
        header('Location: index.php');
        exit;
      } catch (PDOException $e) {
        $msg = $e->getMessage();
        if (stripos($msg, 'Unknown column') !== false) {
          $errors[] = 'Gagal update: kolom `photo` tidak ditemukan pada tabel `profile`. Jalankan: ALTER TABLE profile ADD COLUMN photo VARCHAR(255) NULL;';
        } else {
          $errors[] = 'Gagal update: ' . htmlspecialchars($msg);
        }
      }
    }
}
?>
<div class="card">
  <h2 style="margin-top:0">Edit Profile</h2>
  <?php if ($errors): ?>
    <div style="background:#ffebee;color:#b00020;padding:10px;margin-bottom:12px;border-radius:8px;">
      <ul>
        <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
  <form method="POST" enctype="multipart/form-data">
    <label>Nama Tampilan</label><br>
    <input type="text" name="display_name" value="<?= htmlspecialchars($profile['displayname']) ?>" required><br><br>

    <label>Gender</label><br>
    <select name="gender" required>
      <option value="L" <?= $profile['gender'] === 'L' ? 'selected' : '' ?>>L</option>
      <option value="P" <?= $profile['gender'] === 'P' ? 'selected' : '' ?>>P</option>
    </select><br><br>

    <label>Tanggal Lahir</label><br>
    <input type="date" name="birthdate" value="<?= htmlspecialchars($profile['birthdate']) ?>" required><br><br>

    <label>Lokasi</label><br>
    <input type="text" name="location_at" value="<?= htmlspecialchars($profile['locationat']) ?>" required><br><br>

    <label>Negara/Kode</label><br>
    <input type="text" name="location_on" value="<?= htmlspecialchars($profile['locationon']) ?>" required><br><br>

    <label>Preferensi Gender</label><br>
    <select name="pref_gender" required>
      <option value="L" <?= $profile['prefgender'] === 'L' ? 'selected' : '' ?>>L</option>
      <option value="P" <?= $profile['prefgender'] === 'P' ? 'selected' : '' ?>>P</option>
    </select><br><br>

    <label>Usia Minimum</label><br>
    <input type="number" name="pref_age_min" min="18" value="<?= htmlspecialchars($profile['prefagemin']) ?>" required><br><br>

    <label>Usia Maksimum</label><br>
    <input type="number" name="pref_age_max" min="18" value="<?= htmlspecialchars($profile['prefagemax']) ?>" required><br><br>

    <?php if (!empty($profile['photo']) && is_file(__DIR__ . '/../uploads/avatars/' . $profile['photo'])): ?>
      <label>Foto Saat Ini</label><br>
      <img src="<?= '../uploads/avatars/' . htmlspecialchars($profile['photo']) ?>" alt="foto" style="width:96px;height:96px;object-fit:cover;border-radius:8px;border:1px solid #eee;margin-bottom:8px"><br>
      <label style="display:inline-block;margin-bottom:12px"><input type="checkbox" name="remove_photo" value="1"> Hapus foto ini</label><br>
    <?php endif; ?>

    <label>Ganti Foto Profile (opsional)</label><br>
    <input type="file" name="photo" accept="image/*"><br><br>

    <button class="btn btn-primary" type="submit">Simpan</button>
    <a class="btn" href="index.php">Batal</a>
  </form>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>
