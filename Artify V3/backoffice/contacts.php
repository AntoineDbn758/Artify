<?php
$page_title = 'Contacts — Backoffice Artify';
require_once __DIR__ . '/_header.php';
/** @var PDO $pdo */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    if (($_POST['action'] ?? '') === 'toggle') {
        $pdo->prepare("UPDATE contact SET traite = 1 - traite WHERE id = ?")->execute([$id]);
        flash_set('success', 'Statut mis à jour.');
    } elseif (($_POST['action'] ?? '') === 'delete') {
        $pdo->prepare("DELETE FROM contact WHERE id = ?")->execute([$id]);
        flash_set('success', 'Message supprimé.');
    }
    redirect('contacts.php' . ($_GET ? '?' . http_build_query($_GET) : ''));
}

$f = $_GET['f'] ?? '';
$where = ''; $params = [];
if ($f === 'nontr') { $where = "WHERE traite = 0"; }
elseif ($f === 'tr') { $where = "WHERE traite = 1"; }
$msgs = $pdo->prepare("SELECT * FROM contact $where ORDER BY traite ASC, created_at DESC");
$msgs->execute($params);
$msgs = $msgs->fetchAll();
?>
<div class="crumb"><a href="index.php">Backoffice</a> &rsaquo; Contacts</div>
<h1>Messages de contact <span style="color:var(--muted);font-size:18px">(<?= count($msgs) ?>)</span></h1>

<form class="adm-filters" method="get">
  <div class="fld"><label>Filtre</label>
    <select name="f" onchange="this.form.submit()">
      <option value="" <?= $f===''?'selected':'' ?>>— tous —</option>
      <option value="nontr" <?= $f==='nontr'?'selected':'' ?>>non traités</option>
      <option value="tr" <?= $f==='tr'?'selected':'' ?>>traités</option>
    </select>
  </div>
</form>

<?php if (!$msgs): ?>
  <div class="empty-state">Aucun message.</div>
<?php else: ?>
<table class="adm">
  <thead><tr>
    <th>Reçu le</th><th>De</th><th>Sujet</th><th>Message</th><th>Statut</th><th class="actions">Actions</th>
  </tr></thead>
  <tbody>
  <?php foreach ($msgs as $m): ?>
    <tr>
      <td><?= h(date('d/m/Y H:i', strtotime($m['created_at']))) ?></td>
      <td>
        <strong><?= h($m['nom']) ?></strong><br>
        <a href="mailto:<?= h($m['email']) ?>" style="font-size:12px;color:var(--muted)"><?= h($m['email']) ?></a>
      </td>
      <td><?= h($m['sujet']) ?></td>
      <td style="max-width:400px"><?= nl2br(h(mb_substr($m['message'], 0, 280))) ?><?= mb_strlen($m['message'])>280?'…':'' ?></td>
      <td><?= $m['traite'] ? '<span class="badge ok">traité</span>' : '<span class="badge warn">non traité</span>' ?></td>
      <td class="actions">
        <form method="post">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="toggle">
          <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
          <button class="<?= $m['traite']?'btn-warn':'btn-success' ?> btn-small" type="submit">
            <?= $m['traite'] ? 'Rouvrir' : 'Marquer traité' ?>
          </button>
        </form>
        <form method="post" onsubmit="return confirm('Supprimer ce message ?')">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
          <button class="btn-danger btn-small" type="submit">Suppr</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
<?php require_once __DIR__ . '/_footer.php'; ?>
