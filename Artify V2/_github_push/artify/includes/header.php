<?php

/**
 * Header HTML commun a toutes les pages publiques : logo Artify, navigation
 * principale, barre de recherche, boutons de connexion ou avatar si
 * l'utilisateur est deja loggue. La nav est sticky (reste en haut au scroll)
 * et reagit au responsive via media queries dans style.css.
 */

// includes/header.php - en-tête commun (nav + ouverture <main>).
require_once __DIR__ . '/bootstrap.php';
// Titre par defaut surcharge par chaque page si besoin avant l'include.
$page_title = $page_title ?? 'Artify - Plateforme des Créateurs';
// $base = chemin relatif vers la racine du site (utilisé pour CSS et liens nav)
// Les pages dans backoffice/ doivent définir $base = '../' avant d'inclure le header.
$base = $base ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($page_title) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= h($base) ?>css/style.css">
<script defer src="<?= h($base) ?>js/main.js"></script>
</head>
<body>
<div class="accent-strip"></div>
<nav>
  <a class="logo" href="<?= h($base) ?>index.php">
    <span class="logo-a">A</span>
    <span class="logo-text">Artify</span>
  </a>
  <ul class="nav-links">
    <li><a href="<?= h($base) ?>index.php">Accueil</a></li>
    <li><a href="<?= h($base) ?>creations.php">Créations</a></li>
    <li><a href="<?= h($base) ?>artisans.php">Artisans</a></li>
    <li><a href="<?= h($base) ?>evenements.php">Événements</a></li>
    <li><a href="<?= h($base) ?>galerie.php">Galerie</a></li>
    <li><a href="<?= h($base) ?>faq.php">FAQ</a></li>
    <li><a href="<?= h($base) ?>contact.php">Contact</a></li>
  </ul>
  <!-- Recherche en GET : URL partageable et indexable. -->
  <form class="nav-search" action="<?= h($base) ?>recherche.php" method="get">
    <input type="text" name="q" placeholder="Rechercher…" value="<?= h($_GET['q'] ?? '') ?>">
    <button type="submit" class="btn-ghost">OK</button>
  </form>
  <div class="nav-actions">
  <?php if (is_logged()): ?>
    <?php /* Raccourci specifique au role pour acceder vite a l'espace de l'utilisateur. */ ?>
    <?php if (is_admin()): ?>
      <a class="btn-ghost" href="<?= h($base) ?>backoffice/index.php">Admin</a>
    <?php elseif (is_artisan()): ?>
      <a class="btn-ghost" href="<?= h($base) ?>boutique.php">Ma boutique</a>
    <?php endif; ?>
    <a class="btn-ghost" href="<?= h($base) ?>profile.php">Profil</a>
    <a class="btn-primary" href="<?= h($base) ?>logout.php">Déconnexion</a>
  <?php else: ?>
    <a class="btn-ghost" href="<?= h($base) ?>login_form.php">Se connecter</a>
    <a class="btn-primary" href="<?= h($base) ?>register_form.php">Créer un compte</a>
  <?php endif; ?>
  </div>
</nav>
<main class="page-main">
<?php /* Affichage des flash messages : flash_pop vide la pile pour eviter le doublon au refresh. */ ?>
<?php foreach (flash_pop() as $f): ?>
  <div class="flash flash-<?= h($f['type']) ?>"><?= h($f['msg']) ?></div>
<?php endforeach; ?>
