<?php
require_once __DIR__ . '/includes/bootstrap.php';
$page_title = 'Recherche - Artify';

$q = trim($_GET['q'] ?? '');
$results_prod = [];
$results_art  = [];

if ($q !== '') {
    // Log de la recherche
    try {
        $pdo->prepare("INSERT INTO recherche_log (utilisateur_id, terme) VALUES (?, ?)")
            ->execute([current_user_id(), mb_substr($q, 0, 200)]);
    } catch (PDOException $e) { /* non-bloquant */ }

    $like = '%' . $q . '%';
    $st = $pdo->prepare(
      "SELECT p.id, p.nom, p.prix, c.nom AS categorie, a.nom_boutique,
              ip.url AS image_url
         FROM produit p
         JOIN categorie c ON c.id = p.categorie_id
         JOIN artisan a   ON a.id = p.artisan_id
         LEFT JOIN image_produit ip ON ip.produit_id = p.id AND ip.est_principale = 1
        WHERE p.est_publie = 1 AND (p.nom LIKE ? OR p.description LIKE ? OR p.materiaux LIKE ?)
        ORDER BY p.created_at DESC LIMIT 30");
    $st->execute([$like, $like, $like]);
    $results_prod = $st->fetchAll();

    $st = $pdo->prepare(
      "SELECT a.id, a.nom_boutique, a.specialite, u.ville
         FROM artisan a JOIN utilisateur u ON u.id = a.utilisateur_id
        WHERE u.est_actif = 1 AND (a.nom_boutique LIKE ? OR a.specialite LIKE ? OR a.description LIKE ?)
        LIMIT 30");
    $st->execute([$like, $like, $like]);
    $results_art = $st->fetchAll();
}

include __DIR__ . '/includes/header.php';
?>
<div class="crumb"><a href="index.php">Accueil</a> &rsaquo; Recherche</div>
<h1>Recherche</h1>

<form method="get" action="recherche.php" style="display:flex;gap:8px;margin:14px 0;max-width:600px">
  <input class="form-row" style="flex:1;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px"
         type="text" name="q" value="<?= h($q) ?>" placeholder="Mot-clé : céramique, bague, bois…" autofocus>
  <button class="btn-primary" type="submit">Chercher</button>
</form>

<?php if ($q === ''): ?>
  <p>Saisissez un mot-clé pour rechercher dans les créations et les artisans.</p>
<?php else: ?>
  <h2>Créations correspondant à « <?= h($q) ?> » (<?= count($results_prod) ?>)</h2>
  <?php if (!$results_prod): ?>
    <div class="empty">Aucune création trouvée.</div>
  <?php else: ?>
    <div class="grid grid-3">
      <?php foreach ($results_prod as $p): ?>
        <a class="card" href="produit.php?id=<?= (int)$p['id'] ?>" style="color:inherit">
          <img class="thumb" src="<?= h($p['image_url'] ?: 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=600&h=400&fit=crop&q=80') ?>" alt="<?= h($p['nom']) ?>" loading="lazy">
          <h3><?= h($p['nom']) ?></h3>
          <div class="meta"><?= h($p['nom_boutique']) ?> · <?= h($p['categorie']) ?></div>
          <div class="price"><?= number_format((float)$p['prix'], 2, ',', ' ') ?> &euro;</div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <h2 style="margin-top:30px">Artisans correspondants (<?= count($results_art) ?>)</h2>
  <?php if (!$results_art): ?>
    <div class="empty">Aucun artisan trouvé.</div>
  <?php else: ?>
    <div class="grid grid-3">
      <?php foreach ($results_art as $a): ?>
        <a class="card" href="artisan.php?id=<?= (int)$a['id'] ?>" style="color:inherit">
          <h3><?= h($a['nom_boutique']) ?></h3>
          <div class="meta"><?= h($a['specialite'] ?: '-') ?><?= $a['ville'] ? ' · ' . h($a['ville']) : '' ?></div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
