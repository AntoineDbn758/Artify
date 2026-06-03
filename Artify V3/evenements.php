<?php
require_once __DIR__ . '/includes/bootstrap.php';
$page_title = 'Événements — Artify';

// Filtre : à venir (par défaut) / passés / tous
$filtre = $_GET['f'] ?? 'venir';

$sql = "SELECT e.id, e.titre, e.description, e.lieu, e.ville, e.date_debut, e.date_fin,
               e.prix_entree, e.capacite_max, e.image_url, a.nom_boutique
          FROM evenement e
          JOIN artisan a ON a.id = e.artisan_id
         WHERE e.est_publie = 1 ";
if ($filtre === 'venir')   $sql .= " AND e.date_debut >= NOW() ORDER BY e.date_debut ASC";
elseif ($filtre === 'passes') $sql .= " AND e.date_debut <  NOW() ORDER BY e.date_debut DESC";
else                        $sql .= " ORDER BY e.date_debut DESC";

$events = $pdo->query($sql)->fetchAll();

$_DEFAULT_EVT = 'https://images.unsplash.com/photo-1559563458-527698bf5295?w=800&h=450&fit=crop&q=80';

include __DIR__ . '/includes/header.php';
?>
<div class="crumb"><a href="index.php">Accueil</a> &rsaquo; Événements</div>
<h1>Événements <span class="meta">(<?= count($events) ?>)</span></h1>
<p>Ateliers, marchés et expositions organisés par nos artisans.</p>

<div class="forum-cats" style="margin:14px 0">
  <a class="<?= $filtre==='venir'?'active':'' ?>" href="evenements.php?f=venir">À venir</a>
  <a class="<?= $filtre==='passes'?'active':'' ?>" href="evenements.php?f=passes">Passés</a>
  <a class="<?= $filtre==='tous'?'active':'' ?>"  href="evenements.php?f=tous">Tous</a>
</div>

<?php if (!$events): ?>
  <div class="empty">Aucun événement <?= $filtre==='passes' ? 'passé' : 'à venir' ?>.</div>
<?php else: ?>
  <div class="grid grid-2">
    <?php foreach ($events as $e): $passe = strtotime($e['date_debut']) < time(); ?>
      <a class="card" href="evenement.php?id=<?= (int)$e['id'] ?>" style="color:inherit">
        <img class="thumb" src="<?= h($e['image_url'] ?: $_DEFAULT_EVT) ?>"
             alt="<?= h($e['titre']) ?>" loading="lazy">
        <h3><?= h($e['titre']) ?> <?php if ($passe): ?><span class="badge muted">passé</span><?php endif; ?></h3>
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
