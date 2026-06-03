<?php /** @var ?string $demoLink */ ?>
<div class="form-card" style="max-width:520px;margin:0 auto">
  <h1>Vérifiez votre boîte mail</h1>
  <p>Si un compte existe avec cette adresse, vous recevrez sous peu un email avec un lien de réinitialisation.</p>
  <?php if ($demoLink): ?>
    <div class="flash flash-info" style="margin-top:14px">
      <strong>Mode démo</strong> — en production le lien serait envoyé par mail.<br>
      <a href="<?= htmlspecialchars($demoLink) ?>">Cliquer ici pour réinitialiser</a>
    </div>
  <?php endif; ?>
  <p style="margin-top:14px"><a class="btn-ghost" href="login_form.php">Retour à la connexion</a></p>
</div>
