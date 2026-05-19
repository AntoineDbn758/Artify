<?php

/**
 * Formulaire de connexion (visuel). Le POST est traite par login.php. Accepte
 * un parametre next= pour rediriger l'utilisateur vers la page qu'il voulait
 * initialement consulter apres la connexion.
 */

require_once __DIR__ . '/includes/bootstrap.php';
$page_title = 'Connexion - Artify';
// next= permet de revenir a la page d'origine (ex: fiche produit) une fois logge, valeur reinjectee dans le formulaire en hidden.
$err = $_GET['err'] ?? '';
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

  <form method="post" action="login.php" autocomplete="on">
    <?= csrf_field() ?>
    <input type="hidden" name="next" value="<?= h($next) ?>">
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
