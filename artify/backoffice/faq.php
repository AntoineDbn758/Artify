<?php
// On masque les notices/warnings non-bloquants pour cette page
// (les tables FAQ utilisent des défauts qui peuvent émettre des notices PHP 8 inoffensives).
error_reporting(E_ERROR | E_PARSE);

$page_title = 'FAQ — Backoffice Artify';
require_once __DIR__ . '/_header.php';
/** @var PDO $pdo */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $q = trim($_POST['question'] ?? '');
        $r = trim($_POST['reponse']  ?? '');
        $o = (int)($_POST['ordre'] ?? 0);
        if ($q && $r) {
            $pdo->prepare("INSERT INTO faq (question, reponse, ordre, est_actif) VALUES (?,?,?,1)")
                ->execute([$q, $r, $o]);
            flash_set('success', 'FAQ créée.');
        } else flash_set('error', 'Question et réponse requises.');
    } elseif ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        $q = trim($_POST['question'] ?? '');
        $r = trim($_POST['reponse']  ?? '');
        $o = (int)($_POST['ordre'] ?? 0);
        $a = isset($_POST['est_actif']) ? 1 : 0;
        $pdo->prepare("UPDATE faq SET question=?, reponse=?, ordre=?, est_actif=? WHERE id = ?")
            ->execute([$q, $r, $o, $a, $id]);
        flash_set('success', 'FAQ mise à jour.');
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare("DELETE FROM faq WHERE id = ?")->execute([$id]);
        flash_set('success', 'FAQ supprimée.');
    }
    redirect('faq.php');
}

$faqs = $pdo->query("SELECT * FROM faq ORDER BY ordre ASC, id ASC")->fetchAll();
?>
<div class="crumb"><a href="index.php">Backoffice</a> &rsaquo; FAQ</div>
<h1>Gestion de la FAQ <span style="color:var(--muted);font-size:18px">(<?= count($faqs) ?>)</span></h1>

<div class="admin-card" style="max-width:760px">
  <h3>Nouvelle entrée</h3>
  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="create">
    <div class="form-row"><label>Question</label><input type="text" name="question" required></div>
    <div class="form-row"><label>Réponse</label><textarea name="reponse" required></textarea></div>
    <div class="form-row"><label>Ordre</label><input type="number" name="ordre" value="0" style="width:120px"></div>
    <div class="form-actions"><button class="btn-primary" type="submit">Ajouter</button></div>
  </form>
</div>

<h2>Entrées existantes</h2>
<?php if (!$faqs): ?>
  <div class="empty-state">Aucune entrée FAQ.</div>
<?php endif; ?>
<?php foreach ($faqs as $f): ?>
  <div class="admin-card">
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="id" value="<?= (int)$f['id'] ?>">
      <div class="form-row"><label>Question</label><input type="text" name="question" required value="<?= h($f['question']) ?>"></div>
      <div class="form-row"><label>Réponse</label><textarea name="reponse" required><?= h($f['reponse']) ?></textarea></div>
      <div class="form-row" style="display:flex;gap:16px;align-items:end;flex-wrap:wrap">
        <div><label>Ordre</label><input type="number" name="ordre" value="<?= (int)$f['ordre'] ?>" style="width:90px"></div>
        <label style="display:flex;align-items:center;gap:6px"><input type="checkbox" name="est_actif" value="1" <?= $f['est_actif']?'checked':'' ?> style="width:auto"> Actif</label>
        <button class="btn-edit btn-small" type="submit">Enregistrer</button>
      </div>
    </form>
    <form method="post" onsubmit="return confirm('Supprimer ?')" style="margin-top:6px">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="delete">
      <input type="hidden" name="id" value="<?= (int)$f['id'] ?>">
      <button class="btn-danger btn-small" type="submit">Supprimer cette entrée</button>
    </form>
  </div>
<?php endforeach; ?>
<?php require_once __DIR__ . '/_footer.php'; ?>
