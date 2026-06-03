<?php
require_once __DIR__ . '/includes/bootstrap.php';
$page_title = 'Inscription — Artify';
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
  <?php elseif ($err === 'weak'): ?>
    <div class="flash flash-error">
      Mot de passe trop faible :
      <ul style="margin:6px 0 0 18px">
        <?php foreach (($_SESSION['pwd_errors'] ?? []) as $e): ?>
          <li><?= h($e) ?></li>
        <?php endforeach; unset($_SESSION['pwd_errors']); ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="post" action="inscription.php" autocomplete="on" data-pwd-strength>
    <?= csrf_field() ?>
    <div class="form-row-group">
      <div class="form-row">
        <label>Nom</label>
        <input type="text" name="nom" required>
      </div>
      <div class="form-row">
        <label>Prénom</label>
        <input type="text" name="prenom" required>
      </div>
    </div>
    <div class="form-row">
      <label>Email</label>
      <input type="email" name="email" required>
    </div>
    <div class="form-row">
      <label>Mot de passe</label>
      <input type="password" name="password" id="pwd" required minlength="8" autocomplete="new-password">
      <div class="pwd-strength" aria-live="polite">
        <div class="pwd-bar"><span></span></div>
        <ul class="pwd-rules">
          <li data-rule="len">8 caractères</li>
          <li data-rule="upper">Majuscule</li>
          <li data-rule="lower">Minuscule</li>
          <li data-rule="digit">Chiffre</li>
          <li data-rule="special">Spécial</li>
        </ul>
      </div>
    </div>
    <div class="form-row">
      <label>Confirmer le mot de passe</label>
      <input type="password" name="password_confirm" required minlength="8" autocomplete="new-password">
    </div>
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
