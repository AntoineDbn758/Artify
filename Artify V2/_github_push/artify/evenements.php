<?php

/**
 * Liste des evenements futurs uniquement (clause WHERE date_debut >= NOW()).
 * Chaque evenement a une photo, un lieu, une date et un prix d'entree
 * (souvent gratuit).
 */

require_once __DIR__ . '/includes/bootstrap.php';
$page_title = 'Événements - Artify';

// On filtre sur date_debut >= NOW() pour ne jamais lister un evenement deja passe, meme s'il est encore publie en base.
$events = $pdo->query(
  "SELECT e.id, e.titre, e.description, e.lieu, e.ville, e.date_debut, e.date_fin,
          e.prix_entree, e.capacite_max, e.image_url, a.nom_boutique
     FROM evenement e
     JOIN artisan a ON a.id = e.artisan_id
    WHERE e.est_publie = 1 AND e.date_debut >= NOW()
    ORDER BY e.date_debut ASC"
)->fetchAll();

$_DEFAULT_EVT = 'https://images.unsplash.com/photo-1559563458-527698bf5295?w=800&h=450&fit=crop&q=80';

include __DIR__ . '/includes/header.php';
?>
<div class="crumb"><a href="index.php">Accueil</a> &rsaquo; Événements</div>
<h1>Événements à venir</h1>
<p>Ateliers, marchés et expositions organisés par nos artisans.</p>

<?php if (!$events): ?>
  <div class="empty">Aucun événement à venir n'est publié.</div>
<?php else: ?>
  <div class="grid grid-2" style="margin-top:20px">
    <?php foreach ($events as $e): ?>
      <a class="card" href="evenement.php?id=<?= (int)$e['id'] ?>" style="color:inherit">
        <img class="thumb" src="<?= h($e['image_url'] ?: $_DEFAULT_EVT) ?>"
             alt="<?= h($e['titre']) ?>" loading="lazy">
        <h3><?= h($e['titre']) ?></h3>
        <div class="meta">
          <?= h(date('d/m/Y H:i', strtotime($e['date_debut']))) ?>
          <?= $e['lieu'] ? ' · ' . h($e['lieu']) : '' ?>
          <?= $e['ville'] ? ' (' . h($e['ville']) . ')' : '' ?>
        </div>
        <p><?= h(mb_substr($e['description'] ?? '', 0, 140)) ?><?= mb_strlen($e['description'] ?? '') > 140 ? '…' : '' ?></p>
        <div class="meta">Organisé par <?= h($e['nom_boutique']) ?></div>
        <div class="price"><?= $e['prix_entree'] > 0 ? number_format((float)$e['prix_entree'], 2, ',', ' ') . ' €' : 'Gratuit' ?></div>
      </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
