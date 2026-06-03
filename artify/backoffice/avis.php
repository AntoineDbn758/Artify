<?php
$page_title = 'Avis — Backoffice Artify';
require_once __DIR__ . '/_header.php';
/** @var PDO $pdo */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    if (($_POST['action'] ?? '') === 'delete') {
        // Récupérer artisan_id avant suppression pour recalc
        $r = $pdo->prepare("SELECT artisan_id FROM avis WHERE id = ?");
        $r->execute([$id]); $aid = (int)$r->fetchColumn();
        $pdo->prepare("DELETE FROM avis WHERE id = ?")->execute([$id]);
        if ($aid > 0) {
            // Recalcul moyenne et nb_avis
            $st = $pdo->prepare("SELECT AVG(note) AS m, COUNT(*) AS n FROM avis WHERE artisan_id = ?");
            $st->execute([$aid]); $agg = $st->fetch();
            $pdo->prepare("UPDATE artisan SET note_moyenne = ?, nb_avis = ? WHERE id = ?")
                ->execute([round((float)($agg['m'] ?? 0), 2), (int)($agg['n'] ?? 0), $aid]);
        }
        flash_set('success', 'Avis supprimé.');
    }
    redirect('avis.php' . ($_GET ? '?' . http_build_query($_GET) : ''));
}

$min = (int)($_GET['min'] ?? 0);
$artid = (int)($_GET['artisan_id'] ?? 0);
$where = []; $params = [];
if ($min > 0) { $where[] = "a.note <= ?"; $params[] = $min; }
if ($artid > 0) { $where[] = "a.artisan_id = ?"; $params[] = $artid; }
$sql = "SELECT a.*, u.prenom, u.nom, u.email, ar.nom_boutique
          FROM avis a
          JOIN utilisateur u ON u.id = a.utilisateur_id
          JOIN artisan ar    ON ar.id = a.artisan_id";
if ($where) $sql .= " WHERE " . implode(' AND ', $where);
$sql .= " ORDER BY a.created_at DESC";
$st = $pdo->prepare($sql); $st->execute($params);
$rows = $st->fetchAll();

$artisans = $pdo->query("SELECT id, nom_boutique FROM artisan ORDER BY nom_boutique")->fetchAll();
?>
<div class="crumb"><a href="index.php">Backoffice</a> &rsaquo; Avis</div>
<h1>Modération des avis <span style="color:var(--muted);font-size:18px">(<?= count($rows) ?>)</span></h1>

<form class="adm-filters" method="get">
  <div class="fld"><label>Avis avec note &le;</label>
    <select name="min">
      <option value="0">— peu importe —</option>
      <?php for ($i=1;$i<=5;$i++): ?>
        <option value="<?= $i ?>" <?= $min===$i?'selected':'' ?>><?= $i ?> étoile<?= $i>1?'s':'' ?> ou moins</option>
      <?php endfor; ?>
    </select>
  </div>
  <div class="fld"><label>Artisan</label>
    <select name="artisan_id">
      <option value="0">— tous —</option>
      <?php foreach ($artisans as $a): ?>
        <option value="<?= (int)$a['id'] ?>" <?= $artid===(int)$a['id']?'selected':'' ?>><?= h($a['nom_boutique']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <button class="btn-primary" type="submit">Filtrer</button>
  <a class="btn-ghost" href="avis.php">Réinitialiser</a>
</form>

<?php if (!$rows): ?>
  <div class="empty-state">Aucun avis ne correspond.</div>
<?php else: ?>
<table class="adm">
  <thead><tr>
    <th>#</th><th>Date</th><th>De</th><th>Artisan</th><th>Note</th><th>Commentaire</th><th class="actions">Actions</th>
  </tr></thead>
  <tbody>
  <?php foreach ($rows as $a): ?>
    <tr>
      <td><?= (int)$a['id'] ?></td>
      <td><?= h(date('d/m/Y', strtotime($a['created_at']))) ?></td>
      <td><?= h(trim(($a['prenom'] ?? '') . ' ' . ($a['nom'] ?? ''))) ?><br>
          <span style="font-size:12px;color:var(--muted)"><?= h($a['email']) ?></span></td>
      <td><?= h($a['nom_boutique']) ?></td>
      <td>
        <?php $cls = (int)$a['note'] <= 2 ? 'err' : ((int)$a['note'] === 3 ? 'warn' : 'ok'); ?>
        <span class="badge <?= $cls ?>"><?= str_repeat('★', (int)$a['note']) . str_repeat('☆', 5 - (int)$a['note']) ?></span>
      </td>
      <td style="max-width:380px"><?= nl2br(h(mb_substr($a['commentaire'] ?? '', 0, 240))) ?><?= mb_strlen($a['commentaire'] ?? '')>240?'…':'' ?></td>
      <td class="actions">
        <form method="post" onsubmit="return confirm('Supprimer cet avis ?')">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
          <button class="btn-danger btn-small" type="submit">Supprimer</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
<?php require_once __DIR__ . '/_footer.php'; ?>
