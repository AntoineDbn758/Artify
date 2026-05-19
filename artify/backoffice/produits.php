<?php

/**
 * Liste de tous les produits avec filtres par categorie et artisan. Toggle de
 * publication (est_publie 0/1) sans suppression definitive.
 */

$page_title = 'Produits - Backoffice Artify';
require_once __DIR__ . '/_header.php';
/** @var PDO $pdo */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';
    // Soft-hide via est_publie : on prefere depublier qu'effacer pour ne pas
    // casser les commandes deja passees qui pointent sur ce produit.
    if ($action === 'toggle_publie') {
        $pdo->prepare("UPDATE produit SET est_publie = 1 - est_publie WHERE id = ?")->execute([$id]);
        flash_set('success', 'Publication basculée.');
    } elseif ($action === 'delete') {
        try {
            $pdo->prepare("DELETE FROM produit WHERE id = ?")->execute([$id]);
            flash_set('success', 'Produit supprimé.');
        } catch (\Throwable $e) {
            flash_set('error', 'Suppression impossible (commandes liées).');
        }
    }
    redirect('produits.php' . ($_GET ? '?' . http_build_query($_GET) : ''));
}

$q   = trim($_GET['q'] ?? '');
$cat = (int)($_GET['cat'] ?? 0);
$artid = (int)($_GET['artisan_id'] ?? 0);
$pub = $_GET['pub'] ?? '';

// Filtres cumulables : on n'ajoute la clause que si le filtre est present, et
// on conserve l'ordre des params pour le bind PDO.
$where = []; $params = [];
if ($q !== '')   { $where[] = "p.nom LIKE ?"; $params[] = "%$q%"; }
if ($cat > 0)    { $where[] = "p.categorie_id = ?"; $params[] = $cat; }
if ($artid > 0)  { $where[] = "p.artisan_id = ?"; $params[] = $artid; }
if ($pub === '1' || $pub === '0') { $where[] = "p.est_publie = ?"; $params[] = (int)$pub; }

// Sous-requete pour piocher la miniature : image marquee principale en
// priorite, sinon la premiere selon l'ordre defini par l'artisan.
$sql = "SELECT p.*, c.nom AS cat_nom, a.nom_boutique,
              (SELECT url FROM image_produit ip WHERE ip.produit_id = p.id ORDER BY est_principale DESC, ordre ASC LIMIT 1) AS thumb
         FROM produit p
         JOIN categorie c ON c.id = p.categorie_id
         JOIN artisan a   ON a.id = p.artisan_id";
if ($where) $sql .= " WHERE " . implode(' AND ', $where);
$sql .= " ORDER BY p.created_at DESC";
$st = $pdo->prepare($sql); $st->execute($params);
$rows = $st->fetchAll();

$cats = $pdo->query("SELECT id, nom FROM categorie ORDER BY nom")->fetchAll();
$artisans = $pdo->query("SELECT id, nom_boutique FROM artisan ORDER BY nom_boutique")->fetchAll();
?>
<div class="crumb"><a href="index.php">Backoffice</a> &rsaquo; Produits</div>
<h1>Produits <span style="color:var(--muted);font-size:18px">(<?= count($rows) ?>)</span></h1>

<form class="adm-filters" method="get">
  <div class="fld"><label>Recherche</label>
    <input type="text" name="q" value="<?= h($q) ?>" placeholder="nom du produit">
  </div>
  <div class="fld"><label>Catégorie</label>
    <select name="cat">
      <option value="0">- toutes -</option>
      <?php foreach ($cats as $c): ?>
        <option value="<?= (int)$c['id'] ?>" <?= $cat===(int)$c['id']?'selected':'' ?>><?= h($c['nom']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="fld"><label>Artisan</label>
    <select name="artisan_id">
      <option value="0">- tous -</option>
      <?php foreach ($artisans as $a): ?>
        <option value="<?= (int)$a['id'] ?>" <?= $artid===(int)$a['id']?'selected':'' ?>><?= h($a['nom_boutique']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="fld"><label>Publication</label>
    <select name="pub">
      <option value="">- peu importe -</option>
      <option value="1" <?= $pub==='1'?'selected':'' ?>>publiés</option>
      <option value="0" <?= $pub==='0'?'selected':'' ?>>brouillons</option>
    </select>
  </div>
  <button class="btn-primary" type="submit">Filtrer</button>
  <a class="btn-ghost" href="produits.php">Réinitialiser</a>
</form>

<?php if (!$rows): ?>
  <div class="empty-state">Aucun produit ne correspond.</div>
<?php else: ?>
<table class="adm">
  <thead><tr>
    <th></th><th>#</th><th>Nom</th><th>Artisan</th><th>Catégorie</th>
    <th>Prix</th><th>Stock</th><th>Publié</th><th class="actions">Actions</th>
  </tr></thead>
  <tbody>
  <?php foreach ($rows as $p): ?>
    <tr>
      <td><?php if ($p['thumb']): ?>
        <img class="thumb-sm" src="<?= h($p['thumb']) ?>" alt="">
      <?php else: ?>
        <div class="thumb-sm" style="display:flex;align-items:center;justify-content:center;color:var(--ocre);font-size:11px">-</div>
      <?php endif; ?></td>
      <td><?= (int)$p['id'] ?></td>
      <td><a href="../produit.php?id=<?= (int)$p['id'] ?>" target="_blank"><?= h($p['nom']) ?></a></td>
      <td><?= h($p['nom_boutique']) ?></td>
      <td><span class="badge"><?= h($p['cat_nom']) ?></span></td>
      <td><?= number_format((float)$p['prix'], 2, ',', ' ') ?> €</td>
      <td><?= (int)$p['stock'] ?></td>
      <td><?= $p['est_publie'] ? '<span class="badge ok">oui</span>' : '<span class="badge muted">non</span>' ?></td>
      <td class="actions">
        <form method="post">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="toggle_publie">
          <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
          <button class="<?= $p['est_publie']?'btn-warn':'btn-success' ?> btn-small" type="submit">
            <?= $p['est_publie'] ? 'Dépublier' : 'Publier' ?>
          </button>
        </form>
        <form method="post" onsubmit="return confirm('Supprimer ce produit ?')">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
          <button class="btn-danger btn-small" type="submit">Suppr</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
<?php require_once __DIR__ . '/_footer.php'; ?>
