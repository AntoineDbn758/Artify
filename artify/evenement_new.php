<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_role('artisan');
$page_title = 'Nouvel événement - Artify';
$artisan = current_artisan($pdo);
if (!$artisan) { flash_set('error', 'Aucune boutique.'); redirect('profile.php'); }

$errors = []; $f = $_POST + ['titre'=>'','description'=>'','lieu'=>'','ville'=>'','date_debut'=>'','date_fin'=>'','prix_entree'=>'0','capacite_max'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $titre = trim($_POST['titre'] ?? '');
    $dd    = $_POST['date_debut'] ?? '';
    if (!$titre) $errors[] = "Titre requis.";
    if (!$dd || !strtotime($dd)) $errors[] = "Date de début invalide.";

    if (!$errors) {
        $df = $_POST['date_fin'] ?? '';
        $pdo->prepare(
          "INSERT INTO evenement (artisan_id, titre, description, lieu, ville, date_debut, date_fin,
                                  capacite_max, prix_entree, est_publie)
           VALUES (?,?,?,?,?,?,?,?,?,1)"
        )->execute([
            (int)$artisan['id'], $titre,
            trim($_POST['description'] ?? '') ?: null,
            trim($_POST['lieu'] ?? '') ?: null,
            trim($_POST['ville'] ?? '') ?: null,
            date('Y-m-d H:i:s', strtotime($dd)),
            $df ? date('Y-m-d H:i:s', strtotime($df)) : null,
            $_POST['capacite_max'] !== '' ? (int)$_POST['capacite_max'] : null,
            (float)($_POST['prix_entree'] ?? 0),
        ]);
        flash_set('success', 'Événement créé.');
        redirect('boutique.php');
    }
}
include __DIR__ . '/includes/header.php';
?>
<div class="crumb"><a href="boutique.php">Ma boutique</a> &rsaquo; Nouvel événement</div>
<div class="form-card">
  <h1>Nouvel événement</h1>
  <?php foreach ($errors as $e): ?><div class="flash flash-error"><?= h($e) ?></div><?php endforeach; ?>
  <form method="post" action="evenement_new.php">
    <?= csrf_field() ?>
    <div class="form-row"><label>Titre *</label><input type="text" name="titre" required value="<?= h($f['titre']) ?>"></div>
    <div class="form-row"><label>Description</label><textarea name="description"><?= h($f['description']) ?></textarea></div>
    <div class="form-row"><label>Lieu</label><input type="text" name="lieu" value="<?= h($f['lieu']) ?>"></div>
    <div class="form-row"><label>Ville</label><input type="text" name="ville" value="<?= h($f['ville']) ?>"></div>
    <div class="form-row"><label>Date de début *</label><input type="datetime-local" name="date_debut" required value="<?= h($f['date_debut']) ?>"></div>
    <div class="form-row"><label>Date de fin</label><input type="datetime-local" name="date_fin" value="<?= h($f['date_fin']) ?>"></div>
    <div class="form-row"><label>Prix d'entrée (€)</label><input type="number" min="0" step="0.01" name="prix_entree" value="<?= h($f['prix_entree']) ?>"></div>
    <div class="form-row"><label>Capacité maximale</label><input type="number" min="0" name="capacite_max" value="<?= h($f['capacite_max']) ?>"></div>
    <div class="form-actions">
      <button class="btn-primary" type="submit">Publier</button>
      <a class="btn-ghost" href="boutique.php">Annuler</a>
    </div>
  </form>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
