
<?php
$host = 'localhost';
$port = '5432';
$dbname = 'postgres';    
$user = 'postgres';       
$password = '7541305';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec("SET search_path TO cantabu, public");
} catch (PDOException $e) {
    die('Koneksi DB gagal: ' . htmlspecialchars($e->getMessage()));
}

?>
