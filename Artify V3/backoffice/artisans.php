<?php
$page_title = 'Artisans — Backoffice Artify';
require_once __DIR__ . '/_header.php';
/** @var PDO $pdo */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if ($action === 'toggle_verifie') {
        $pdo->prepare("UPDATE artisan SET verifie = 1 - verifie WHERE id = ?")->execute([$id]);
        flash_set('success', 'Statut de vérification mis à jour.');
    } elseif ($action === 'delete') {
        try {
            $pdo->prepare("DELETE FROM artisan WHERE id = ?")->execute([$id]);
            flash_set('success', 'Fiche artisan supprimée.');
        } catch (\Throwable $e) {
            flash_set('error', 'Suppression impossible (produits ou commandes liés).');
        }
    }
    redirect('artisans.php');
}

$q = trim($_GET['q'] ?? '');
$where = []; $params = [];
if ($q !== '') {
    $where[] = "(a.nom_boutique LIKE ? OR a.specialite LIKE ? OR u.email LIKE ?)";
    $params = ["%$q%","%$q%","%$q%"];
}
$sql = "SELECT a.*, u.email, u.prenom, u.nom, u.ville,
               (SELECT COUNT(*) FROM produit p WHERE p.artisan_id = a.id) AS nb_produits,
               (SELECT COUNT(*) FROM evenement e WHERE e.artisan_id = a.id) AS nb_evts
          FROM artisan a JOIN utilisateur u ON u.id = a.utilisateur_id";
if ($where) $sql .= " WHERE " . implode(' AND ', $where);
$sql .= " ORDER BY a.created_at DESC";
$st = $pdo->prepare($sql); $st->execute($params);
$rows = $st->fetchAll();
?>
<div class="crumb"><a href="index.php">Backoffice</a> &rsaquo; Artisans</div>
<h1>Artisans <span style="color:var(--muted);font-size:18px">(<?= count($rows) ?>)</span></h1>

<form class="adm-filters" method="get">
  <div class="fld"><label>Recherche (boutique, spécialité, email)</label>
    <input type="text" name="q" value="<?= h($q) ?>" placeholder="ex: céramique">
  </div>
  <button class="btn-primary" type="submit">Filtrer</button>
  <a class="btn-ghost" href="artisans.php">Réinitialiser</a>
</form>

<?php if (!$rows): ?>
  <div class="empty-state">Aucun artisan ne correspond.</div>
<?php else: ?>
<table class="adm">
  <thead><tr>
    <th>#</th><th>Boutique</th><th>Spécialité</th><th>Propriétaire</th><th>Ville</th>
    <th>Note</th><th>Avis</th><th>Produits</th><th>Vérifié</th><th class="actions">Actions</th>
  </tr></thead>
  <tbody>
  <?php foreach ($rows as $a): ?>
    <tr>
      <td><?= (int)$a['id'] ?></td>
      <td><strong><?= h($a['nom_boutique']) ?></strong></td>
      <td><?= h($a['specialite'] ?: '—') ?></td>
      <td><?= h(trim(($a['prenom'] ?? '') . ' ' . ($a['nom'] ?? ''))) ?><br>
          <span class="meta" style="font-size:12px;color:var(--muted)"><?= h($a['email']) ?></span></td>
      <td><?= h($a['ville'] ?: '—') ?></td>
      <td><?= number_format((float)$a['note_moyenne'], 2, ',', ' ') ?></td>
      <td><?= (int)$a['nb_avis'] ?></td>
      <td><?= (int)$a['nb_produits'] ?></td>
      <td><?= $a['verifie'] ? '<span class="badge ok">vérifié</span>' : '<span class="badge warn">non</span>' ?></td>
      <td class="actions">
        <a class="btn-ghost btn-small" href="produits.php?artisan_id=<?= (int)$a['id'] ?>">Produits</a>
        <form method="post">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="toggle_verifie">
          <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
          <button class="<?= $a['verifie']?'btn-warn':'btn-success' ?> btn-small" type="submit">
            <?= $a['verifie'] ? 'Annuler' : 'Vérifier' ?>
          </button>
        </form>
        <form method="post" onsubmit="return confirm('Supprimer cette fiche artisan ?')">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
          <button class="btn-danger btn-small" type="submit">Suppr</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
<?php require_once __DIR__ . '/_footer.php'; ?>
