
<?php
require_once __DIR__ . '/../config/db.php';
include __DIR__ . '/../layout/header.php';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $account_id   = trim($_POST['account_id'] ?? '');
    $display_name = trim($_POST['display_name'] ?? '');
    $gender       = trim($_POST['gender'] ?? '');
    $birthdate    = trim($_POST['birthdate'] ?? '');
    $location_at  = trim($_POST['location_at'] ?? '');
    $location_on  = trim($_POST['location_on'] ?? '');
    $pref_gender  = trim($_POST['pref_gender'] ?? '');
    $pref_age_min = trim($_POST['pref_age_min'] ?? '');
    $pref_age_max = trim($_POST['pref_age_max'] ?? '');

    foreach ([
        'account_id' => $account_id,
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
      // Handle optional photo upload
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
        $sql = "INSERT INTO profile (accountid, displayname, gender, birthdate, locationat, locationon, prefgender, prefagemin, prefagemax, photo)
            VALUES (:accountid, :displayname, :gender, :birthdate, :locationat, :locationon, :prefgender, :prefagemin, :prefagemax, :photo)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
          ':accountid' => $account_id,
          ':displayname' => $display_name,
          ':gender' => $gender,
          ':birthdate' => $birthdate,
          ':locationat' => $location_at,
          ':locationon' => $location_on,
          ':prefgender' => $pref_gender,
          ':prefagemin' => (int)$pref_age_min,
          ':prefagemax' => (int)$pref_age_max,
          ':photo' => $photoFilename,
        ]);
        header('Location: index.php');
        exit;
      } catch (PDOException $e) {
        $msg = $e->getMessage();
        if (stripos($msg, 'Unknown column') !== false) {
          $errors[] = 'Gagal simpan: kolom `photo` tidak ditemukan pada tabel `profile`. Jalankan: ALTER TABLE profile ADD COLUMN photo VARCHAR(255) NULL;';
        } else {
          $errors[] = 'Gagal simpan: ' . htmlspecialchars($msg);
        }
      }
    }
}
$accounts = $pdo->query('SELECT accountid, email FROM account ORDER BY accountid')->fetchAll();
?>
<div class="card">
  <h2 style="margin-top:0">Tambah Profile</h2>
  <?php if ($errors): ?>
    <div style="background:#ffebee;color:#b00020;padding:10px;margin-bottom:12px;border-radius:8px;">
      <ul>
        <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
  <form method="POST" enctype="multipart/form-data">
    <label>Akun</label><br>
    <select name="account_id" required>
      <option value="">-- pilih akun --</option>
      <?php foreach ($accounts as $a): ?>
        <option value="<?= htmlspecialchars($a['accountid']) ?>"><?= htmlspecialchars($a['accountid'] . ' - ' . $a['email']) ?></option>
      <?php endforeach; ?>
    </select><br><br>

    <label>Nama Tampilan</label><br>
    <input type="text" name="display_name" required><br><br>

    <label>Gender</label><br>
    <select name="gender" required>
      <option value="L">L</option>
      <option value="P">P</option>
    </select><br><br>

    <label>Tanggal Lahir</label><br>
    <input type="date" name="birthdate" required><br><br>

    <label>Lokasi</label><br>
    <input type="text" name="location_at" required placeholder="Kota"><br><br>

    <label>Negara/Kode</label><br>
    <input type="text" name="location_on" required placeholder="ID"><br><br>

    <label>Preferensi Gender</label><br>
    <select name="pref_gender" required>
      <option value="L">L</option>
      <option value="P">P</option>
    </select><br><br>

    <label>Usia Minimum</label><br>
    <input type="number" name="pref_age_min" min="18" required><br><br>

    <label>Usia Maksimum</label><br>
    <input type="number" name="pref_age_max" min="18" required><br><br>

    <label>Foto Profile (opsional)</label><br>
    <input type="file" name="photo" accept="image/*"><br><br>

    <button class="btn btn-primary" type="submit">Simpan</button>
    <a class="btn" href="index.php">Batal</a>
  </form>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>
