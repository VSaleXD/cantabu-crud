
<?php
require_once __DIR__ . '/../config/db.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php'); exit; }
$id = $_POST['id'] ?? null;
if (!$id) { header('Location: index.php'); exit; }
try {
    $stmt = $pdo->prepare('DELETE FROM profile WHERE profileid = :id');
    $stmt->execute([':id' => $id]);
} catch (PDOException $e) {
    die('Gagal menghapus profile: ' . htmlspecialchars($e->getMessage()));
}
header('Location: index.php');
exit;
