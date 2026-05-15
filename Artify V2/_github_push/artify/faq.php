<?php
require_once __DIR__ . '/includes/bootstrap.php';
$page_title = 'FAQ - Artify';
$faqs = $pdo->query("SELECT question, reponse FROM faq WHERE est_actif = 1 ORDER BY ordre ASC, id ASC")->fetchAll();
include __DIR__ . '/includes/header.php';
?>
<div class="crumb"><a href="index.php">Accueil</a> &rsaquo; FAQ</div>
<h1>Foire aux questions</h1>
<p>Les réponses aux questions les plus fréquentes sur Artify.</p>

<?php if (!$faqs): ?>
  <div class="empty">Aucune question publiée pour le moment.</div>
<?php else: ?>
  <div style="margin-top:20px">
    <?php foreach ($faqs as $f): ?>
      <div class="card" style="margin-bottom:14px">
        <h3><?= h($f['question']) ?></h3>
        <p><?= nl2br(h($f['reponse'])) ?></p>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
