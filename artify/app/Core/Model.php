<?php
namespace App\Core;

/**
 * Model de base — Active Record minimaliste.
 * Chaque modèle déclare $table et hérite des helpers find/all/insert/update/delete.
 */
abstract class Model
{
    protected static string $table = '';
    protected \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::pdo();
    }

    public function all(string $orderBy = 'id DESC', int $limit = 0): array
    {
        $sql = "SELECT * FROM " . static::$table . " ORDER BY $orderBy";
        if ($limit > 0) $sql .= " LIMIT " . (int)$limit;
        return $this->pdo->query($sql)->fetchAll();
    }

    public function find(int $id): ?array
    {
        $st = $this->pdo->prepare("SELECT * FROM " . static::$table . " WHERE id = ? LIMIT 1");
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    public function where(string $sql, array $params = []): array
    {
        $st = $this->pdo->prepare("SELECT * FROM " . static::$table . " WHERE $sql");
        $st->execute($params);
        return $st->fetchAll();
    }

    public function insert(array $data): int
    {
        $cols = array_keys($data);
        $place = array_fill(0, count($cols), '?');
        $sql = "INSERT INTO " . static::$table . " (" . implode(',', $cols) . ") VALUES (" . implode(',', $place) . ")";
        $this->pdo->prepare($sql)->execute(array_values($data));
        return (int)$this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $set = implode(', ', array_map(fn($c) => "$c = ?", array_keys($data)));
        $sql = "UPDATE " . static::$table . " SET $set WHERE id = ?";
        $vals = array_values($data); $vals[] = $id;
        $this->pdo->prepare($sql)->execute($vals);
    }

    public function delete(int $id): void
    {
        $this->pdo->prepare("DELETE FROM " . static::$table . " WHERE id = ?")->execute([$id]);
    }
}
