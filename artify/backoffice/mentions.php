<?php
$page_title = 'Mentions légales - Backoffice Artify';
require_once __DIR__ . '/_header.php';
/** @var PDO $pdo */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $contenu = trim($_POST['contenu'] ?? '');
    if ($contenu) {
        $existing = $pdo->query("SELECT id FROM mention_legale ORDER BY updated_at DESC LIMIT 1")->fetch();
        if ($existing) {
            $pdo->prepare("UPDATE mention_legale SET contenu = ? WHERE id = ?")
                ->execute([$contenu, (int)$existing['id']]);
        } else {
            $pdo->prepare("INSERT INTO mention_legale (contenu) VALUES (?)")->execute([$contenu]);
        }
        flash_set('success', 'Mentions légales mises à jour.');
    } else {
        flash_set('error', 'Contenu vide.');
    }
    redirect('mentions.php');
}

$ml = $pdo->query("SELECT * FROM mention_legale ORDER BY updated_at DESC LIMIT 1")->fetch();
?>
<div class="crumb"><a href="index.php">Backoffice</a> &rsaquo; Mentions légales</div>
<h1>Gestion des mentions légales</h1>

<div class="admin-card" style="max-width:880px">
  <form method="post">
    <?= csrf_field() ?>
    <div class="form-row"><label>Contenu</label>
      <textarea name="contenu" required style="min-height:340px"><?= h($ml['contenu'] ?? '') ?></textarea></div>
    <div class="form-actions"><button class="btn-primary" type="submit">Enregistrer</button></div>
  </form>
  <?php if ($ml): ?>
    <p class="meta" style="margin-top:12px;font-size:13px;color:var(--muted)">
      Dernière mise à jour : <?= h(date('d/m/Y H:i', strtotime($ml['updated_at']))) ?>
    </p>
  <?php endif; ?>
</div>
<?php require_once __DIR__ . '/_footer.php'; ?>
