<?php
// index.php - Page d'accueil Artify (server-rendered).
require_once __DIR__ . '/includes/bootstrap.php';
$page_title = 'Artify - Plateforme des Créateurs';

$produits = $pdo->query(
  "SELECT p.id, p.nom, p.prix, p.materiaux, c.nom AS categorie, a.nom_boutique,
          ip.url AS image_url
     FROM produit p
     JOIN categorie c ON c.id = p.categorie_id
     JOIN artisan a   ON a.id = p.artisan_id
     LEFT JOIN image_produit ip ON ip.produit_id = p.id AND ip.est_principale = 1
    WHERE p.est_publie = 1
    ORDER BY p.created_at DESC
    LIMIT 6"
)->fetchAll();

$artisans = $pdo->query(
  "SELECT a.id, a.nom_boutique, a.specialite, a.description, u.prenom, u.nom, u.ville
     FROM artisan a JOIN utilisateur u ON u.id = a.utilisateur_id
    WHERE u.est_actif = 1
    ORDER BY a.created_at DESC
    LIMIT 6"
)->fetchAll();

$events = $pdo->query(
  "SELECT e.id, e.titre, e.lieu, e.ville, e.date_debut, e.prix_entree
     FROM evenement e
    WHERE e.est_publie = 1 AND e.date_debut >= NOW()
    ORDER BY e.date_debut ASC
    LIMIT 4"
)->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <h1>Découvrez les <em>créateurs</em> qui réinventent l'artisanat</h1>
  <p>Artify met en lumière les artisans et créateurs indépendants : céramique, bijouterie, textile, ébénisterie&hellip;
     Achetez en direct, suivez leurs ateliers, participez à leurs événements.</p>
  <p style="margin-top:18px">
    <a class="btn-primary" href="creations.php">Explorer les créations</a>
    <a class="btn-ghost" href="register_form.php" style="margin-left:8px">Devenir artisan</a>
  </p>
</section>

<section>
  <div style="display:flex;justify-content:space-between;align-items:baseline">
    <h2>Créations à la une</h2>
    <a href="creations.php" style="font-size:14px">Voir tout &rarr;</a>
  </div>
  <?php if (!$produits): ?>
    <div class="empty">Aucune création publiée pour le moment.</div>
  <?php else: ?>
    <div class="grid grid-3">
      <?php foreach ($produits as $p): ?>
        <a class="card" href="produit.php?id=<?= (int)$p['id'] ?>" style="color:inherit">
          <img class="thumb" src="<?= h($p['image_url'] ?: 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=600&h=400&fit=crop&q=80') ?>" alt="<?= h($p['nom']) ?>" loading="lazy">
          <h3><?= h($p['nom']) ?></h3>
          <div class="meta"><?= h($p['nom_boutique']) ?> · <?= h($p['categorie']) ?></div>
          <div class="price"><?= number_format((float)$p['prix'], 2, ',', ' ') ?> &euro;</div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<section style="margin-top:38px">
  <div style="display:flex;justify-content:space-between;align-items:baseline">
    <h2>Nos artisans</h2>
    <a href="artisans.php" style="font-size:14px">Voir tout &rarr;</a>
  </div>
  <?php if (!$artisans): ?>
    <div class="empty">Aucun artisan inscrit pour le moment.</div>
  <?php else: ?>
    <div class="grid grid-3">
      <?php foreach ($artisans as $a): ?>
        <a class="card" href="artisan.php?id=<?= (int)$a['id'] ?>" style="color:inherit">
          <img class="thumb" src="https://images.unsplash.com/photo-1556228720-195a672e8a03?w=600&h=400&fit=crop&q=80" alt="<?= h($a['nom_boutique']) ?>" loading="lazy">
          <h3><?= h($a['nom_boutique']) ?></h3>
          <div class="meta"><?= h($a['specialite'] ?: '-') ?><?= $a['ville'] ? ' · ' . h($a['ville']) : '' ?></div>
          <p><?= h(mb_substr($a['description'] ?? '', 0, 110)) ?></p>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<section style="margin-top:38px">
  <div style="display:flex;justify-content:space-between;align-items:baseline">
    <h2>Événements à venir</h2>
    <a href="evenements.php" style="font-size:14px">Voir tout &rarr;</a>
  </div>
  <?php if (!$events): ?>
    <div class="empty">Aucun événement à venir publié.</div>
  <?php else: ?>
    <div class="grid grid-2">
      <?php foreach ($events as $e): ?>
        <a class="card" href="evenement.php?id=<?= (int)$e['id'] ?>" style="color:inherit">
          <h3><?= h($e['titre']) ?></h3>
          <div class="meta">
            <?= h(date('d/m/Y H:i', strtotime($e['date_debut']))) ?>
            <?= $e['lieu'] ? ' · ' . h($e['lieu']) : '' ?>
            <?= $e['ville'] ? ' (' . h($e['ville']) . ')' : '' ?>
          </div>
          <div class="price"><?= $e['prix_entree'] > 0 ? number_format((float)$e['prix_entree'], 2, ',', ' ') . ' €' : 'Gratuit' ?></div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
