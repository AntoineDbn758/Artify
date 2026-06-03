<?php
namespace App\Core;

use PDO;
use PDOException;

/**
 * Connexion BDD — Singleton.
 * XAMPP local : 127.0.0.1:3306, user root sans mot de passe, base "artify".
 */
final class Database
{
    private static ?PDO $instance = null;

    public static function pdo(): PDO
    {
        if (self::$instance !== null) return self::$instance;
        $dsn = "mysql:host=127.0.0.1;port=3306;dbname=artify;charset=utf8mb4";
        try {
            self::$instance = new PDO($dsn, 'root', '', [
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
