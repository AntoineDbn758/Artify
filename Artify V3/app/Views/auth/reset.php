<?php /** @var string $token */ /** @var array $errors */
$errors = $errors ?? []; ?>
<div class="form-card" style="max-width:520px;margin:0 auto">
  <h1>Nouveau mot de passe</h1>
  <?php foreach ($errors as $e): ?>
    <div class="flash flash-error"><?= htmlspecialchars($e) ?></div>
  <?php endforeach; ?>
  <form method="post" action="reset.php" data-pwd-strength>
    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
    <div class="form-row"><label>Nouveau mot de passe</label>
      <input type="password" name="password" id="pwd" required minlength="8" autocomplete="new-password">
      <div class="pwd-strength" aria-live="polite">
        <div class="pwd-bar"><span></span></div>
        <ul class="pwd-rules">
          <li data-rule="len">8 caractères minimum</li>
          <li data-rule="upper">Une majuscule</li>
          <li data-rule="lower">Une minuscule</li>
          <li data-rule="digit">Un chiffre</li>
          <li data-rule="special">Un caractère spécial</li>
        </ul>
      </div>
    </div>
    <div class="form-row"><label>Confirmation</label>
      <input type="password" name="password_confirm" required minlength="8" autocomplete="new-password"></div>
    <div class="form-actions">
      <button class="btn-primary" type="submit">Mettre à jour</button>
    </div>
  </form>
</div>
