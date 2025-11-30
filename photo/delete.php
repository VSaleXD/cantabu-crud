
<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/app.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php'); exit; }
$id = $_POST['id'] ?? null;
if (!$id) { header('Location: index.php'); exit; }

// Cari URL dulu agar kita tahu nama file fisik
$stmt = $pdo->prepare("SELECT storageurl FROM cantabu.photo WHERE photoid = :id");
$stmt->execute([':id' => $id]);
$row = $stmt->fetch();

try {
    $pdo->prepare("DELETE FROM cantabu.photo WHERE photoid = :id")->execute([':id' => $id]);

    if ($row && isset($row['storageurl'])) {
        // Translate URL publik -> path file
        $url = $row['storageurl'];
        $pos = strpos($url, '/uploads/');
        if ($pos !== false) {
            $filename = substr($url, $pos + strlen('/uploads/'));
            $path = $UPLOAD_DIR . '/' . $filename;
            if (is_file($path)) { @unlink($path); }
        }
    }
} catch (PDOException $e) {
    die('Gagal menghapus photo: ' . htmlspecialchars($e->getMessage()));
}
header('Location: index.php');
exit;
