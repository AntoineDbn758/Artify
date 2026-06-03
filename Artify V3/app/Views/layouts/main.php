<?php
/**
 * Layout principal des vues MVC.
 * Reprend la nav du site existant pour rester cohérent visuellement.
 * Variables disponibles : $title (string), $content (HTML déjà rendu).
 */
$pageTitle = $title ?? 'Artify';
$base      = '';
$_SESSION = $_SESSION ?? [];
function _logged(): bool { return !empty($_SESSION['user_id']); }
function _role(): string { return $_SESSION['user_role'] ?? 'visiteur'; }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500&family=OpenDyslexic&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/a11y.css">
  <script defer src="js/main.js"></script>
  <script defer src="js/a11y.js"></script>
  <script>
    // Applique les préférences d'accessibilité dès le head pour éviter le flash.
    (function(){
      var p = localStorage.getItem('a11y') || '';
      if (p) document.documentElement.setAttribute('data-a11y', p);
      var s = localStorage.getItem('a11y-size') || '';
      if (s) document.documentElement.style.fontSize = s;
    })();
  </script>
</head>
<body>
<div class="accent-strip"></div>
<nav>
  <a class="logo" href="index.php"><span class="logo-a">A</span><span class="logo-text">Artify</span></a>
  <ul class="nav-links">
    <li><a href="index.php">Accueil</a></li>
    <li><a href="creations.php">Créations</a></li>
    <li><a href="artisans.php">Artisans</a></li>
    <li><a href="evenements.php">Événements</a></li>
    <li><a href="forum.php">Forum</a></li>
    <li><a href="faq.php">FAQ</a></li>
    <li><a href="contact.php">Contact</a></li>
  </ul>
  <form class="nav-search" action="recherche.php" method="get">
    <input type="text" name="q" placeholder="Rechercher…" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" aria-label="Recherche">
    <button type="submit" class="btn-ghost">OK</button>
  </form>
  <div class="nav-actions">
    <button id="a11y-btn" type="button" class="btn-ghost" aria-label="Options d'accessibilité" title="Accessibilité">A+</button>
    <?php if (_logged()): ?>
      <a class="btn-ghost" href="messages.php">Messages</a>
      <?php if (_role() === 'admin'): ?>
        <a class="btn-ghost" href="backoffice/index.php">Admin</a>
      <?php elseif (_role() === 'artisan'): ?>
        <a class="btn-ghost" href="boutique.php">Ma boutique</a>
      <?php endif; ?>
      <a class="btn-ghost" href="profile.php">Profil</a>
      <a class="btn-primary" href="logout.php">Déconnexion</a>
    <?php else: ?>
      <a class="btn-ghost" href="login_form.php">Se connecter</a>
      <a class="btn-primary" href="register_form.php">Créer un compte</a>
    <?php endif; ?>
  </div>
</nav>
<div class="layout-with-sidebar">
<aside class="sidebar" aria-label="Navigation secondaire">
  <h3>Explorer</h3>
  <ul>
    <li><a href="creations.php">Toutes les créations</a></li>
    <li><a href="artisans.php">Tous les artisans</a></li>
    <li><a href="evenements.php">Événements</a></li>
    <li><a href="forum.php">Forum communauté</a></li>
  </ul>
  <h3>Mon compte</h3>
  <ul>
    <?php if (_logged()): ?>
      <li><a href="profile.php">Mon profil</a></li>
      <li><a href="messages.php">Messagerie</a></li>
      <li><a href="mes_commandes.php">Mes commandes</a></li>
    <?php else: ?>
      <li><a href="login_form.php">Connexion</a></li>
      <li><a href="register_form.php">Inscription</a></li>
      <li><a href="forgot.php">Mot de passe oublié</a></li>
    <?php endif; ?>
  </ul>
  <h3>Aide</h3>
  <ul>
    <li><a href="faq.php">FAQ</a></li>
    <li><a href="contact.php">Nous contacter</a></li>
  </ul>
</aside>
<main class="page-main">
  <?php foreach ($_SESSION['flash'] ?? [] as $f): ?>
    <div class="flash flash-<?= htmlspecialchars($f['type']) ?>"><?= htmlspecialchars($f['msg']) ?></div>
  <?php endforeach; unset($_SESSION['flash']); ?>
  <?= $content ?>
</main>
</div>
<footer style="text-align:center;padding:24px;color:var(--muted);font-size:13px">
  <p>&copy; <?= date('Y') ?> Artify · <a href="mentions.php">Mentions légales</a> · <a href="cgu.php">CGU</a></p>
</footer>
</body>
</html>
