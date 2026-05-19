<?php

/**
 * Creation d'un produit pour l'artisan. Le formulaire accepte une URL d'image
 * externe OU un upload de fichier (jpg/png/webp, max 5 Mo, stocke dans
 * uploads/produits/). La photo est ajoutee dans image_produit avec
 * est_principale=1.
 */

require_once __DIR__ . '/includes/bootstrap.php';
// Acces strictement reserve aux comptes artisan.
require_role('artisan');
$page_title = 'Nouveau produit - Artify';
$artisan = current_artisan($pdo);
// Garde-fou : un compte artisan sans ligne dans la table artisan n'a pas de boutique a alimenter.
if (!$artisan) { flash_set('error', 'Aucune boutique trouvée.'); redirect('profile.php'); }

// Liste des categories proposees dans le select, tri alpha pour navigation predictible.
$cats = $pdo->query("SELECT id, nom FROM categorie ORDER BY nom")->fetchAll();
// $f : valeurs par defaut fusionnees avec POST pour repopuler le formulaire en cas d'erreur de validation.
$errors = []; $f = $_POST + ['nom'=>'','prix'=>'','categorie_id'=>'','description'=>'','materiaux'=>'','dimensions'=>'','stock'=>'1','delai_fabrication_jours'=>'','est_publie'=>'1'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $nom  = trim($_POST['nom'] ?? '');
    $prix = $_POST['prix'] ?? '';
    // Cast en int sur categorie pour eviter qu'une string passe sans validation au INSERT.
    $cat  = (int)($_POST['categorie_id'] ?? 0);
    if (!$nom) $errors[] = "Nom requis.";
    // is_numeric tolere les decimales contrairement a ctype_digit, et on exige un prix strictement positif.
    if (!is_numeric($prix) || (float)$prix <= 0) $errors[] = "Prix invalide.";
    if (!$cat) $errors[] = "Catégorie requise.";

    // L'artisan_id vient toujours de la session, jamais d'un input cache, pour empecher un artisan de creer un produit sous une autre boutique.
    if (!$errors) {
        $st = $pdo->prepare(
          "INSERT INTO produit (artisan_id, categorie_id, nom, description, prix, materiaux, dimensions,
                                delai_fabrication_jours, stock, est_publie)
           VALUES (?,?,?,?,?,?,?,?,?,?)");
        $st->execute([
            (int)$artisan['id'], $cat, $nom,
            trim($_POST['description'] ?? '') ?: null,
            (float)$prix,
            trim($_POST['materiaux'] ?? '') ?: null,
            trim($_POST['dimensions'] ?? '') ?: null,
            $_POST['delai_fabrication_jours'] !== '' ? (int)$_POST['delai_fabrication_jours'] : null,
            (int)($_POST['stock'] ?? 0),
            isset($_POST['est_publie']) ? 1 : 0,
        ]);
        $produit_id = (int)$pdo->lastInsertId();

        // Photo principale : URL externe OU upload local. On controle le MIME reel via mime_content_type plutot que de se fier a l'extension, plus fiable.
        $image_url = trim($_POST['image_url'] ?? '');
        $uploaded  = '';
        if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            // Whitelist explicite : impossible d'uploader un .php meme renomme en .jpg.
            $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
            $mime = mime_content_type($_FILES['photo']['tmp_name']);
            // Plafond a 5 Mo : evite la saturation disque et les uploads abusifs.
            if (isset($allowed[$mime]) && $_FILES['photo']['size'] <= 5 * 1024 * 1024) {
                $ext = $allowed[$mime];
                $dir = __DIR__ . '/uploads/produits';
                // Creation du dossier si premier upload, @ pour silence si deja present concurrent.
                if (!is_dir($dir)) @mkdir($dir, 0755, true);
                // Nom de fichier compose de l'id produit + suffixe aleatoire, pour eviter les collisions et cacher l'ordre d'upload.
                $fname = "p{$produit_id}-" . bin2hex(random_bytes(4)) . ".$ext";
                // move_uploaded_file verifie que le fichier vient bien d'un POST upload, anti-fake.
                if (move_uploaded_file($_FILES['photo']['tmp_name'], "$dir/$fname")) {
                    $uploaded = "uploads/produits/$fname";
                }
            }
        }
        // L'upload local prime sur l'URL externe si les deux sont fournis, sinon on enregistre quand meme une image principale.
        $final_url = $uploaded ?: $image_url;
        if ($final_url) {
            // ordre=0 + est_principale=1 : premiere image, marquee comme la principale par defaut.
            $pdo->prepare("INSERT INTO image_produit (produit_id, url, ordre, est_principale) VALUES (?,?,0,1)")
                ->execute([$produit_id, $final_url]);
        }

        flash_set('success', 'Produit créé.');
        redirect('boutique.php');
    }
}
include __DIR__ . '/includes/header.php';
?>
<div class="crumb"><a href="boutique.php">Ma boutique</a> &rsaquo; Nouveau produit</div>
<div class="form-card">
  <h1>Nouveau produit</h1>
  <?php foreach ($errors as $e): ?><div class="flash flash-error"><?= h($e) ?></div><?php endforeach; ?>
  <?php // enctype multipart obligatoire pour le champ file (upload binaire). ?>
  <form method="post" action="produit_new.php" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="form-row"><label>Nom *</label>
      <input type="text" name="nom" required value="<?= h($f['nom']) ?>"></div>
    <div class="form-row"><label>Photo (URL externe)</label>
      <input type="url" name="image_url" placeholder="https://images.unsplash.com/..."
             value="<?= h($_POST['image_url'] ?? '') ?>"></div>
    <div class="form-row"><label>… ou fichier (JPG/PNG/WebP, max 5 Mo)</label>
      <input type="file" name="photo" accept="image/jpeg,image/png,image/webp"></div>
    <div class="form-row"><label>Catégorie *</label>
      <select name="categorie_id" required>
        <option value="">- choisir -</option>
        <?php foreach ($cats as $c): ?>
          <option value="<?= (int)$c['id'] ?>" <?= (int)$f['categorie_id']===(int)$c['id']?'selected':'' ?>><?= h($c['nom']) ?></option>
        <?php endforeach; ?>
      </select></div>
    <div class="form-row"><label>Prix (€) *</label>
      <input type="number" min="0" step="0.01" name="prix" required value="<?= h($f['prix']) ?>"></div>
    <div class="form-row"><label>Description</label>
      <textarea name="description"><?= h($f['description']) ?></textarea></div>
    <div class="form-row"><label>Matériaux</label>
      <input type="text" name="materiaux" value="<?= h($f['materiaux']) ?>"></div>
    <div class="form-row"><label>Dimensions</label>
      <input type="text" name="dimensions" value="<?= h($f['dimensions']) ?>"></div>
    <div class="form-row"><label>Délai fabrication (jours)</label>
      <input type="number" min="0" name="delai_fabrication_jours" value="<?= h($f['delai_fabrication_jours']) ?>"></div>
    <div class="form-row"><label>Stock</label>
      <input type="number" min="0" name="stock" value="<?= h($f['stock']) ?>"></div>
    <div class="form-row"><label><input type="checkbox" name="est_publie" value="1" checked style="width:auto;margin-right:6px"> Publier immédiatement</label></div>
    <div class="form-actions">
      <button class="btn-primary" type="submit">Créer</button>
      <a class="btn-ghost" href="boutique.php">Annuler</a>
    </div>
  </form>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
