<?php /** @var array $conversations */ /** @var int $unread */ ?>
<div class="crumb"><a href="index.php">Accueil</a> &rsaquo; Messagerie</div>
<h1>Messagerie <?php if ($unread > 0): ?><span class="badge err"><?= (int)$unread ?> non lus</span><?php endif; ?></h1>
<p>Vos conversations avec les artisans et clients.</p>

<?php if (!$conversations): ?>
  <div class="empty">Aucune conversation pour le moment. Contactez un artisan depuis sa fiche.</div>
<?php else: ?>
<div class="msg-list">
  <?php foreach ($conversations as $c): ?>
    <a class="msg-thread" href="messages.php?action=show&id=<?= (int)$c['contact_id'] ?>">
      <div class="msg-thread-head">
        <strong><?= htmlspecialchars($c['contact_nom']) ?></strong>
        <span class="meta"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($c['last_at']))) ?></span>
      </div>
      <div class="msg-thread-preview"><?= htmlspecialchars(mb_substr($c['last_msg'] ?? '', 0, 140)) ?></div>
    </a>
  <?php endforeach; ?>
</div>
<?php endif; ?>
