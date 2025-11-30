
<?php
require_once __DIR__ . '/../config/db.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php'); exit; }
$id = $_POST['id'] ?? null; if (!$id) { header('Location: index.php'); exit; }
try {
    $pdo->prepare('DELETE FROM matches WHERE matchid = :id')->execute([':id' => $id]);
} catch (PDOException $e) {
    // bisa log error
}
header('Location: index.php');
exit;
