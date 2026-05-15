<?php
// Connexion BDD multi-plateforme :
//   - macOS XAMPP : socket Unix si présent
//   - Docker      : variables d'env DB_HOST/DB_PORT (cf. docker-compose)
//   - Windows XAMPP / Linux : TCP 127.0.0.1:3306 par défaut
$dbname   = getenv('DB_NAME') ?: 'artify';
$user     = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';

$unix_socket = '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock';
if (PHP_OS_FAMILY !== 'Windows' && file_exists($unix_socket)) {
    $dsn = "mysql:unix_socket=$unix_socket;dbname=$dbname;charset=utf8mb4";
} else {
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = getenv('DB_PORT') ?: '3306';
    $dsn  = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
}

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    die("Erreur connexion BDD : " . $e->getMessage());
}
?>