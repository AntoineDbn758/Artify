<?php
// Connexion BDD — XAMPP local (MySQL/MariaDB sur 127.0.0.1:3306)
$dsn = "mysql:host=127.0.0.1;port=3306;dbname=artify;charset=utf8mb4";
try {
    $pdo = new PDO($dsn, 'root', '', [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    die("Erreur connexion BDD : " . $e->getMessage());
}
