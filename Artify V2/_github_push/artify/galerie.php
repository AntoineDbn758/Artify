<?php

/**
 * Galerie virtuelle : mosaique de visuels des creations soumis par les
 * artisans. Les images sont stockees dans la table galerie avec leur URL et
 * un titre/description optionnels.
 */

require_once __DIR__ . '/includes/bootstrap.php';
$page_title = 'Galerie - Artify';

// Limite a 60 pour eviter une page interminable, suffisant pour donner un apercu de la diversite des creations.
$items = $pdo->query(
  "SELECT g.id, g.image_url, g.titre, g.description, a.nom_boutique, a.id AS aid
     FROM galerie g JOIN artisan a ON a.id = g.artisan_id
    WHERE g.est_publie = 1
    ORDER BY g.created_at DESC
    LIMIT 60"
)->fetchAll();

include __DIR__ . '/includes/header.php';
?>
<div class="crumb"><a href="index.php">Accueil</a> &rsaquo; Galerie</div>
<h1>Galerie</h1>
<p>Une sélection de pièces présentées par nos artisans.</p>

<?php if (!$items): ?>
  <div class="empty">La galerie est vide pour le moment.</div>
<?php else: ?>
  <div class="grid grid-3" style="margin-top:20px">
    <?php foreach ($items as $g): ?>
      <div class="card">
        <img class="thumb" src="<?= h(!empty($g['image_url']) ? $g['image_url'] : 'https://images.unsplash.com/photo-1547891654-e66ed7ebb968?w=600&h=400&fit=crop&q=80') ?>" alt="<?= h($g['titre'] ?: 'Sans titre') ?>" loading="lazy">
        <h3><?= h($g['titre'] ?: 'Sans titre') ?></h3>
        <div class="meta">par <a href="artisan.php?id=<?= (int)$g['aid'] ?>"><?= h($g['nom_boutique']) ?></a></div>
        <?php if (!empty($g['description'])): ?>
          <p><?= h(mb_substr($g['description'], 0, 120)) ?></p>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
