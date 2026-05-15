<?php
require_once __DIR__ . '/includes/bootstrap.php';
$id = (int)($_GET['id'] ?? 0);

$st = $pdo->prepare(
  "SELECT a.*, u.prenom, u.nom, u.ville, u.bio, u.avatar_url
     FROM artisan a JOIN utilisateur u ON u.id = a.utilisateur_id
    WHERE a.id = ? AND u.est_actif = 1");
$st->execute([$id]);
$artisan = $st->fetch();
if (!$artisan) {
    http_response_code(404);
    $page_title = 'Artisan introuvable';
    include __DIR__ . '/includes/header.php';
    echo '<div class="empty">Cet artisan n\'existe pas ou n\'est plus actif.</div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}
$page_title = $artisan['nom_boutique'] . ' - Artify';

$prods = $pdo->prepare(
  "SELECT p.id, p.nom, p.prix, p.materiaux, c.nom AS categorie,
          ip.url AS image_url
     FROM produit p
     JOIN categorie c ON c.id = p.categorie_id
     LEFT JOIN image_produit ip ON ip.produit_id = p.id AND ip.est_principale = 1
    WHERE p.artisan_id = ? AND p.est_publie = 1
    ORDER BY p.created_at DESC");
$prods->execute([$id]);
$prods = $prods->fetchAll();

include __DIR__ . '/includes/header.php';
?>
<div class="crumb"><a href="index.php">Accueil</a> &rsaquo; <a href="artisans.php">Artisans</a> &rsaquo; <?= h($artisan['nom_boutique']) ?></div>

<div class="detail">
  <?php
    $ATELIER_BIG = [
      'Bijouterie'   => 'https://images.unsplash.com/photo-1606760227091-3dd870d97f1d?w=800&h=600&fit=crop&q=80',
      'Céramique'    => 'https://images.unsplash.com/photo-1493106819501-66d381c466f1?w=800&h=600&fit=crop&q=80',
      'Textile'      => 'https://images.unsplash.com/photo-1452860606245-08befc0ff44b?w=800&h=600&fit=crop&q=80',
      'Ébénisterie'  => 'https://images.unsplash.com/photo-1601058268499-e52658b8bb88?w=800&h=600&fit=crop&q=80',
    ];
    $atelier_big = $ATELIER_BIG[$artisan['specialite']]
        ?? 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=800&h=600&fit=crop&q=80';
  ?>
  <img class="visual" src="<?= h($atelier_big) ?>" alt="<?= h($artisan['nom_boutique']) ?>">
  <div>
    <h1><?= h($artisan['nom_boutique']) ?></h1>
    <p><strong><?= h($artisan['prenom'] . ' ' . $artisan['nom']) ?></strong>
       <?= $artisan['ville'] ? ' &middot; ' . h($artisan['ville']) : '' ?>
    </p>
    <?php if (!empty($artisan['specialite'])): ?>
      <span class="tag"><?= h($artisan['specialite']) ?></span>
    <?php endif; ?>
    <?php if ($artisan['verifie']): ?>
      <span class="tag" style="background:#e6f4eb;color:#1e5a35">Vérifié</span>
    <?php endif; ?>
    <dl>
      <dt>Description</dt><dd><?= h($artisan['description'] ?: '-') ?></dd>
      <?php if (!empty($artisan['site_web'])): ?>
        <dt>Site web</dt><dd><a href="<?= h($artisan['site_web']) ?>" rel="noopener" target="_blank"><?= h($artisan['site_web']) ?></a></dd>
      <?php endif; ?>
      <?php if (!empty($artisan['instagram'])): ?>
        <dt>Instagram</dt><dd><?= h($artisan['instagram']) ?></dd>
      <?php endif; ?>
      <?php if ((float)$artisan['note_moyenne'] > 0): ?>
        <dt>Note</dt><dd><?= number_format((float)$artisan['note_moyenne'], 1) ?> / 5 (<?= (int)$artisan['nb_avis'] ?> avis)</dd>
      <?php endif; ?>
    </dl>
  </div>
</div>

<h2 style="margin-top:30px">Créations de cet artisan</h2>
<?php if (!$prods): ?>
  <div class="empty">Pas encore de création publiée.</div>
<?php else: ?>
  <div class="grid grid-3">
    <?php foreach ($prods as $p): ?>
      <a class="card" href="produit.php?id=<?= (int)$p['id'] ?>" style="color:inherit">
        <img class="thumb" src="<?= h($p['image_url'] ?: 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=600&h=400&fit=crop&q=80') ?>" alt="<?= h($p['nom']) ?>" loading="lazy">
        <h3><?= h($p['nom']) ?></h3>
        <div class="meta"><?= h($p['categorie']) ?></div>
        <div class="price"><?= number_format((float)$p['prix'], 2, ',', ' ') ?> &euro;</div>
      </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
