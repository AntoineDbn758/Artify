<?php
require_once __DIR__ . '/includes/bootstrap.php';
$page_title = 'Créations - Artify';

$cat = (int)($_GET['cat'] ?? 0);
$cats = $pdo->query("SELECT id, nom FROM categorie ORDER BY nom")->fetchAll();

$sql = "SELECT p.id, p.nom, p.prix, p.materiaux, c.nom AS categorie, a.nom_boutique, a.id AS aid,
               ip.url AS image_url
          FROM produit p
          JOIN categorie c ON c.id = p.categorie_id
          JOIN artisan a   ON a.id = p.artisan_id
          LEFT JOIN image_produit ip ON ip.produit_id = p.id AND ip.est_principale = 1
         WHERE p.est_publie = 1 ";
$params = [];
if ($cat > 0) { $sql .= " AND p.categorie_id = ? "; $params[] = $cat; }
$sql .= " ORDER BY p.created_at DESC LIMIT 60";
$st = $pdo->prepare($sql);
$st->execute($params);
$produits = $st->fetchAll();

include __DIR__ . '/includes/header.php';
?>
<div class="crumb"><a href="index.php">Accueil</a> &rsaquo; Créations</div>
<h1>Créations</h1>
<p>Toutes les œuvres et objets proposés par nos artisans.</p>

<div style="margin:18px 0;display:flex;gap:8px;flex-wrap:wrap">
  <a class="btn-ghost btn-small <?= $cat === 0 ? 'btn-primary' : '' ?>" href="creations.php">Toutes</a>
  <?php foreach ($cats as $c): ?>
    <a class="btn-ghost btn-small <?= $cat === (int)$c['id'] ? 'btn-primary' : '' ?>"
       href="creations.php?cat=<?= (int)$c['id'] ?>"><?= h($c['nom']) ?></a>
  <?php endforeach; ?>
</div>

<?php if (!$produits): ?>
  <div class="empty">Aucune création trouvée pour ce filtre.</div>
<?php else: ?>
  <div class="grid grid-3">
    <?php foreach ($produits as $p): ?>
      <a class="card" href="produit.php?id=<?= (int)$p['id'] ?>" style="color:inherit">
        <img class="thumb" src="<?= h($p['image_url'] ?: 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=600&h=400&fit=crop&q=80') ?>" alt="<?= h($p['nom']) ?>" loading="lazy">
        <h3><?= h($p['nom']) ?></h3>
        <div class="meta"><?= h($p['nom_boutique']) ?> · <?= h($p['categorie']) ?></div>
        <?php if (!empty($p['materiaux'])): ?>
          <div class="meta"><?= h(mb_substr($p['materiaux'], 0, 80)) ?></div>
        <?php endif; ?>
        <div class="price"><?= number_format((float)$p['prix'], 2, ',', ' ') ?> &euro;</div>
      </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
