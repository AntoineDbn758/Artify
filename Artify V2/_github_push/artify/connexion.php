<?php

/**
 * Ouvre la connexion PDO a la base. Tous les autres scripts l'incluent via
 * require_once. Le DSN est construit a partir des variables d'environnement
 * (DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD) fixees dans
 * docker-compose. Permet de switcher facilement entre local Docker, machine
 * de dev, ou prod sans toucher au code.
 */

// Connexion BDD multi-plateforme :
//   - macOS XAMPP : socket Unix si présent
//   - Docker      : variables d'env DB_HOST/DB_PORT (cf. docker-compose)
//   - Windows XAMPP / Linux : TCP 127.0.0.1:3306 par défaut
// getenv() avec fallback : marche aussi sans variables d'env (dev local rapide).
$dbname   = getenv('DB_NAME') ?: 'artify';
$user     = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';

// Sur Mac XAMPP on prefere le socket Unix (plus rapide que TCP),
// sinon on retombe sur host/port pour Docker et Windows.
$unix_socket = '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock';
if (PHP_OS_FAMILY !== 'Windows' && file_exists($unix_socket)) {
    // DSN socket : pas de host/port, MySQL est accede via fichier socket local.
    $dsn = "mysql:unix_socket=$unix_socket;dbname=$dbname;charset=utf8mb4";
} else {
    // DSN TCP : cas standard pour Docker (service mysql) et Windows.
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = getenv('DB_PORT') ?: '3306';
    // charset=utf8mb4 : supporte emojis et caracteres rares (utf8 classique = 3 octets seulement).
    $dsn  = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
}

try {
    // EMULATE_PREPARES=false force les vraies requetes preparees cote MySQL,
    // plus sur contre les injections que le mode emule.
    // ERRMODE_EXCEPTION : on attrape les erreurs SQL via try/catch au lieu de codes silencieux.
    // FETCH_ASSOC : par defaut on recupere des tableaux associatifs (pas numeriques + assoc).
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    // die() simple : pour un projet etudiant on accepte le message brut, en prod il faudrait logger.
    die("Erreur connexion BDD : " . $e->getMessage());
}
?>