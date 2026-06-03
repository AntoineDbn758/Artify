<?php
require_once __DIR__ . '/includes/bootstrap.php';
$page_title = 'Créations — Artify';

// === Filtres recherche multicritère ===
$cat       = (int)($_GET['cat'] ?? 0);
$q         = trim($_GET['q'] ?? '');
$ville     = trim($_GET['ville'] ?? '');
$prixMin   = isset($_GET['prix_min']) && $_GET['prix_min'] !== '' ? (float)$_GET['prix_min'] : null;
$prixMax   = isset($_GET['prix_max']) && $_GET['prix_max'] !== '' ? (float)$_GET['prix_max'] : null;
$tri       = $_GET['tri'] ?? 'recent';

$cats = $pdo->query("SELECT id, nom FROM categorie ORDER BY nom")->fetchAll();

// === Pagination ===
$perPage = 12;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

$where = ["p.est_publie = 1"];
$params = [];
if ($cat > 0)    { $where[] = "p.categorie_id = ?"; $params[] = $cat; }
if ($q !== '')   {
    // Multi-mots-clés : chaque terme doit matcher au moins un champ
    foreach (preg_split('/\s+/', $q) as $term) {
        if ($term === '') continue;
        $where[] = "(p.nom LIKE ? OR p.description LIKE ? OR p.materiaux LIKE ?)";
        $like = "%$term%";
        array_push($params, $like, $like, $like);
    }
}
if ($ville !== '') { $where[] = "u.ville LIKE ?"; $params[] = "%$ville%"; }
if ($prixMin !== null) { $where[] = "p.prix >= ?"; $params[] = $prixMin; }
if ($prixMax !== null) { $where[] = "p.prix <= ?"; $params[] = $prixMax; }

$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
$orderBy = match ($tri) {
    'prix_asc'  => 'p.prix ASC',
    'prix_desc' => 'p.prix DESC',
    'nom'       => 'p.nom ASC',
    default     => 'p.created_at DESC',
};

// COUNT pour pagination
$sqlCount = "
  SELECT COUNT(*)
    FROM produit p
    JOIN categorie c ON c.id = p.categorie_id
    JOIN artisan a   ON a.id = p.artisan_id
    JOIN utilisateur u ON u.id = a.utilisateur_id
    $whereSql
";
$st = $pdo->prepare($sqlCount); $st->execute($params);
$total = (int)$st->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage));

// SELECT page courante
$sql = "
  SELECT p.id, p.nom, p.prix, p.materiaux, c.nom AS categorie, a.nom_boutique, u.ville,
         ip.url AS image_url
    FROM produit p
    JOIN categorie c ON c.id = p.categorie_id
    JOIN artisan a   ON a.id = p.artisan_id
    JOIN utilisateur u ON u.id = a.utilisateur_id
    LEFT JOIN image_produit ip ON ip.produit_id = p.id AND ip.est_principale = 1
    $whereSql
    ORDER BY $orderBy
    LIMIT $perPage OFFSET $offset
";
$st = $pdo->prepare($sql); $st->execute($params);
$produits = $st->fetchAll();

include __DIR__ . '/includes/header.php';

/** Construit une URL de pagination en gardant tous les filtres actifs. */
function pageUrl(int $p): string {
    $params = $_GET; $params['page'] = $p;
    return 'creations.php?' . http_build_query($params);
}
?>
<div class="crumb"><a href="index.php">Accueil</a> &rsaquo; Créations</div>
<h1>Créations <span class="meta">(<?= $total ?> résultat<?= $total > 1 ? 's' : '' ?>)</span></h1>

<form class="search-filters" method="get" action="creations.php">
  <div><label for="f-q">Mots-clés</label>
    <input id="f-q" type="text" name="q" value="<?= h($q) ?>" placeholder="ex: bague argent">
  </div>
  <div><label for="f-cat">Catégorie</label>
    <select id="f-cat" name="cat">
      <option value="0">— Toutes —</option>
      <?php foreach ($cats as $c): ?>
        <option value="<?= (int)$c['id'] ?>" <?= $cat === (int)$c['id'] ? 'selected' : '' ?>><?= h($c['nom']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div><label for="f-ville">Ville</label>
    <input id="f-ville" type="text" name="ville" value="<?= h($ville) ?>" placeholder="ex: Paris">
  </div>
  <div><label for="f-min">Prix min (€)</label>
    <input id="f-min" type="number" name="prix_min" min="0" step="1" value="<?= h($_GET['prix_min'] ?? '') ?>">
  </div>
  <div><label for="f-max">Prix max (€)</label>
    <input id="f-max" type="number" name="prix_max" min="0" step="1" value="<?= h($_GET['prix_max'] ?? '') ?>">
  </div>
  <div><label for="f-tri">Tri</label>
    <select id="f-tri" name="tri">
      <option value="recent"    <?= $tri==='recent'?'selected':'' ?>>Plus récents</option>
      <option value="prix_asc"  <?= $tri==='prix_asc'?'selected':'' ?>>Prix croissant</option>
      <option value="prix_desc" <?= $tri==='prix_desc'?'selected':'' ?>>Prix décroissant</option>
      <option value="nom"       <?= $tri==='nom'?'selected':'' ?>>Nom A→Z</option>
    </select>
  </div>
  <div class="actions">
    <button class="btn-primary btn-small" type="submit">Filtrer</button>
    <a class="btn-ghost btn-small" href="creations.php">Réinitialiser</a>
  </div>
</form>

<?php if (!$produits): ?>
  <div class="empty">Aucune création ne correspond à ces critères.</div>
<?php else: ?>
  <div class="grid grid-3">
    <?php foreach ($produits as $p): ?>
      <a class="card" href="produit.php?id=<?= (int)$p['id'] ?>" style="color:inherit">
        <img class="thumb" src="<?= h($p['image_url'] ?: 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=600&h=400&fit=crop&q=80') ?>" alt="<?= h($p['nom']) ?>" loading="lazy">
        <h3><?= h($p['nom']) ?></h3>
        <div class="meta"><?= h($p['nom_boutique']) ?> · <?= h($p['categorie']) ?><?= $p['ville'] ? ' · ' . h($p['ville']) : '' ?></div>
        <?php if (!empty($p['materiaux'])): ?>
          <div class="meta"><?= h(mb_substr($p['materiaux'], 0, 80)) ?></div>
        <?php endif; ?>
        <div class="price"><?= number_format((float)$p['prix'], 2, ',', ' ') ?> &euro;</div>
      </a>
    <?php endforeach; ?>
  </div>

  <?php if ($pages > 1): ?>
    <nav class="pagination" aria-label="Pagination">
      <?php if ($page > 1): ?><a href="<?= h(pageUrl($page - 1)) ?>">&laquo;</a><?php endif; ?>
      <?php for ($p = 1; $p <= $pages; $p++): ?>
        <a class="<?= $p === $page ? 'active' : '' ?>" href="<?= h(pageUrl($p)) ?>"><?= $p ?></a>
      <?php endfor; ?>
      <?php if ($page < $pages): ?><a href="<?= h(pageUrl($page + 1)) ?>">&raquo;</a><?php endif; ?>
    </nav>
  <?php endif; ?>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
