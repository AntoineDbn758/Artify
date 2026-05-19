<?php

/**
 * Annuaire des artisans actifs. Chaque artisan a une photo d'atelier choisie
 * selon sa specialite (mapping en debut de fichier vers des photos Unsplash
 * thematiques). Affiche aussi sa note moyenne et le nombre d'avis si > 0.
 */

require_once __DIR__ . '/includes/bootstrap.php';
$page_title = 'Artisans - Artify';

// On masque les artisans dont le compte a ete suspendu, mais on garde leurs produits accessibles en cache via produit.php.
$artisans = $pdo->query(
  "SELECT a.id, a.nom_boutique, a.specialite, a.description, a.note_moyenne, a.nb_avis,
          u.prenom, u.nom, u.ville
     FROM artisan a JOIN utilisateur u ON u.id = a.utilisateur_id
    WHERE u.est_actif = 1
    ORDER BY a.nom_boutique ASC"
)->fetchAll();

// Photo d'atelier par spécialité (Unsplash, URLs vérifiées HTTP 200).
$ATELIER_PHOTOS = [
    'Bijouterie'   => 'https://images.unsplash.com/photo-1606760227091-3dd870d97f1d?w=600&h=400&fit=crop&q=80',
    'Céramique'    => 'https://images.unsplash.com/photo-1493106819501-66d381c466f1?w=600&h=400&fit=crop&q=80',
    'Textile'      => 'https://images.unsplash.com/photo-1452860606245-08befc0ff44b?w=600&h=400&fit=crop&q=80',
    'Ébénisterie'  => 'https://images.unsplash.com/photo-1601058268499-e52658b8bb88?w=600&h=400&fit=crop&q=80',
    'Cuir'         => 'https://images.unsplash.com/photo-1591561954557-26941169b49e?w=600&h=400&fit=crop&q=80',
    'Verrerie'     => 'https://images.unsplash.com/photo-1493663284031-b7e3aefcae8e?w=600&h=400&fit=crop&q=80',
    'Peinture'     => 'https://images.unsplash.com/photo-1547891654-e66ed7ebb968?w=600&h=400&fit=crop&q=80',
    'Illustration' => 'https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?w=600&h=400&fit=crop&q=80',
];
$DEFAULT_ATELIER = 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=600&h=400&fit=crop&q=80';

include __DIR__ . '/includes/header.php';
?>
<div class="crumb"><a href="index.php">Accueil</a> &rsaquo; Artisans</div>
<h1>Nos artisans</h1>
<p>Découvrez les créateurs et créatrices qui font vivre Artify.</p>

<?php if (!$artisans): ?>
  <div class="empty">Aucun artisan inscrit pour le moment.</div>
<?php else: ?>
  <div class="grid grid-3" style="margin-top:20px">
    <?php // Mapping specialite vers photo Unsplash thematique, avec fallback si la specialite n'est pas connue.
    foreach ($artisans as $a):
        $atelier = $ATELIER_PHOTOS[$a['specialite']] ?? $DEFAULT_ATELIER;
    ?>
      <a class="card" href="artisan.php?id=<?= (int)$a['id'] ?>" style="color:inherit">
        <img class="thumb" src="<?= h($atelier) ?>" alt="<?= h($a['nom_boutique']) ?>" loading="lazy">
        <h3><?= h($a['nom_boutique']) ?></h3>
        <div class="meta">
          <?= h($a['specialite'] ?: '-') ?><?= $a['ville'] ? ' · ' . h($a['ville']) : '' ?>
        </div>
        <p><?= h(mb_substr($a['description'] ?? '', 0, 120)) ?><?= mb_strlen($a['description'] ?? '') > 120 ? '…' : '' ?></p>
        <?php if ((float)$a['note_moyenne'] > 0): ?>
          <div class="meta">Note : <?= number_format((float)$a['note_moyenne'], 1) ?> / 5 (<?= (int)$a['nb_avis'] ?>)</div>
        <?php endif; ?>
      </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
