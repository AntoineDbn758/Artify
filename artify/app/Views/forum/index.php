<?php /** @var array $sujets */ /** @var int $page */ /** @var int $pages */ /** @var ?string $cat */ /** @var int $total */
$CATS = ['general' => 'Général', 'artisanat' => 'Artisanat', 'technique' => 'Technique', 'annonces' => 'Annonces', 'aide' => 'Aide'];
?>
<div class="crumb"><a href="index.php">Accueil</a> &rsaquo; Forum</div>
<div class="forum-header">
  <h1>Forum <span class="meta">(<?= (int)$total ?> sujets)</span></h1>
  <?php if (!empty($_SESSION['user_id'])): ?>
    <a class="btn-primary" href="forum.php?action=new">Nouveau sujet</a>
  <?php else: ?>
    <a class="btn-ghost" href="login_form.php">Se connecter pour participer</a>
  <?php endif; ?>
</div>

<div class="forum-cats">
  <a class="<?= !$cat ? 'active' : '' ?>" href="forum.php">Toutes</a>
  <?php foreach ($CATS as $k => $lbl): ?>
    <a class="<?= $cat === $k ? 'active' : '' ?>" href="forum.php?cat=<?= $k ?>"><?= htmlspecialchars($lbl) ?></a>
  <?php endforeach; ?>
</div>

<?php if (!$sujets): ?>
  <div class="empty">Aucun sujet dans cette catégorie. Soyez le premier !</div>
<?php else: ?>
<table class="forum-table">
  <thead><tr><th>Sujet</th><th>Catégorie</th><th>Auteur</th><th>Réponses</th><th>Dernier message</th></tr></thead>
  <tbody>
  <?php foreach ($sujets as $s): ?>
    <tr>
      <td>
        <?php if ($s['est_epingle']): ?><span class="badge ok">📌</span><?php endif; ?>
        <a href="forum.php?action=show&id=<?= (int)$s['id'] ?>"><?= htmlspecialchars($s['titre']) ?></a>
        <?php if ($s['est_ferme']): ?><span class="badge muted">fermé</span><?php endif; ?>
      </td>
      <td><span class="badge"><?= htmlspecialchars($CATS[$s['categorie']] ?? $s['categorie']) ?></span></td>
      <td><?= htmlspecialchars($s['auteur']) ?></td>
      <td><?= (int)$s['nb_msg'] ?></td>
      <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($s['last_msg_at'] ?: $s['created_at']))) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<?php if ($pages > 1): ?>
  <nav class="pagination" aria-label="Pagination">
    <?php for ($p = 1; $p <= $pages; $p++): ?>
      <a class="<?= $p === $page ? 'active' : '' ?>"
         href="forum.php?page=<?= $p ?><?= $cat ? '&cat=' . urlencode($cat) : '' ?>"><?= $p ?></a>
    <?php endfor; ?>
  </nav>
<?php endif; ?>
<?php endif; ?>
