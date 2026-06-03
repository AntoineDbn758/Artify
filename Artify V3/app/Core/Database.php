<?php
namespace App\Core;

use PDO;
use PDOException;

/**
 * Connexion BDD — Singleton.
 * Auto-détecte XAMPP local (127.0.0.1) ou Docker (DB_HOST=db).
 */
final class Database
{
    private static ?PDO $instance = null;

    public static function pdo(): PDO
    {
        if (self::$instance !== null) return self::$instance;
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASSWORD') ?: '';
        $dsn  = "mysql:host=$host;port=3306;dbname=artify;charset=utf8mb4";
        try {
            self::$instance = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die("Erreur connexion BDD : " . $e->getMessage());
        }
        return self::$instance;
    }
}
