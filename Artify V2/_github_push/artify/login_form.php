<?php

/**
 * Formulaire de connexion (visuel). Le POST est traite par login.php. Accepte
 * un parametre next= pour rediriger l'utilisateur vers la page qu'il voulait
 * initialement consulter apres la connexion.
 */

require_once __DIR__ . '/includes/bootstrap.php';
$page_title = 'Connexion - Artify';
// next= permet de revenir a la page d'origine (ex: fiche produit) une fois logge, valeur reinjectee dans le formulaire en hidden.
// Code d'erreur GET pour repondre apres redirect de login.php (1 = bad creds, disabled = compte suspendu).
$err = $_GET['err'] ?? '';
// Si pas de next fourni, on retourne a l'accueil par defaut.
$next = $_GET['next'] ?? 'index.php';
include __DIR__ . '/includes/header.php';
?>
<div class="form-card">
  <h1>Se connecter</h1>
  <p style="margin-bottom:14px">Accédez à votre espace Artify.</p>

  <?php // Message generique en cas d'echec : on ne dit pas si c'est l'email ou le password qui est faux, pour pas faciliter l'enumeration de comptes. ?>
  <?php if ($err === '1'): ?>
    <div class="flash flash-error">Email ou mot de passe incorrect.</div>
  <?php elseif ($err === 'disabled'): ?>
    <div class="flash flash-error">Ce compte a été désactivé.</div>
  <?php endif; ?>

  <?php // POST traite par login.php : verification du hash + creation de session. ?>
  <form method="post" action="login.php" autocomplete="on">
    <?= csrf_field() ?>
    <?php // next reinjecte en hidden pour conserver la cible apres login. ?>
    <input type="hidden" name="next" value="<?= h($next) ?>">
    <?php // autofocus pour saisie immediate du visiteur, type=email pour validation native. ?>
    <div class="form-row">
      <label>Email</label>
      <input type="email" name="email" required autofocus>
    </div>
    <div class="form-row">
      <label>Mot de passe</label>
      <input type="password" name="password" required>
    </div>
    <div class="form-actions">
      <button class="btn-primary" type="submit">Se connecter</button>
      <a href="register_form.php" style="margin-left:auto">Créer un compte &rarr;</a>
    </div>
  </form>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
