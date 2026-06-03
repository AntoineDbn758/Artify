<?php
namespace App\Models;

use App\Core\Model;

class PasswordReset extends Model
{
    protected static string $table = 'password_reset';

    /** Crée un token unique pour un user et retourne le token brut (à envoyer). */
    public function create(int $userId, int $ttlMinutes = 60): string
    {
        $token = bin2hex(random_bytes(32));
        $hash  = hash('sha256', $token);
        $exp   = (new \DateTime("+$ttlMinutes minutes"))->format('Y-m-d H:i:s');
        $this->insert(['utilisateur_id' => $userId, 'token_hash' => $hash, 'expire_at' => $exp]);
        return $token;
    }

    /** Trouve un reset valide à partir du token brut. */
    public function findValid(string $token): ?array
    {
        $hash = hash('sha256', $token);
        $st = $this->pdo->prepare(
            "SELECT * FROM password_reset
              WHERE token_hash = ? AND used_at IS NULL AND expire_at > NOW()
              LIMIT 1"
        );
        $st->execute([$hash]);
        return $st->fetch() ?: null;
    }

    public function markUsed(int $id): void
    {
        $this->pdo->prepare("UPDATE password_reset SET used_at = NOW() WHERE id = ?")->execute([$id]);
    }
}
