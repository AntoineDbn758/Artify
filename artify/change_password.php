<?php

/**
 * Changement du mot de passe. Demande l'ancien mot de passe pour verification
 * avant d'accepter le nouveau. Le hash est regenere avec password_hash().
 */

require_once __DIR__ . '/includes/bootstrap.php';
require_login();
$page_title = 'Changer mon mot de passe - Artify';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $old = $_POST['old_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (strlen($new) < 6) $errors[] = "Le nouveau mot de passe doit faire au moins 6 caractères.";
    if ($new !== $confirm) $errors[] = "La confirmation ne correspond pas.";

    // On exige l'ancien mot de passe meme si l'utilisateur est deja logge, pour empecher qu'une session volee permette de prendre le compte.
    if (!$errors) {
        $st = $pdo->prepare("SELECT mot_de_passe FROM utilisateur WHERE id = ?");
        $st->execute([current_user_id()]);
        $hash = $st->fetchColumn();
        if (!$hash || !password_verify($old, $hash)) {
            $errors[] = "Ancien mot de passe incorrect.";
        }
    }
    // Bcrypt avec son cost par defaut, suffisant et coherent avec ce qui est utilise a l'inscription.
    if (!$errors) {
        $newHash = password_hash($new, PASSWORD_BCRYPT);
        $pdo->prepare("UPDATE utilisateur SET mot_de_passe = ? WHERE id = ?")
            ->execute([$newHash, current_user_id()]);
        flash_set('success', 'Mot de passe modifié avec succès.');
        redirect('profile.php');
    }
}
include __DIR__ . '/includes/header.php';
?>
<div class="crumb"><a href="index.php">Accueil</a> &rsaquo; <a href="profile.php">Profil</a> &rsaquo; Mot de passe</div>
<div class="form-card">
  <h1>Changer mon mot de passe</h1>
  <?php foreach ($errors as $e): ?>
    <div class="flash flash-error"><?= h($e) ?></div>
  <?php endforeach; ?>
  <form method="post" action="change_password.php" autocomplete="off">
    <?= csrf_field() ?>
    <div class="form-row"><label>Ancien mot de passe</label>
      <input type="password" name="old_password" required></div>
    <div class="form-row"><label>Nouveau mot de passe</label>
      <input type="password" name="new_password" required minlength="6"></div>
    <div class="form-row"><label>Confirmer</label>
      <input type="password" name="confirm_password" required minlength="6"></div>
    <div class="form-actions">
      <button class="btn-primary" type="submit">Modifier</button>
      <a class="btn-ghost" href="profile.php">Annuler</a>
    </div>
  </form>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
