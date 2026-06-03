<?php
$page_title = 'CGU — Backoffice Artify';
require_once __DIR__ . '/_header.php';
/** @var PDO $pdo */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (($_POST['action'] ?? '') === 'create') {
        $contenu = trim($_POST['contenu'] ?? '');
        $version = trim($_POST['version'] ?? '1.0');
        $date = $_POST['date_effet'] ?: date('Y-m-d');
        if ($contenu) {
            $pdo->exec("UPDATE cgu SET est_actif = 0");
            $pdo->prepare("INSERT INTO cgu (contenu, version, date_effet, est_actif) VALUES (?,?,?,1)")
                ->execute([$contenu, $version, $date]);
            flash_set('success', 'Nouvelle version des CGU publiée.');
        } else flash_set('error', 'Contenu vide.');
    } elseif (($_POST['action'] ?? '') === 'toggle') {
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare("UPDATE cgu SET est_actif = 1 - est_actif WHERE id = ?")->execute([$id]);
        flash_set('success', 'Statut basculé.');
    } elseif (($_POST['action'] ?? '') === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare("DELETE FROM cgu WHERE id = ?")->execute([$id]);
        flash_set('success', 'Version supprimée.');
    }
    redirect('cgu.php');
}

$all = $pdo->query("SELECT * FROM cgu ORDER BY date_effet DESC, id DESC")->fetchAll();
$current = null;
foreach ($all as $c) if ($c['est_actif']) { $current = $c; break; }
?>
<div class="crumb"><a href="index.php">Backoffice</a> &rsaquo; CGU</div>
<h1>Gestion des CGU</h1>
<p>Publier une nouvelle version désactive automatiquement les versions précédentes.</p>

<?php if ($current): ?>
  <div class="admin-card">
    <h3>Version en vigueur : <?= h($current['version']) ?>
      <span class="badge ok">active</span></h3>
    <p class="meta">En vigueur depuis le <?= h(date('d/m/Y', strtotime($current['date_effet']))) ?></p>
    <p><?= nl2br(h(mb_substr($current['contenu'], 0, 500))) ?><?= mb_strlen($current['contenu']) > 500 ? '…' : '' ?></p>
  </div>
<?php else: ?>
  <div class="empty-state">Aucune CGU active. Publiez la première version ci-dessous.</div>
<?php endif; ?>

<div class="admin-card" style="max-width:820px">
  <h3>Publier une nouvelle version</h3>
  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="create">
    <div class="form-row"><label>Version</label>
      <input type="text" name="version" value="<?= h($current ? sprintf('%.1f', (float)$current['version'] + 0.1) : '1.0') ?>" style="width:140px"></div>
    <div class="form-row"><label>Date d'effet</label>
      <input type="date" name="date_effet" value="<?= date('Y-m-d') ?>" style="width:200px"></div>
    <div class="form-row"><label>Contenu</label>
      <textarea name="contenu" required style="min-height:240px"></textarea></div>
    <div class="form-actions"><button class="btn-primary" type="submit">Publier cette version</button></div>
  </form>
</div>

<h2>Historique (<?= count($all) ?>)</h2>
<?php if (!$all): ?>
  <div class="empty-state">Aucune version enregistrée.</div>
<?php else: ?>
<table class="adm">
  <thead><tr><th>Version</th><th>Date d'effet</th><th>Statut</th><th>Aperçu</th><th class="actions">Actions</th></tr></thead>
  <tbody>
  <?php foreach ($all as $c): ?>
    <tr>
      <td><strong><?= h($c['version']) ?></strong></td>
      <td><?= h(date('d/m/Y', strtotime($c['date_effet']))) ?></td>
      <td><?= $c['est_actif'] ? '<span class="badge ok">active</span>' : '<span class="badge muted">archivée</span>' ?></td>
      <td><?= h(mb_substr($c['contenu'], 0, 100)) ?>…</td>
      <td class="actions">
        <form method="post">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="toggle">
          <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
          <button class="<?= $c['est_actif']?'btn-warn':'btn-success' ?> btn-small" type="submit">
            <?= $c['est_actif'] ? 'Désactiver' : 'Réactiver' ?>
          </button>
        </form>
        <form method="post" onsubmit="return confirm('Supprimer cette version ?')">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
          <button class="btn-danger btn-small" type="submit">Suppr</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
<?php require_once __DIR__ . '/_footer.php'; ?>
