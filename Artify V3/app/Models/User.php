<?php
namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected static string $table = 'utilisateur';

    public function byEmail(string $email): ?array
    {
        $st = $this->pdo->prepare("SELECT * FROM utilisateur WHERE email = ? LIMIT 1");
        $st->execute([$email]);
        return $st->fetch() ?: null;
    }

    public function setPassword(int $id, string $newPlain): void
    {
        $hash = password_hash($newPlain, PASSWORD_BCRYPT);
        $this->pdo->prepare("UPDATE utilisateur SET mot_de_passe = ? WHERE id = ?")
                  ->execute([$hash, $id]);
    }

    /**
     * Validation force du mot de passe.
     * Renvoie [] si OK, sinon une liste d'erreurs.
     * Règles : 8+ caractères, 1 majuscule, 1 minuscule, 1 chiffre, 1 spécial.
     */
    public static function validatePassword(string $p): array
    {
        $errs = [];
        if (mb_strlen($p) < 8)         $errs[] = "8 caractères minimum.";
        if (!preg_match('/[A-Z]/', $p)) $errs[] = "Au moins une majuscule.";
        if (!preg_match('/[a-z]/', $p)) $errs[] = "Au moins une minuscule.";
        if (!preg_match('/[0-9]/', $p)) $errs[] = "Au moins un chiffre.";
        if (!preg_match('/[^A-Za-z0-9]/', $p)) $errs[] = "Au moins un caractère spécial.";
        return $errs;
    }
}
