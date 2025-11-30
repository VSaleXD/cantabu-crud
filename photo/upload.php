
<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/app.php';
include __DIR__ . '/../layout/header.php';

$errors = [];
$okmsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $profile_id = trim($_POST['profile_id'] ?? '');
    $is_primary = isset($_POST['is_primary']) ? true : false;
    $verified   = isset($_POST['verified'])   ? true : false;

    // Validasi dasar
    if ($profile_id === '' || !ctype_digit($profile_id)) $errors[] = 'Profile wajib dipilih';

    // Periksa upload file dan beri pesan error yang jelas jika gagal
    if (!isset($_FILES['photo'])) {
      $errors[] = 'File foto tidak dikirim (input name mungkin salah atau form tidak memakai enctype multipart/form-data).';
    } elseif (($_FILES['photo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
      $code = $_FILES['photo']['error'] ?? UPLOAD_ERR_NO_FILE;
      switch ($code) {
        case UPLOAD_ERR_INI_SIZE: $errors[] = 'File melebihi upload_max_filesize pada konfigurasi PHP.'; break;
        case UPLOAD_ERR_FORM_SIZE: $errors[] = 'File melebihi MAX_FILE_SIZE yang dikirimkan oleh form.'; break;
        case UPLOAD_ERR_PARTIAL: $errors[] = 'File terupload hanya sebagian.'; break;
        case UPLOAD_ERR_NO_FILE: $errors[] = 'Tidak ada file yang dipilih.'; break;
        case UPLOAD_ERR_NO_TMP_DIR: $errors[] = 'Folder sementara tidak tersedia di server.'; break;
        case UPLOAD_ERR_CANT_WRITE: $errors[] = 'Gagal menulis file ke disk.'; break;
        case UPLOAD_ERR_EXTENSION: $errors[] = 'Upload dihentikan oleh ekstensi PHP.'; break;
        default: $errors[] = 'Terjadi kesalahan saat upload (kode ' . (int)$code . ')';
      }
    }

    if (empty($errors)) {
        $file = $_FILES['photo'];

        // Pastikan direktori upload ada
        if (!is_dir($UPLOAD_DIR)) {
          if (!mkdir($UPLOAD_DIR, 0755, true)) {
            $errors[] = 'Direktori upload tidak ada dan gagal dibuat: ' . $UPLOAD_DIR;
          }
        }
        if (!is_writable($UPLOAD_DIR)) {
          $errors[] = 'Direktori upload tidak dapat ditulis oleh server: ' . $UPLOAD_DIR;
        }

        // Ambil mime type dengan fallbacks
        $size = $file['size'];
        $mime = null;
        if (function_exists('finfo_open')) {
          $finfo = finfo_open(FILEINFO_MIME_TYPE);
          $mime = finfo_file($finfo, $file['tmp_name']);
          finfo_close($finfo);
        } elseif (function_exists('mime_content_type')) {
          $mime = mime_content_type($file['tmp_name']);
        }
        if (!$mime) $mime = $file['type'] ?? '';

        if (!in_array($mime, $ALLOWED_MIMES)) $errors[] = 'Format gambar harus JPG/PNG/WebP (detected: ' . htmlspecialchars($mime) . ')';
        if ($size > $MAX_SIZE_MB * 1024 * 1024) $errors[] = 'Ukuran file melebihi ' . $MAX_SIZE_MB . ' MB';

        // Pastikan profile ada
        $exists = $pdo->prepare("SELECT 1 FROM cantabu.profile WHERE profileid = :id");
        $exists->execute([':id' => $profile_id]);
        if (!$exists->fetchColumn()) $errors[] = 'Profile tidak valid';

        if (empty($errors)) {
            // Buat nama file unik
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $basename = 'p' . $profile_id . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4));
            $filename = $basename . '.' . strtolower($ext);
            $dest = $UPLOAD_DIR . '/' . $filename;

            // Pindahkan file
            if (!move_uploaded_file($file['tmp_name'], $dest)) {
                $errors[] = 'Gagal menyimpan file ke server';
            } else {
                // Buat URL publik + hash
                $publicUrl = $PUBLIC_BASE . '/' . $filename;
                $hash = 'sha256:' . hash_file('sha256', $dest);

                try {
                    // Jika user menandai sebagai primary, optional: set semua foto lain is_primary=false
                    // atau biarkan constraint unique partial index yang menolak jika sudah ada primary.
                    if ($is_primary) {
                        // opsi 1 (lebih user-friendly): turunkan primary yang lain
                        $pdo->prepare("UPDATE cantabu.photo SET isprimary=false WHERE profileid=:pid")->execute([':pid' => $profile_id]);
                        // dengan ini tidak akan kena unique index error
                    }

                    $sql = "INSERT INTO cantabu.photo (profileid, storageurl, isprimary, verifiedbyfaceid, hash)
                            VALUES (:pid, :url, :primary, :verified, :hash)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ':pid'     => $profile_id,
                        ':url'     => $publicUrl,
                        ':primary' => $is_primary,
                        ':verified'=> $verified,
                        ':hash'    => $hash,
                    ]);
                    $okmsg = 'Foto berhasil diunggah!';
                } catch (PDOException $e) {
                    // Jika kena unique index (satu primary per profile), tampilkan pesan ramah
                    if (strpos($e->getMessage(), 'uq_photo_primary_per_profile') !== false) {
                        $errors[] = 'Sudah ada foto utama untuk profile ini. Nonaktifkan primary di foto lama dulu, atau pakai opsi menurunkan primary otomatis.';
                    } else {
                        $errors[] = 'DB error: ' . htmlspecialchars($e->getMessage());
                    }
                }
            }
        }
    }
}

// Ambil profile untuk dropdown
$profiles = $pdo->query("
SELECT p.profileid, a.email, p.displayname
FROM cantabu.profile p JOIN cantabu.account a ON a.accountid = p.accountid
ORDER BY p.profileid
")->fetchAll();
?>
<div class="card">
  <h2 style="margin-top:0">Upload Foto Profile</h2>
  <?php if ($okmsg): ?>
    <div style="background:#e8f5e9;color:#2e7d32;padding:10px;margin-bottom:12px;border-radius:8px;">
      <?= htmlspecialchars($okmsg) ?>
    </div>
  <?php endif; ?>
  <?php if ($errors): ?>
    <div style="background:#ffebee;color:#b00020;padding:10px;margin-bottom:12px;border-radius:8px;">
      <ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
    </div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data">
    <label>Profile</label><br>
    <select name="profile_id" required>
      <option value="">-- pilih profile --</option>
      <?php foreach ($profiles as $p): ?>
        <option value="<?= htmlspecialchars($p['profileid']) ?>">
          <?= htmlspecialchars($p['profileid'] . ' — ' . $p['displayname'] . ' (' . $p['email'] . ')') ?>
        </option>
      <?php endforeach; ?>
    </select><br><br>

    <label>File Foto (JPG/PNG/WebP, maks <?= $MAX_SIZE_MB ?>MB)</label><br>
    <input type="file" name="photo" accept=".jpg,.jpeg,.png,.webp,image/*" required><br><br>

    <label><input type="checkbox" name="is_primary"> Jadikan foto utama</label><br>
    <label><input type="checkbox" name="verified"> Verified by FaceID</label><br><br>

    <button class="btn btn-primary" type="submit">Upload</button></a>
    <button class="btn" type="button" onclick="window.location.href='index.php'">Kembali</button>
  </form>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>
