<?php
namespace App\Models;

use App\Core\Model;

/**
 * Messagerie interne entre utilisateurs.
 * Réutilise la table `messagerie` du schéma existant.
 */
class Message extends Model
{
    protected static string $table = 'messagerie';

    /** Conversations distinctes de l'utilisateur (dernier message par interlocuteur). */
    public function conversationsFor(int $userId): array
    {
        $sql = "
            SELECT u.id AS contact_id,
                   CONCAT(u.prenom,' ',u.nom) AS contact_nom,
                   u.email AS contact_email,
                   MAX(m.created_at) AS last_at,
                   (SELECT contenu FROM messagerie
                     WHERE (expediteur_id=u.id AND destinataire_id=?) OR (expediteur_id=? AND destinataire_id=u.id)
                     ORDER BY created_at DESC LIMIT 1) AS last_msg
              FROM messagerie m
              JOIN utilisateur u ON u.id = IF(m.expediteur_id = ?, m.destinataire_id, m.expediteur_id)
             WHERE m.expediteur_id = ? OR m.destinataire_id = ?
             GROUP BY u.id, u.prenom, u.nom, u.email
             ORDER BY last_at DESC
        ";
        $st = $this->pdo->prepare($sql);
        $st->execute([$userId, $userId, $userId, $userId, $userId]);
        return $st->fetchAll();
    }

    /** Fil de discussion entre deux utilisateurs. */
    public function thread(int $a, int $b): array
    {
        $st = $this->pdo->prepare(
            "SELECT m.*, CONCAT(u.prenom,' ',u.nom) AS expediteur_nom
               FROM messagerie m
               JOIN utilisateur u ON u.id = m.expediteur_id
              WHERE (expediteur_id=? AND destinataire_id=?) OR (expediteur_id=? AND destinataire_id=?)
              ORDER BY m.created_at ASC"
        );
        $st->execute([$a, $b, $b, $a]);
        return $st->fetchAll();
    }

    public function send(int $from, int $to, string $contenu): int
    {
        return $this->insert([
            'expediteur_id'   => $from,
            'destinataire_id' => $to,
            'contenu'         => $contenu,
            'lu'              => 0,
        ]);
    }

    public function markRead(int $userId, int $contactId): void
    {
        $this->pdo->prepare(
            "UPDATE messagerie SET lu=1
              WHERE destinataire_id=? AND expediteur_id=? AND lu=0"
        )->execute([$userId, $contactId]);
    }

    public function unreadCount(int $userId): int
    {
        $st = $this->pdo->prepare("SELECT COUNT(*) FROM messagerie WHERE destinataire_id=? AND lu=0");
        $st->execute([$userId]);
        return (int)$st->fetchColumn();
    }
}
