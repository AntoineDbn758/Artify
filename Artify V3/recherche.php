<?php
require_once __DIR__ . '/includes/bootstrap.php';
$page_title = 'Recherche — Artify';

// Critères multiples : redirection vers creations.php enrichie qui couvre déjà
// tout le filtrage produit. Ici on garde une vue "recherche transversale"
// (produits + artisans) qui supporte les mêmes critères.
$q       = trim($_GET['q'] ?? '');
$ville   = trim($_GET['ville'] ?? '');
$cat     = (int)($_GET['cat'] ?? 0);
$prixMin = isset($_GET['prix_min']) && $_GET['prix_min'] !== '' ? (float)$_GET['prix_min'] : null;
$prixMax = isset($_GET['prix_max']) && $_GET['prix_max'] !== '' ? (float)$_GET['prix_max'] : null;

$cats = $pdo->query("SELECT id, nom FROM categorie ORDER BY nom")->fetchAll();
$results_prod = [];
$results_art  = [];

$hasCriteria = $q !== '' || $ville !== '' || $cat > 0 || $prixMin !== null || $prixMax !== null;

if ($hasCriteria) {
    try {
        $pdo->prepare("INSERT INTO recherche_log (utilisateur_id, terme) VALUES (?, ?)")
            ->execute([current_user_id(), mb_substr($q, 0, 200)]);
    } catch (PDOException $e) { /* non-bloquant */ }

    // === Produits ===
    $w = ["p.est_publie = 1"]; $params = [];
    if ($q !== '') {
        foreach (preg_split('/\s+/', $q) as $t) {
            if ($t === '') continue;
            $w[] = "(p.nom LIKE ? OR p.description LIKE ? OR p.materiaux LIKE ?)";
            $like = "%$t%"; array_push($params, $like, $like, $like);
        }
    }
    if ($cat > 0) { $w[] = "p.categorie_id = ?"; $params[] = $cat; }
    if ($ville !== '') { $w[] = "u.ville LIKE ?"; $params[] = "%$ville%"; }
    if ($prixMin !== null) { $w[] = "p.prix >= ?"; $params[] = $prixMin; }
    if ($prixMax !== null) { $w[] = "p.prix <= ?"; $params[] = $prixMax; }

    $sql = "SELECT p.id, p.nom, p.prix, c.nom AS categorie, a.nom_boutique, u.ville,
                   ip.url AS image_url
              FROM produit p
              JOIN categorie c ON c.id = p.categorie_id
              JOIN artisan a   ON a.id = p.artisan_id
              JOIN utilisateur u ON u.id = a.utilisateur_id
              LEFT JOIN image_produit ip ON ip.produit_id = p.id AND ip.est_principale = 1
             WHERE " . implode(' AND ', $w) . "
             ORDER BY p.created_at DESC LIMIT 30";
    $st = $pdo->prepare($sql); $st->execute($params);
    $results_prod = $st->fetchAll();

    // === Artisans ===
    if ($q !== '' || $ville !== '') {
        $w = ["u.est_actif = 1"]; $params = [];
        if ($q !== '') {
            foreach (preg_split('/\s+/', $q) as $t) {
                if ($t === '') continue;
                $w[] = "(a.nom_boutique LIKE ? OR a.specialite LIKE ? OR a.description LIKE ?)";
                $like = "%$t%"; array_push($params, $like, $like, $like);
            }
        }
        if ($ville !== '') { $w[] = "u.ville LIKE ?"; $params[] = "%$ville%"; }
        $sql = "SELECT a.id, a.nom_boutique, a.specialite, u.ville
                  FROM artisan a JOIN utilisateur u ON u.id = a.utilisateur_id
                 WHERE " . implode(' AND ', $w) . " LIMIT 30";
        $st = $pdo->prepare($sql); $st->execute($params);
        $results_art = $st->fetchAll();
    }
}

include __DIR__ . '/includes/header.php';
?>
<div class="crumb"><a href="index.php">Accueil</a> &rsaquo; Recherche</div>
<h1>Recherche</h1>

<form class="search-filters" method="get" action="recherche.php">
  <div><label>Mots-clés (multiples)</label>
    <input type="text" name="q" value="<?= h($q) ?>" placeholder="ex: céramique bois">
  </div>
  <div><label>Catégorie</label>
    <select name="cat">
      <option value="0">— Toutes —</option>
      <?php foreach ($cats as $c): ?>
        <option value="<?= (int)$c['id'] ?>" <?= $cat === (int)$c['id'] ? 'selected' : '' ?>><?= h($c['nom']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div><label>Ville</label>
    <input type="text" name="ville" value="<?= h($ville) ?>" placeholder="ex: Paris">
  </div>
  <div><label>Prix min (€)</label>
    <input type="number" name="prix_min" min="0" value="<?= h($_GET['prix_min'] ?? '') ?>">
  </div>
  <div><label>Prix max (€)</label>
    <input type="number" name="prix_max" min="0" value="<?= h($_GET['prix_max'] ?? '') ?>">
  </div>
  <div class="actions">
    <button class="btn-primary btn-small" type="submit">Chercher</button>
    <a class="btn-ghost btn-small" href="recherche.php">Reset</a>
  </div>
</form>

<?php if (!$hasCriteria): ?>
  <p>Combinez plusieurs critères pour affiner votre recherche.</p>
<?php else: ?>
  <h2>Créations (<?= count($results_prod) ?>)</h2>
  <?php if (!$results_prod): ?>
    <div class="empty">Aucune création trouvée.</div>
  <?php else: ?>
    <div class="grid grid-3">
      <?php foreach ($results_prod as $p): ?>
        <a class="card" href="produit.php?id=<?= (int)$p['id'] ?>" style="color:inherit">
          <img class="thumb" src="<?= h($p['image_url'] ?: 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=600&h=400&fit=crop&q=80') ?>" alt="<?= h($p['nom']) ?>" loading="lazy">
          <h3><?= h($p['nom']) ?></h3>
          <div class="meta"><?= h($p['nom_boutique']) ?> · <?= h($p['categorie']) ?><?= $p['ville'] ? ' · ' . h($p['ville']) : '' ?></div>
          <div class="price"><?= number_format((float)$p['prix'], 2, ',', ' ') ?> &euro;</div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <h2 style="margin-top:24px">Artisans (<?= count($results_art) ?>)</h2>
  <?php if (!$results_art): ?>
    <div class="empty">Aucun artisan trouvé.</div>
  <?php else: ?>
    <div class="grid grid-3">
      <?php foreach ($results_art as $a): ?>
        <a class="card" href="artisan.php?id=<?= (int)$a['id'] ?>" style="color:inherit">
          <h3><?= h($a['nom_boutique']) ?></h3>
          <div class="meta"><?= h($a['specialite'] ?: '—') ?><?= $a['ville'] ? ' · ' . h($a['ville']) : '' ?></div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
