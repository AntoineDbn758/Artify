<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('admin');

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if (!$id) { flash_set('error', 'Artisan introuvable.'); redirect('artisans.php'); }

$st = $pdo->prepare(
    "SELECT a.*, u.nom, u.prenom, u.email, u.ville, u.telephone, u.bio, u.avatar_url
       FROM artisan a JOIN utilisateur u ON u.id = a.utilisateur_id
      WHERE a.id = ?"
);
$st->execute([$id]);
$a = $st->fetch();
if (!$a) { flash_set('error', 'Artisan introuvable.'); redirect('artisans.php'); }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    // Upload avatar
    $avatar = trim($_POST['avatar_url'] ?? '');
    if (!empty($_FILES['avatar_file']['name']) && $_FILES['avatar_file']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif'];
        $mime    = mime_content_type($_FILES['avatar_file']['tmp_name']);
        if (isset($allowed[$mime]) && $_FILES['avatar_file']['size'] <= 2 * 1024 * 1024) {
            $dir = __DIR__ . '/../uploads/avatars';
            if (!is_dir($dir)) @mkdir($dir, 0755, true);
            $fname = 'u' . $a['utilisateur_id'] . '-' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
            if (move_uploaded_file($_FILES['avatar_file']['tmp_name'], "$dir/$fname")) {
                $avatar = 'uploads/avatars/' . $fname;
            }
        }
    }

    // Update utilisateur
    $pdo->prepare(
        "UPDATE utilisateur SET nom=?, prenom=?, email=?, ville=?, telephone=?, bio=?, avatar_url=? WHERE id=?"
    )->execute([
        trim($_POST['nom'] ?? ''),
        trim($_POST['prenom'] ?? ''),
        trim($_POST['email'] ?? ''),
        trim($_POST['ville'] ?? '') ?: null,
        trim($_POST['telephone'] ?? '') ?: null,
        trim($_POST['bio'] ?? '') ?: null,
        $avatar ?: null,
        (int)$a['utilisateur_id'],
    ]);

    // Update artisan
    $pdo->prepare(
        "UPDATE artisan SET nom_boutique=?, specialite=?, description=?, site_web=?, instagram=?, verifie=? WHERE id=?"
    )->execute([
        trim($_POST['nom_boutique'] ?? ''),
        trim($_POST['specialite'] ?? '') ?: null,
        trim($_POST['description'] ?? '') ?: null,
        trim($_POST['site_web'] ?? '') ?: null,
        trim($_POST['instagram'] ?? '') ?: null,
        isset($_POST['verifie']) ? 1 : 0,
        $id,
    ]);

    flash_set('success', 'Fiche artisan mise à jour.');
    redirect('artisans.php');
}

$page_title = 'Modifier artisan — Backoffice';
require_once __DIR__ . '/_header.php';
?>
<div class="crumb"><a href="index.php">Backoffice</a> &rsaquo; <a href="artisans.php">Artisans</a> &rsaquo; Modifier</div>
<div class="form-card" style="max-width:680px">
  <h1>Modifier — <?= h($a['nom_boutique']) ?></h1>

  <form method="post" action="artisan_edit.php" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= $id ?>">

    <h2 style="font-size:15px;margin:10px 0 6px">Compte utilisateur</h2>
    <div class="form-row-group">
      <div class="form-row"><label>Prénom</label>
        <input type="text" name="prenom" value="<?= h($a['prenom']) ?>"></div>
      <div class="form-row"><label>Nom</label>
        <input type="text" name="nom" value="<?= h($a['nom']) ?>"></div>
    </div>
    <div class="form-row-group">
      <div class="form-row"><label>Email</label>
        <input type="email" name="email" value="<?= h($a['email']) ?>"></div>
      <div class="form-row"><label>Ville</label>
        <input type="text" name="ville" value="<?= h($a['ville'] ?? '') ?>"></div>
    </div>
    <div class="form-row"><label>Téléphone</label>
      <input type="text" name="telephone" value="<?= h($a['telephone'] ?? '') ?>"></div>
    <div class="form-row"><label>Bio</label>
      <textarea name="bio"><?= h($a['bio'] ?? '') ?></textarea></div>

    <div class="form-row"><label>Photo de profil</label>
      <?php if (!empty($a['avatar_url'])): ?>
        <img src="<?= h(strpos($a['avatar_url'],'http')===0 ? $a['avatar_url'] : '../'.$a['avatar_url']) ?>"
             style="width:64px;height:64px;border-radius:50%;object-fit:cover;display:block;margin-bottom:6px;border:2px solid var(--border)">
      <?php endif; ?>
      <input type="file" name="avatar_file" accept="image/jpeg,image/png,image/webp,image/gif">
      <input type="url" name="avatar_url" placeholder="https://… URL externe" value="<?= h($a['avatar_url'] ?? '') ?>" style="margin-top:4px">
    </div>

    <h2 style="font-size:15px;margin:14px 0 6px">Fiche boutique</h2>
    <div class="form-row-group">
      <div class="form-row"><label>Nom de boutique</label>
        <input type="text" name="nom_boutique" value="<?= h($a['nom_boutique']) ?>"></div>
      <div class="form-row"><label>Spécialité</label>
        <input type="text" name="specialite" value="<?= h($a['specialite'] ?? '') ?>"></div>
    </div>
    <div class="form-row"><label>Description</label>
      <textarea name="description"><?= h($a['description'] ?? '') ?></textarea></div>
    <div class="form-row-group">
      <div class="form-row"><label>Site web</label>
        <input type="url" name="site_web" value="<?= h($a['site_web'] ?? '') ?>"></div>
      <div class="form-row"><label>Instagram</label>
        <input type="text" name="instagram" value="<?= h($a['instagram'] ?? '') ?>"></div>
    </div>
    <div class="form-row">
      <label><input type="checkbox" name="verifie" value="1" <?= $a['verifie']?'checked':'' ?> style="width:auto;margin-right:6px">Boutique vérifiée</label>
    </div>

    <div class="form-actions">
      <button class="btn-primary" type="submit">Enregistrer</button>
      <a class="btn-ghost" href="artisans.php">Annuler</a>
    </div>
  </form>
</div>
<?php require_once __DIR__ . '/_footer.php'; ?>
