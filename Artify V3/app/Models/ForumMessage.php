<?php
namespace App\Models;

use App\Core\Model;

class ForumMessage extends Model
{
    protected static string $table = 'forum_message';

    public function post(int $sujetId, int $userId, string $contenu): int
    {
        return $this->insert([
            'sujet_id'       => $sujetId,
            'utilisateur_id' => $userId,
            'contenu'        => $contenu,
        ]);
    }
}
