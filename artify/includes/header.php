<?php
// includes/header.php — en-tête commun (nav + sidebar + ouverture <main>).
require_once __DIR__ . '/bootstrap.php';
$page_title = $page_title ?? 'Artify — Plateforme des Créateurs';
$base = $base ?? '';
$bodyClass = $bodyClass ?? '';

// Compteur messages non lus pour le badge dans la nav
$unread_msg = 0;
if (is_logged()) {
    try {
        $st = $pdo->prepare("SELECT COUNT(*) FROM messagerie WHERE destinataire_id = ? AND lu = 0");
        $st->execute([current_user_id()]);
        $unread_msg = (int)$st->fetchColumn();
    } catch (\Throwable $e) { /* table absente : ignorer */ }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($page_title) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,400&family=OpenDyslexic&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= h($base) ?>css/style.css">
<link rel="stylesheet" href="<?= h($base) ?>css/a11y.css">
<script defer src="<?= h($base) ?>js/main.js"></script>
<script defer src="<?= h($base) ?>js/a11y.js"></script>
<script>
  // Préférences a11y appliquées avant le rendu pour éviter le flash visuel.
  (function(){
    var p = localStorage.getItem('a11y') || '';
    if (p) document.documentElement.setAttribute('data-a11y', p);
    var s = localStorage.getItem('a11y-size') || '';
    if (s) document.documentElement.style.fontSize = s;
  })();
</script>
</head>
<body class="<?= h($bodyClass) ?>">
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
    <li><a href="<?= h($base) ?>forum.php">Forum</a></li>
    <li><a href="<?= h($base) ?>faq.php">FAQ</a></li>
    <li><a href="<?= h($base) ?>contact.php">Contact</a></li>
  </ul>
  <form class="nav-search" action="<?= h($base) ?>recherche.php" method="get">
    <input type="text" name="q" placeholder="Rechercher…" value="<?= h($_GET['q'] ?? '') ?>" aria-label="Recherche">
    <button type="submit" class="btn-ghost">OK</button>
  </form>
  <div class="nav-actions">
    <button id="a11y-btn" type="button" class="btn-ghost" aria-label="Options d'accessibilité" title="Accessibilité">A+</button>
  <?php if (is_logged()): ?>
    <a class="btn-ghost" href="<?= h($base) ?>messages.php" aria-label="Messagerie">
      ✉ <?php if ($unread_msg > 0): ?><span class="badge err"><?= $unread_msg ?></span><?php endif; ?>
    </a>
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
<a href="#main" class="skip-link">Aller au contenu principal</a>
<div class="layout-with-sidebar">
<aside class="sidebar" aria-label="Navigation secondaire">
  <h3>Explorer</h3>
  <ul>
    <li><a href="<?= h($base) ?>creations.php">Toutes les créations</a></li>
    <li><a href="<?= h($base) ?>artisans.php">Tous les artisans</a></li>
    <li><a href="<?= h($base) ?>evenements.php">Événements</a></li>
    <li><a href="<?= h($base) ?>forum.php">Forum communauté</a></li>
  </ul>
  <h3>Mon compte</h3>
  <ul>
  <?php if (is_logged()): ?>
    <li><a href="<?= h($base) ?>profile.php">Mon profil</a></li>
    <li><a href="<?= h($base) ?>messages.php">Messagerie</a></li>
    <li><a href="<?= h($base) ?>mes_commandes.php">Mes commandes</a></li>
    <?php if (is_artisan()): ?>
      <li><a href="<?= h($base) ?>boutique.php">Ma boutique</a></li>
    <?php endif; ?>
  <?php else: ?>
    <li><a href="<?= h($base) ?>login_form.php">Connexion</a></li>
    <li><a href="<?= h($base) ?>register_form.php">Inscription</a></li>
    <li><a href="<?= h($base) ?>forgot.php">Mot de passe oublié</a></li>
  <?php endif; ?>
  </ul>
  <h3>Aide</h3>
  <ul>
    <li><a href="<?= h($base) ?>faq.php">FAQ</a></li>
    <li><a href="<?= h($base) ?>contact.php">Nous contacter</a></li>
    <li><a href="<?= h($base) ?>cgu.php">CGU</a></li>
  </ul>
</aside>
<main id="main" class="page-main">
<?php foreach (flash_pop() as $f): ?>
  <div class="flash flash-<?= h($f['type']) ?>"><?= h($f['msg']) ?></div>
<?php endforeach; ?>
