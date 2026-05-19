<?php

/**
 * Formulaire d'edition du profil. L'email n'est pas modifiable (c'est la cle
 * d'identification). On peut changer nom, prenom, ville, telephone, bio, URL
 * de l'avatar.
 */

require_once __DIR__ . '/includes/bootstrap.php';
// Acces reserve aux utilisateurs connectes.
require_login();
$page_title = 'Éditer mon profil - Artify';

$ok = false; $errors = [];
// Donnees actuelles pour pre-remplir le formulaire.
$u = current_user($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verification CSRF avant tout effet de bord.
    csrf_check();
    // trim systematique pour eviter qu'un nom = "  " passe la regle "non vide".
    $nom       = trim($_POST['nom'] ?? '');
    $prenom    = trim($_POST['prenom'] ?? '');
    $bio       = trim($_POST['bio'] ?? '');
    $ville     = trim($_POST['ville'] ?? '');
    $tel       = trim($_POST['telephone'] ?? '');
    $avatar    = trim($_POST['avatar_url'] ?? '');
    if (!$nom || !$prenom) $errors[] = "Nom et prénom requis.";
    // FILTER_VALIDATE_URL rejette les schemes exotiques type javascript:, securise l'integration en <img src>.
    if ($avatar && !filter_var($avatar, FILTER_VALIDATE_URL)) $errors[] = "URL d'avatar invalide.";

    // Les champs optionnels sont passes a null en BDD (plutot que chaine vide) pour rester coherents avec le schema.
    if (!$errors) {
        // WHERE id = current_user_id : on ne peut modifier que son propre profil, impossibilite de toucher au compte d'un autre.
        $pdo->prepare(
          "UPDATE utilisateur SET nom=?, prenom=?, bio=?, ville=?, telephone=?, avatar_url=? WHERE id=?"
        )->execute([$nom, $prenom, $bio ?: null, $ville ?: null, $tel ?: null, $avatar ?: null, current_user_id()]);
        // On synchronise la session pour que le header affiche immediatement le nouveau nom sans devoir se reconnecter.
        $_SESSION['user_nom'] = $prenom . ' ' . $nom;
        flash_set('success', 'Profil mis à jour.');
        // PRG pour eviter un re-POST au refresh.
        redirect('profile.php');
    }
    // En cas d'erreur de validation on conserve la saisie de l'utilisateur dans $u pour eviter qu'il doive tout retaper.
    $u = array_merge($u, [
      'nom'=>$nom,'prenom'=>$prenom,'bio'=>$bio,'ville'=>$ville,'telephone'=>$tel,'avatar_url'=>$avatar
    ]);
}
include __DIR__ . '/includes/header.php';
?>
<div class="crumb"><a href="index.php">Accueil</a> &rsaquo; <a href="profile.php">Profil</a> &rsaquo; Modifier</div>
<div class="form-card">
  <h1>Éditer mon profil</h1>
  <?php foreach ($errors as $e): ?>
    <div class="flash flash-error"><?= h($e) ?></div>
  <?php endforeach; ?>
  <?php // Pas de champ email ici : l'identifiant de compte n'est pas modifiable depuis le front. ?>
  <form method="post" action="profile_edit.php">
    <?= csrf_field() ?>
    <div class="form-row"><label>Prénom</label>
      <input type="text" name="prenom" required value="<?= h($u['prenom']) ?>"></div>
    <div class="form-row"><label>Nom</label>
      <input type="text" name="nom" required value="<?= h($u['nom']) ?>"></div>
    <div class="form-row"><label>Ville</label>
      <input type="text" name="ville" value="<?= h($u['ville'] ?? '') ?>"></div>
    <div class="form-row"><label>Téléphone</label>
      <input type="text" name="telephone" value="<?= h($u['telephone'] ?? '') ?>"></div>
    <?php // input type=url declenche la validation native du navigateur (en plus de la validation serveur). ?>
    <div class="form-row"><label>Avatar (URL)</label>
      <input type="url" name="avatar_url" placeholder="https://…" value="<?= h($u['avatar_url'] ?? '') ?>"></div>
    <div class="form-row"><label>Bio</label>
      <textarea name="bio"><?= h($u['bio'] ?? '') ?></textarea></div>
    <div class="form-actions">
      <button class="btn-primary" type="submit">Enregistrer</button>
      <a class="btn-ghost" href="profile.php">Annuler</a>
    </div>
  </form>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
