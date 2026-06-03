<?php
require_once __DIR__ . '/includes/bootstrap.php';
$page_title = 'CGU — Artify';
$cgu = $pdo->query("SELECT contenu, version, date_effet FROM cgu WHERE est_actif = 1 ORDER BY date_effet DESC LIMIT 1")->fetch();
include __DIR__ . '/includes/header.php';
?>
<div class="crumb"><a href="index.php">Accueil</a> &rsaquo; CGU</div>
<h1>Conditions Générales d'Utilisation</h1>

<?php if (!$cgu): ?>
  <div class="card">
    <p class="meta">Version 1.0 — par défaut</p>
    <h3>1. Objet</h3>
    <p>Artify est une plateforme mettant en relation des artisans/créateurs et des visiteurs souhaitant
       découvrir, suivre et acheter leurs créations.</p>
    <h3>2. Inscription</h3>
    <p>L'inscription est libre et gratuite. L'utilisateur s'engage à fournir des informations exactes et à
       respecter la charte de la plateforme.</p>
    <h3>3. Rôles</h3>
    <p>Trois rôles cohabitent : visiteur (compte basique), artisan (peut publier produits et événements),
       admin (gestion de la plateforme).</p>
    <h3>4. Contenu</h3>
    <p>Chaque artisan reste propriétaire de ses créations et garantit qu'il dispose des droits sur les
       images et descriptions publiées.</p>
    <h3>5. Modération</h3>
    <p>Artify se réserve le droit de désactiver tout contenu ou compte ne respectant pas les présentes
       conditions.</p>
    <h3>6. Limitation de responsabilité</h3>
    <p>Artify est un projet pédagogique : aucune garantie commerciale n'est offerte sur la disponibilité
       du service ou la véracité des annonces.</p>
  </div>
<?php else: ?>
  <div class="card">
    <p class="meta">Version <?= h($cgu['version']) ?> — en vigueur depuis le <?= h(date('d/m/Y', strtotime($cgu['date_effet']))) ?></p>
    <?= nl2br(h($cgu['contenu'])) ?>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
