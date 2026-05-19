<?php

/**
 * Tableau de bord de l'artisan connecte. Liste ses produits, ses evenements
 * et ses ventes. Boutons pour ajouter un nouveau produit ou un nouvel
 * evenement.
 */

require_once __DIR__ . '/includes/bootstrap.php';
// require_role bloque non-artisan, role admin n'est pas exempte ici.
require_role('artisan');
$page_title = 'Ma boutique - Artify';

// Filet de securite : le role artisan est verifie par require_role, mais on garde un fallback si l'enregistrement artisan a ete supprime apres coup.
$artisan = current_artisan($pdo);
if (!$artisan) {
    flash_set('error', 'Aucune boutique trouvée pour ce compte.');
    redirect('profile.php');
}

// MAJ infos boutique
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_shop') {
    csrf_check();
    $nom = trim($_POST['nom_boutique'] ?? '');
    $spec = trim($_POST['specialite'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $site = trim($_POST['site_web'] ?? '');
    $insta = trim($_POST['instagram'] ?? '');
    // Nom obligatoire : invariant metier, une boutique sans nom ne peut pas s'afficher dans l'annuaire.
    if ($nom) {
        // WHERE id = artisan courant : impossible de modifier la boutique d'un autre artisan.
        $pdo->prepare(
          "UPDATE artisan SET nom_boutique=?, specialite=?, description=?, site_web=?, instagram=?
            WHERE id = ?"
        )->execute([$nom, $spec ?: null, $desc ?: null, $site ?: null, $insta ?: null, (int)$artisan['id']]);
        flash_set('success', 'Boutique mise à jour.');
        // PRG pour purger le POST de l'URL.
        redirect('boutique.php');
    }
}
// Refresh des donnees apres l'eventuel UPDATE pour que le formulaire reaffiche les nouvelles valeurs.
$artisan = current_artisan($pdo);

// On liste TOUS les produits de l'artisan, publies ou pas, car c'est sa vue d'admin (contrairement aux pages publiques).
// Pas de filtre est_publie : la vue boutique doit inclure les brouillons que l'artisan n'a pas encore publies.
$produits = $pdo->prepare(
  "SELECT p.id, p.nom, p.prix, p.stock, p.est_publie, c.nom AS categorie
     FROM produit p JOIN categorie c ON c.id = p.categorie_id
    WHERE p.artisan_id = ? ORDER BY p.created_at DESC");
$produits->execute([(int)$artisan['id']]);
$produits = $produits->fetchAll();

// Idem pour les evenements : on remonte les passes/futurs et brouillons inclus.
$evts = $pdo->prepare(
  "SELECT id, titre, date_debut, est_publie FROM evenement
    WHERE artisan_id = ? ORDER BY date_debut DESC");
$evts->execute([(int)$artisan['id']]);
$evts = $evts->fetchAll();

include __DIR__ . '/includes/header.php';
?>
<div class="crumb"><a href="index.php">Accueil</a> &rsaquo; Ma boutique</div>
<h1>Ma boutique : <?= h($artisan['nom_boutique']) ?></h1>

<div class="form-card" style="max-width:760px">
  <h2 style="margin-top:0">Informations de la boutique</h2>
  <form method="post" action="boutique.php">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="update_shop">
    <div class="form-row"><label>Nom de la boutique</label>
      <input type="text" name="nom_boutique" required value="<?= h($artisan['nom_boutique']) ?>"></div>
    <div class="form-row"><label>Spécialité</label>
      <input type="text" name="specialite" value="<?= h($artisan['specialite'] ?? '') ?>"></div>
    <div class="form-row"><label>Description</label>
      <textarea name="description"><?= h($artisan['description'] ?? '') ?></textarea></div>
    <div class="form-row"><label>Site web</label>
      <input type="url" name="site_web" value="<?= h($artisan['site_web'] ?? '') ?>"></div>
    <div class="form-row"><label>Instagram</label>
      <input type="text" name="instagram" value="<?= h($artisan['instagram'] ?? '') ?>"></div>
    <div class="form-actions"><button class="btn-primary" type="submit">Enregistrer</button></div>
  </form>
</div>

<div style="display:flex;justify-content:space-between;align-items:baseline;margin-top:30px">
  <h2>Mes produits (<?= count($produits) ?>)</h2>
  <a class="btn-primary" href="produit_new.php">+ Nouveau produit</a>
</div>
<?php if (!$produits): ?>
  <div class="empty">Aucun produit pour le moment. Cliquez sur « Nouveau produit » pour démarrer.</div>
<?php else: ?>
  <table class="tbl">
    <tr><th>Nom</th><th>Catégorie</th><th>Prix</th><th>Stock</th><th>Publié</th><th>Actions</th></tr>
    <?php foreach ($produits as $p): ?>
      <tr>
        <td><a href="produit.php?id=<?= (int)$p['id'] ?>"><?= h($p['nom']) ?></a></td>
        <td><?= h($p['categorie']) ?></td>
        <td><?= number_format((float)$p['prix'], 2, ',', ' ') ?> €</td>
        <td><?= (int)$p['stock'] ?></td>
        <td><?= $p['est_publie'] ? 'oui' : 'non' ?></td>
        <td>
          <a class="btn-ghost btn-small" href="produit_edit.php?id=<?= (int)$p['id'] ?>">Éditer</a>
          <?php // onsubmit confirm JS pour eviter les deletes accidentels, la veritable verif reste cote serveur. ?>
          <form method="post" action="produit_delete.php" onsubmit="return confirm('Supprimer ?')" style="display:inline">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
            <button class="btn-danger btn-small" type="submit">Supprimer</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
<?php endif; ?>

<div style="display:flex;justify-content:space-between;align-items:baseline;margin-top:30px">
  <h2>Mes événements (<?= count($evts) ?>)</h2>
  <a class="btn-primary" href="evenement_new.php">+ Nouvel événement</a>
</div>
<?php if (!$evts): ?>
  <div class="empty">Aucun événement.</div>
<?php else: ?>
  <table class="tbl">
    <tr><th>Titre</th><th>Date</th><th>Publié</th></tr>
    <?php foreach ($evts as $e): ?>
      <tr>
        <td><a href="evenement.php?id=<?= (int)$e['id'] ?>"><?= h($e['titre']) ?></a></td>
        <td><?= h(date('d/m/Y H:i', strtotime($e['date_debut']))) ?></td>
        <td><?= $e['est_publie'] ? 'oui' : 'non' ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
