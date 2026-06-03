<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();
$page_title = 'Éditer mon profil — Artify';

$ok = false; $errors = [];
$u = current_user($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $nom    = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $bio    = trim($_POST['bio'] ?? '');
    $ville  = trim($_POST['ville'] ?? '');
    $tel    = trim($_POST['telephone'] ?? '');
    $avatar = trim($_POST['avatar_url'] ?? '');

    if (!$nom || !$prenom) $errors[] = "Nom et prénom requis.";
    if ($avatar && !filter_var($avatar, FILTER_VALIDATE_URL)) $errors[] = "URL d'avatar invalide.";

    // Upload fichier avatar (prioritaire sur l'URL)
    if (!$errors && !empty($_FILES['avatar_file']['name']) && $_FILES['avatar_file']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
        $mime = mime_content_type($_FILES['avatar_file']['tmp_name']);
        if (!isset($allowed[$mime])) {
            $errors[] = "Format non supporté (JPG, PNG, WebP ou GIF uniquement).";
        } elseif ($_FILES['avatar_file']['size'] > 2 * 1024 * 1024) {
            $errors[] = "L'image ne doit pas dépasser 2 Mo.";
        } else {
            $ext = $allowed[$mime];
            $dir = __DIR__ . '/uploads/avatars';
            if (!is_dir($dir)) @mkdir($dir, 0755, true);
            $fname = 'u' . current_user_id() . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
            if (move_uploaded_file($_FILES['avatar_file']['tmp_name'], "$dir/$fname")) {
                $avatar = 'uploads/avatars/' . $fname;
            } else {
                $errors[] = "Impossible de sauvegarder le fichier.";
            }
        }
    }

    if (!$errors) {
        $pdo->prepare(
            "UPDATE utilisateur SET nom=?, prenom=?, bio=?, ville=?, telephone=?, avatar_url=? WHERE id=?"
        )->execute([$nom, $prenom, $bio ?: null, $ville ?: null, $tel ?: null, $avatar ?: null, current_user_id()]);
        $_SESSION['user_nom'] = $prenom . ' ' . $nom;
        flash_set('success', 'Profil mis à jour.');
        redirect('profile.php');
    }

    $u = array_merge($u, [
        'nom' => $nom, 'prenom' => $prenom, 'bio' => $bio,
        'ville' => $ville, 'telephone' => $tel, 'avatar_url' => $avatar,
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
  <form method="post" action="profile_edit.php" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="form-row"><label>Prénom</label>
      <input type="text" name="prenom" required value="<?= h($u['prenom']) ?>"></div>
    <div class="form-row"><label>Nom</label>
      <input type="text" name="nom" required value="<?= h($u['nom']) ?>"></div>
    <div class="form-row"><label>Ville</label>
      <input type="text" name="ville" value="<?= h($u['ville'] ?? '') ?>"></div>
    <div class="form-row"><label>Téléphone</label>
      <input type="text" name="telephone" value="<?= h($u['telephone'] ?? '') ?>"></div>

    <div class="form-row">
      <label>Photo de profil</label>
      <?php if (!empty($u['avatar_url'])): ?>
        <img src="<?= h($u['avatar_url']) ?>" alt="Avatar actuel"
             style="width:64px;height:64px;border-radius:50%;object-fit:cover;display:block;margin-bottom:8px;border:2px solid var(--border)">
      <?php endif; ?>
      <input type="file" name="avatar_file" accept="image/jpeg,image/png,image/webp,image/gif"
             style="margin-bottom:6px">
      <span style="font-size:11px;color:var(--muted)">JPG, PNG, WebP ou GIF — max 2 Mo</span>
    </div>

    <div class="form-row"><label>… ou URL d'une image externe</label>
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
