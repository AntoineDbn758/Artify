<?php /** @var array $thread */ /** @var array $contact */
$me = (int)$_SESSION['user_id']; ?>
<div class="crumb"><a href="messages.php">Messagerie</a> &rsaquo; <?= htmlspecialchars($contact['prenom'] . ' ' . $contact['nom']) ?></div>
<h1>Conversation avec <?= htmlspecialchars($contact['prenom'] . ' ' . $contact['nom']) ?></h1>

<div class="msg-thread-box">
  <?php if (!$thread): ?>
    <div class="empty">Aucun message. Envoyez le premier.</div>
  <?php endif; ?>
  <?php foreach ($thread as $m): $own = ((int)$m['expediteur_id'] === $me); ?>
    <div class="msg-bubble <?= $own ? 'own' : 'other' ?>">
      <div class="msg-bubble-meta">
        <?= htmlspecialchars($m['expediteur_nom']) ?>
        · <?= htmlspecialchars(date('d/m/Y H:i', strtotime($m['created_at']))) ?>
      </div>
      <div class="msg-bubble-body"><?= nl2br(htmlspecialchars($m['contenu'])) ?></div>
    </div>
  <?php endforeach; ?>
</div>

<form class="msg-form" method="post" action="messages.php?action=send">
  <input type="hidden" name="destinataire_id" value="<?= (int)$contact['id'] ?>">
  <textarea name="contenu" required minlength="1" placeholder="Écrire un message…" rows="3"></textarea>
  <button class="btn-primary" type="submit">Envoyer</button>
</form>
