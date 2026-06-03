<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('admin');

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if (!$id) { flash_set('error', 'Événement introuvable.'); redirect('evenements.php'); }

$st = $pdo->prepare("SELECT * FROM evenement WHERE id = ?");
$st->execute([$id]);
$e = $st->fetch();
if (!$e) { flash_set('error', 'Événement introuvable.'); redirect('evenements.php'); }

$artisans = $pdo->query("SELECT id, nom_boutique FROM artisan ORDER BY nom_boutique")->fetchAll();
$errors   = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $titre = trim($_POST['titre'] ?? '');
    $date  = trim($_POST['date_debut'] ?? '');
    if (!$titre) $errors[] = "Titre requis.";
    if (!$date)  $errors[] = "Date de début requise.";

    // Upload image
    $image_url = trim($_POST['image_url'] ?? '');
    if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
        $mime    = mime_content_type($_FILES['photo']['tmp_name']);
        if (isset($allowed[$mime]) && $_FILES['photo']['size'] <= 5 * 1024 * 1024) {
            $dir = __DIR__ . '/../uploads/evenements';
            if (!is_dir($dir)) @mkdir($dir, 0755, true);
            $fname = "e{$id}-" . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
            if (move_uploaded_file($_FILES['photo']['tmp_name'], "$dir/$fname")) {
                $image_url = 'uploads/evenements/' . $fname;
            }
        }
    }

    if (!$errors) {
        $pdo->prepare(
            "UPDATE evenement SET artisan_id=?, titre=?, description=?, lieu=?, ville=?,
                                  date_debut=?, date_fin=?, prix_entree=?, capacite_max=?,
                                  image_url=?, est_publie=?
             WHERE id=?"
        )->execute([
            (int)($_POST['artisan_id'] ?? 0),
            $titre,
            trim($_POST['description'] ?? '') ?: null,
            trim($_POST['lieu'] ?? '') ?: null,
            trim($_POST['ville'] ?? '') ?: null,
            $date,
            trim($_POST['date_fin'] ?? '') ?: null,
            (float)($_POST['prix_entree'] ?? 0),
            $_POST['capacite_max'] !== '' ? (int)$_POST['capacite_max'] : null,
            $image_url ?: null,
            isset($_POST['est_publie']) ? 1 : 0,
            $id,
        ]);
        flash_set('success', 'Événement mis à jour.');
        redirect('evenements.php');
    }
    $e = array_merge($e, $_POST);
}

$page_title = 'Modifier événement — Backoffice';
require_once __DIR__ . '/_header.php';
?>
<div class="crumb"><a href="index.php">Backoffice</a> &rsaquo; <a href="evenements.php">Événements</a> &rsaquo; Modifier</div>
<div class="form-card" style="max-width:680px">
  <h1>Modifier l'événement #<?= $id ?></h1>
  <?php foreach ($errors as $err): ?><div class="flash flash-error"><?= h($err) ?></div><?php endforeach; ?>
  <form method="post" action="evenement_edit.php" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= $id ?>">

    <div class="form-row"><label>Titre *</label>
      <input type="text" name="titre" required value="<?= h($e['titre']) ?>"></div>

    <div class="form-row"><label>Artisan organisateur</label>
      <select name="artisan_id">
        <?php foreach ($artisans as $a): ?>
          <option value="<?= (int)$a['id'] ?>" <?= (int)$e['artisan_id']===(int)$a['id']?'selected':'' ?>><?= h($a['nom_boutique']) ?></option>
        <?php endforeach; ?>
      </select></div>

    <div class="form-row"><label>Description</label>
      <textarea name="description"><?= h($e['description'] ?? '') ?></textarea></div>

    <div class="form-row-group">
      <div class="form-row"><label>Lieu</label>
        <input type="text" name="lieu" value="<?= h($e['lieu'] ?? '') ?>"></div>
      <div class="form-row"><label>Ville</label>
        <input type="text" name="ville" value="<?= h($e['ville'] ?? '') ?>"></div>
    </div>

    <div class="form-row-group">
      <div class="form-row"><label>Date de début *</label>
        <input type="datetime-local" name="date_debut" required
               value="<?= h(date('Y-m-d\TH:i', strtotime($e['date_debut']))) ?>"></div>
      <div class="form-row"><label>Date de fin</label>
        <input type="datetime-local" name="date_fin"
               value="<?= $e['date_fin'] ? h(date('Y-m-d\TH:i', strtotime($e['date_fin']))) : '' ?>"></div>
    </div>

    <div class="form-row-group">
      <div class="form-row"><label>Prix d'entrée (€)</label>
        <input type="number" min="0" step="0.01" name="prix_entree" value="<?= h($e['prix_entree']) ?>"></div>
      <div class="form-row"><label>Capacité max</label>
        <input type="number" min="0" name="capacite_max" value="<?= h($e['capacite_max'] ?? '') ?>" placeholder="illimitée"></div>
    </div>

    <div class="form-row"><label>Photo</label>
      <?php if (!empty($e['image_url'])): ?>
        <img src="<?= h(strpos($e['image_url'],'http')===0 ? $e['image_url'] : '../'.$e['image_url']) ?>"
             style="height:80px;border-radius:6px;object-fit:cover;margin-bottom:6px;display:block">
      <?php endif; ?>
      <input type="file" name="photo" accept="image/jpeg,image/png,image/webp">
      <input type="url" name="image_url" placeholder="https://… URL externe"
             value="<?= h($_POST['image_url'] ?? $e['image_url'] ?? '') ?>" style="margin-top:4px">
    </div>

    <div class="form-row">
      <label><input type="checkbox" name="est_publie" value="1" <?= $e['est_publie']?'checked':'' ?> style="width:auto;margin-right:6px">Publié</label>
    </div>

    <div class="form-actions">
      <button class="btn-primary" type="submit">Enregistrer</button>
      <a class="btn-ghost" href="evenements.php">Annuler</a>
    </div>
  </form>
</div>
<?php require_once __DIR__ . '/_footer.php'; ?>
