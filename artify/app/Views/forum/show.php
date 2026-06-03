<?php /** @var array $sujet */ ?>
<div class="crumb"><a href="forum.php">Forum</a> &rsaquo; <?= htmlspecialchars($sujet['titre']) ?></div>
<h1><?= htmlspecialchars($sujet['titre']) ?></h1>
<div class="meta">par <?= htmlspecialchars($sujet['auteur']) ?> · <?= htmlspecialchars(date('d/m/Y H:i', strtotime($sujet['created_at']))) ?> · <?= htmlspecialchars($sujet['categorie']) ?></div>

<?php foreach ($sujet['messages'] as $m): ?>
  <div class="forum-msg">
    <div class="forum-msg-head">
      <strong><?= htmlspecialchars($m['auteur']) ?></strong>
      <?php if ($m['role'] === 'admin'): ?><span class="badge ok">admin</span><?php endif; ?>
      <?php if ($m['role'] === 'artisan'): ?><span class="badge">artisan</span><?php endif; ?>
      <span class="meta">· <?= htmlspecialchars(date('d/m/Y H:i', strtotime($m['created_at']))) ?></span>
    </div>
    <div class="forum-msg-body"><?= nl2br(htmlspecialchars($m['contenu'])) ?></div>
  </div>
<?php endforeach; ?>

<a id="bas"></a>
<?php if (!$sujet['est_ferme'] && !empty($_SESSION['user_id'])): ?>
  <form method="post" action="forum.php?action=reply" class="forum-reply">
    <input type="hidden" name="sujet_id" value="<?= (int)$sujet['id'] ?>">
    <label>Votre réponse</label>
    <textarea name="contenu" required rows="4" placeholder="Écrivez votre réponse…"></textarea>
    <button class="btn-primary" type="submit">Publier</button>
  </form>
<?php elseif ($sujet['est_ferme']): ?>
  <div class="empty">Sujet fermé.</div>
<?php else: ?>
  <p><a href="login_form.php">Connectez-vous</a> pour répondre.</p>
<?php endif; ?>
