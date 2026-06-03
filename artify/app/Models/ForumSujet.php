<?php
namespace App\Models;

use App\Core\Model;

class ForumSujet extends Model
{
    protected static string $table = 'forum_sujet';

    public function listing(?string $categorie = null, int $offset = 0, int $limit = 20): array
    {
        $sql = "
            SELECT s.*, CONCAT(u.prenom,' ',u.nom) AS auteur,
                   (SELECT COUNT(*) FROM forum_message m WHERE m.sujet_id = s.id) AS nb_msg,
                   (SELECT MAX(created_at) FROM forum_message m WHERE m.sujet_id = s.id) AS last_msg_at
              FROM forum_sujet s
              JOIN utilisateur u ON u.id = s.utilisateur_id
        ";
        $params = [];
        if ($categorie) { $sql .= " WHERE s.categorie = ? "; $params[] = $categorie; }
        $sql .= " ORDER BY s.est_epingle DESC, COALESCE(last_msg_at, s.created_at) DESC
                 LIMIT $limit OFFSET $offset";
        $st = $this->pdo->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    public function count(?string $categorie = null): int
    {
        if ($categorie) {
            $st = $this->pdo->prepare("SELECT COUNT(*) FROM forum_sujet WHERE categorie = ?");
            $st->execute([$categorie]);
        } else {
            $st = $this->pdo->query("SELECT COUNT(*) FROM forum_sujet");
        }
        return (int)$st->fetchColumn();
    }

    public function withMessages(int $id): ?array
    {
        $sujet = $this->find($id);
        if (!$sujet) return null;
        $st = $this->pdo->prepare("SELECT prenom, nom FROM utilisateur WHERE id = ?");
        $st->execute([$sujet['utilisateur_id']]);
        $u = $st->fetch();
        $sujet['auteur'] = trim(($u['prenom'] ?? '') . ' ' . ($u['nom'] ?? ''));

        $st = $this->pdo->prepare(
            "SELECT m.*, CONCAT(u.prenom,' ',u.nom) AS auteur, u.role
               FROM forum_message m
               JOIN utilisateur u ON u.id = m.utilisateur_id
              WHERE m.sujet_id = ?
              ORDER BY m.created_at ASC"
        );
        $st->execute([$id]);
        $sujet['messages'] = $st->fetchAll();
        return $sujet;
    }
}
