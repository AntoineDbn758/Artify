<?php
$page_title = 'Événements — Backoffice Artify';
require_once __DIR__ . '/_header.php';
/** @var PDO $pdo */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if ($action === 'toggle_publie') {
        $pdo->prepare("UPDATE evenement SET est_publie = 1 - est_publie WHERE id = ?")->execute([$id]);
        flash_set('success', 'Publication basculée.');
    } elseif ($action === 'delete') {
        $pdo->prepare("DELETE FROM evenement WHERE id = ?")->execute([$id]);
        flash_set('success', 'Événement supprimé.');
    }
    redirect('evenements.php' . (isset($_GET['view']) ? '?view=' . (int)$_GET['view'] : ''));
}

// Voir inscrits ?
$view = (int)($_GET['view'] ?? 0);
if ($view > 0) {
    $evt = $pdo->prepare(
      "SELECT e.*, a.nom_boutique FROM evenement e JOIN artisan a ON a.id = e.artisan_id WHERE e.id = ?"
    );
    $evt->execute([$view]);
    $evt = $evt->fetch();
    if ($evt) {
        $ins = $pdo->prepare(
          "SELECT ie.*, u.prenom, u.nom, u.email FROM inscription_evenement ie
             JOIN utilisateur u ON u.id = ie.utilisateur_id
            WHERE ie.evenement_id = ? ORDER BY ie.date_inscription DESC"
        );
        $ins->execute([$view]);
        $ins = $ins->fetchAll();
    }
}

$rows = $pdo->query(
  "SELECT e.*, a.nom_boutique,
          (SELECT COUNT(*) FROM inscription_evenement ie WHERE ie.evenement_id = e.id AND ie.statut='confirmee') AS nb_inscrits
     FROM evenement e JOIN artisan a ON a.id = e.artisan_id
    ORDER BY e.date_debut DESC"
)->fetchAll();
?>
<div class="crumb"><a href="index.php">Backoffice</a> &rsaquo; Événements</div>

<?php if ($view > 0 && isset($evt) && $evt): ?>
  <h1>Inscrits — <?= h($evt['titre']) ?></h1>
  <p class="meta"><?= h($evt['nom_boutique']) ?> · <?= h(date('d/m/Y H:i', strtotime($evt['date_debut']))) ?> · <?= h($evt['ville'] ?: '') ?></p>
  <p><a class="btn-ghost btn-small" href="evenements.php">&lsaquo; Retour à la liste</a></p>
  <?php if (!$ins): ?>
    <div class="empty-state">Aucun inscrit pour le moment.</div>
  <?php else: ?>
    <table class="adm">
      <thead><tr><th>#</th><th>Nom</th><th>Email</th><th>Date inscription</th><th>Statut</th></tr></thead>
      <tbody>
      <?php foreach ($ins as $i): ?>
        <tr>
          <td><?= (int)$i['id'] ?></td>
          <td><?= h(trim(($i['prenom'] ?? '') . ' ' . ($i['nom'] ?? ''))) ?></td>
          <td><?= h($i['email']) ?></td>
          <td><?= h(date('d/m/Y H:i', strtotime($i['date_inscription']))) ?></td>
          <td><span class="badge"><?= h($i['statut']) ?></span></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
<?php else: ?>
  <h1>Événements <span style="color:var(--muted);font-size:18px">(<?= count($rows) ?>)</span></h1>
  <?php if (!$rows): ?>
    <div class="empty-state">Aucun événement.</div>
  <?php else: ?>
  <table class="adm">
    <thead><tr>
      <th>#</th><th>Titre</th><th>Artisan</th><th>Date</th><th>Ville</th>
      <th>Prix</th><th>Cap.</th><th>Inscrits</th><th>Publié</th><th class="actions">Actions</th>
    </tr></thead>
    <tbody>
    <?php foreach ($rows as $e): ?>
      <tr>
        <td><?= (int)$e['id'] ?></td>
        <td><?= h($e['titre']) ?></td>
        <td><?= h($e['nom_boutique']) ?></td>
        <td><?= h(date('d/m/Y H:i', strtotime($e['date_debut']))) ?></td>
        <td><?= h($e['ville'] ?: '—') ?></td>
        <td><?= number_format((float)$e['prix_entree'], 2, ',', ' ') ?> €</td>
        <td><?= $e['capacite_max'] !== null ? (int)$e['capacite_max'] : '∞' ?></td>
        <td><?= (int)$e['nb_inscrits'] ?></td>
        <td><?= $e['est_publie'] ? '<span class="badge ok">oui</span>' : '<span class="badge muted">non</span>' ?></td>
        <td class="actions">
          <a class="btn-edit btn-small" href="evenement_edit.php?id=<?= (int)$e['id'] ?>">Modifier</a>
          <a class="btn-ghost btn-small" href="evenements.php?view=<?= (int)$e['id'] ?>">Inscrits</a>
          <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="toggle_publie">
            <input type="hidden" name="id" value="<?= (int)$e['id'] ?>">
            <button class="<?= $e['est_publie']?'btn-warn':'btn-success' ?> btn-small" type="submit">
              <?= $e['est_publie'] ? 'Dépublier' : 'Publier' ?>
            </button>
          </form>
          <form method="post" onsubmit="return confirm('Supprimer cet événement ?')">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int)$e['id'] ?>">
            <button class="btn-danger btn-small" type="submit">Suppr</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
<?php endif; ?>
<?php require_once __DIR__ . '/_footer.php'; ?>
