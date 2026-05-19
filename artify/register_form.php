<?php

/**
 * Formulaire d'inscription (le visuel). Le POST est traite par
 * inscription.php. Propose deux types de comptes : visiteur (par defaut) ou
 * artisan (cree aussi automatiquement une boutique liee).
 */

require_once __DIR__ . '/includes/bootstrap.php';
$page_title = 'Inscription - Artify';
// Code d'erreur en GET utilise comme un enum pour afficher le bon message, plus simple qu'une session flash pour ce cas precis.
$err = $_GET['err'] ?? '';
include __DIR__ . '/includes/header.php';
?>
<div class="form-card">
  <h1>Créer un compte</h1>
  <p style="margin-bottom:14px">Rejoignez la communauté Artify, visiteur ou artisan.</p>

  <?php if ($err === '1'): ?>
    <div class="flash flash-error">Cet email est déjà utilisé.</div>
  <?php elseif ($err === '2'): ?>
    <div class="flash flash-error">Les deux mots de passe ne correspondent pas.</div>
  <?php elseif ($err === '3'): ?>
    <div class="flash flash-error">Tous les champs obligatoires doivent être remplis.</div>
  <?php endif; ?>

  <form method="post" action="inscription.php" autocomplete="on">
    <?= csrf_field() ?>
    <div class="form-row">
      <label>Nom</label>
      <input type="text" name="nom" required>
    </div>
    <div class="form-row">
      <label>Prénom</label>
      <input type="text" name="prenom" required>
    </div>
    <div class="form-row">
      <label>Email</label>
      <input type="email" name="email" required>
    </div>
    <div class="form-row">
      <label>Mot de passe</label>
      <input type="password" name="password" required minlength="6">
    </div>
    <div class="form-row">
      <label>Confirmer le mot de passe</label>
      <input type="password" name="password_confirm" required minlength="6">
    </div>
    <?php // Selecteur visiteur/artisan : choisir artisan declenche aussi la creation d'un enregistrement dans la table artisan cote inscription.php. ?>
    <div class="form-row">
      <label>Type de compte</label>
      <select name="role">
        <option value="visiteur">Visiteur (acheteur)</option>
        <option value="artisan">Artisan (vendeur)</option>
      </select>
    </div>
    <div class="form-row">
      <label>Nom de boutique (si artisan)</label>
      <input type="text" name="nom_boutique" placeholder="Ex. Atelier de Marie">
    </div>
    <div class="form-row" style="font-size:13px;color:var(--mid)">
      <label><input type="checkbox" name="cgu" required style="width:auto;margin-right:6px">
      J'accepte les <a href="cgu.php">CGU</a> et les <a href="mentions.php">mentions légales</a>.</label>
    </div>
    <div class="form-actions">
      <button class="btn-primary" type="submit">Créer le compte</button>
      <a href="login_form.php" style="margin-left:auto">Déjà inscrit ?</a>
    </div>
  </form>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
