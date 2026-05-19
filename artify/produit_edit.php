<?php

/**
 * Edition d'un produit existant. Verifie au prealable que le produit
 * appartient bien a l'artisan connecte (anti-IDOR). Permet aussi de remplacer
 * la photo principale.
 */

require_once __DIR__ . '/includes/bootstrap.php';
require_role('artisan');
$page_title = 'Modifier un produit - Artify';
$artisan = current_artisan($pdo);
if (!$artisan) { flash_set('error', 'Aucune boutique.'); redirect('profile.php'); }

// Check d'ownership : on filtre par artisan_id de la session pour qu'un artisan ne puisse pas editer le produit d'un autre via l'URL (anti-IDOR).
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$st = $pdo->prepare("SELECT * FROM produit WHERE id = ? AND artisan_id = ?");
$st->execute([$id, (int)$artisan['id']]);
$p = $st->fetch();
if (!$p) { http_response_code(404); die('Produit introuvable ou non autorisé.'); }

$cats = $pdo->query("SELECT id, nom FROM categorie ORDER BY nom")->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $nom  = trim($_POST['nom'] ?? '');
    $prix = $_POST['prix'] ?? '';
    $cat  = (int)($_POST['categorie_id'] ?? 0);
    if (!$nom) $errors[] = "Nom requis.";
    if (!is_numeric($prix) || (float)$prix <= 0) $errors[] = "Prix invalide.";
    if (!$cat) $errors[] = "Catégorie requise.";

    // Deuxieme garde-fou : on repete la condition artisan_id dans le WHERE de l'UPDATE pour bloquer toute manipulation de l'id en POST.
    if (!$errors) {
        $pdo->prepare(
          "UPDATE produit SET categorie_id=?, nom=?, description=?, prix=?, materiaux=?, dimensions=?,
                              delai_fabrication_jours=?, stock=?, est_publie=?
            WHERE id=? AND artisan_id=?"
        )->execute([
            $cat, $nom,
            trim($_POST['description'] ?? '') ?: null,
            (float)$prix,
            trim($_POST['materiaux'] ?? '') ?: null,
            trim($_POST['dimensions'] ?? '') ?: null,
            $_POST['delai_fabrication_jours'] !== '' ? (int)$_POST['delai_fabrication_jours'] : null,
            (int)($_POST['stock'] ?? 0),
            isset($_POST['est_publie']) ? 1 : 0,
            $id, (int)$artisan['id']
        ]);

        // Photo principale : URL externe OU upload local
        $image_url = trim($_POST['image_url'] ?? '');
        $uploaded  = '';
        if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
            $mime = mime_content_type($_FILES['photo']['tmp_name']);
            if (isset($allowed[$mime]) && $_FILES['photo']['size'] <= 5 * 1024 * 1024) {
                $ext = $allowed[$mime];
                $dir = __DIR__ . '/uploads/produits';
                if (!is_dir($dir)) @mkdir($dir, 0755, true);
                $fname = "p{$id}-" . bin2hex(random_bytes(4)) . ".$ext";
                if (move_uploaded_file($_FILES['photo']['tmp_name'], "$dir/$fname")) {
                    $uploaded = "uploads/produits/$fname";
                }
            }
        }
        // On bascule d'abord les images existantes en non-principales, puis on ajoute la nouvelle : evite d'avoir deux principales en base.
        $final_url = $uploaded ?: $image_url;
        if ($final_url) {
            $pdo->prepare("UPDATE image_produit SET est_principale=0 WHERE produit_id=?")->execute([$id]);
            $pdo->prepare("INSERT INTO image_produit (produit_id, url, ordre, est_principale) VALUES (?,?,0,1)")
                ->execute([$id, $final_url]);
        }

        flash_set('success', 'Produit mis à jour.');
        redirect('boutique.php');
    }
    $p = array_merge($p, $_POST); // pour réafficher
}
include __DIR__ . '/includes/header.php';
?>
<div class="crumb"><a href="boutique.php">Ma boutique</a> &rsaquo; Modifier « <?= h($p['nom']) ?> »</div>
<div class="form-card">
  <h1>Modifier le produit</h1>
  <?php foreach ($errors as $e): ?><div class="flash flash-error"><?= h($e) ?></div><?php endforeach; ?>
  <form method="post" action="produit_edit.php" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int)$id ?>">
    <div class="form-row"><label>Nom *</label><input type="text" name="nom" required value="<?= h($p['nom']) ?>"></div>
    <div class="form-row"><label>Nouvelle photo (URL externe)</label>
      <input type="url" name="image_url" placeholder="https://images.unsplash.com/..."></div>
    <div class="form-row"><label>… ou fichier (JPG/PNG/WebP, max 5 Mo)</label>
      <input type="file" name="photo" accept="image/jpeg,image/png,image/webp"></div>
    <div class="form-row"><label>Catégorie *</label>
      <select name="categorie_id" required>
        <?php foreach ($cats as $c): ?>
          <option value="<?= (int)$c['id'] ?>" <?= (int)$p['categorie_id']===(int)$c['id']?'selected':'' ?>><?= h($c['nom']) ?></option>
        <?php endforeach; ?>
      </select></div>
    <div class="form-row"><label>Prix (€) *</label><input type="number" min="0" step="0.01" name="prix" required value="<?= h($p['prix']) ?>"></div>
    <div class="form-row"><label>Description</label><textarea name="description"><?= h($p['description'] ?? '') ?></textarea></div>
    <div class="form-row"><label>Matériaux</label><input type="text" name="materiaux" value="<?= h($p['materiaux'] ?? '') ?>"></div>
    <div class="form-row"><label>Dimensions</label><input type="text" name="dimensions" value="<?= h($p['dimensions'] ?? '') ?>"></div>
    <div class="form-row"><label>Délai fabrication (jours)</label><input type="number" min="0" name="delai_fabrication_jours" value="<?= h($p['delai_fabrication_jours'] ?? '') ?>"></div>
    <div class="form-row"><label>Stock</label><input type="number" min="0" name="stock" value="<?= h($p['stock']) ?>"></div>
    <div class="form-row"><label><input type="checkbox" name="est_publie" value="1" <?= $p['est_publie']?'checked':'' ?> style="width:auto;margin-right:6px"> Publié</label></div>
    <div class="form-actions">
      <button class="btn-primary" type="submit">Enregistrer</button>
      <a class="btn-ghost" href="boutique.php">Annuler</a>
    </div>
  </form>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
