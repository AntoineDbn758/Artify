<?php

/**
 * CRUD complet des categories (Bijouterie, Ceramique, Textile, ...). Le slug
 * est genere automatiquement a partir du nom.
 */

$page_title = 'Catégories - Backoffice Artify';
require_once __DIR__ . '/_header.php';
/** @var PDO $pdo */

// Slug URL-safe : retrait des accents via iconv, remplacement de tout caractere
// non alphanumerique par un tiret, puis trim des tirets aux bords.
function slugify(string $s): string {
    // Lowercase Unicode-safe avant translit ASCII.
    $s = mb_strtolower(trim($s), 'UTF-8');
    // iconv TRANSLIT : "Céramique" -> "Ceramique" (suppression accents).
    $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s) ?: $s;
    // Collapse de tout caractere non a-z0-9 en un tiret.
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    // Trim des tirets aux bords ("-poterie-" -> "poterie").
    return trim($s ?? '', '-');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $nom = trim($_POST['nom'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        // Le nom est obligatoire ; on degage tot avec un flash error.
        if (!$nom) { flash_set('error', 'Nom requis.'); redirect('categories.php'); }
        // Slug auto si l'admin n'en a pas saisi un manuellement.
        if (!$slug) $slug = slugify($nom);
        try {
            $pdo->prepare("INSERT INTO categorie (nom, slug) VALUES (?, ?)")->execute([$nom, $slug]);
            flash_set('success', 'Catégorie créée.');
        } catch (\Throwable $e) {
            // Index unique sur slug : doublon => exception PDO interceptee ici.
            flash_set('error', 'Erreur (slug déjà utilisé ?).');
        }
    } elseif ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        $nom = trim($_POST['nom'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        if (!$nom) { flash_set('error', 'Nom requis.'); redirect('categories.php'); }
        // Meme logique de slug auto en update qu'en create.
        if (!$slug) $slug = slugify($nom);
        try {
            $pdo->prepare("UPDATE categorie SET nom = ?, slug = ? WHERE id = ?")
                ->execute([$nom, $slug, $id]);
            flash_set('success', 'Catégorie mise à jour.');
        } catch (\Throwable $e) {
            flash_set('error', 'Erreur mise à jour.');
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        // La FK produit.categorie_id empechera la suppression si elle est utilisee.
        try {
            $pdo->prepare("DELETE FROM categorie WHERE id = ?")->execute([$id]);
            flash_set('success', 'Catégorie supprimée.');
        } catch (\Throwable $e) {
            flash_set('error', 'Suppression impossible (produits liés).');
        }
    }
    redirect('categories.php');
}

// On compte les produits par categorie pour pouvoir desactiver le bouton
// supprimer cote UI quand la categorie est encore utilisee.
$rows = $pdo->query(
  "SELECT c.id, c.nom, c.slug,
          (SELECT COUNT(*) FROM produit p WHERE p.categorie_id = c.id) AS nb
     FROM categorie c ORDER BY c.nom"
)->fetchAll();
?>
<div class="crumb"><a href="index.php">Backoffice</a> &rsaquo; Catégories</div>
<h1>Catégories <span style="color:var(--muted);font-size:18px">(<?= count($rows) ?>)</span></h1>

<div class="admin-card" style="max-width:640px">
  <h3>Nouvelle catégorie</h3>
  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="create">
    <div class="form-row"><label>Nom</label><input type="text" name="nom" required></div>
    <div class="form-row"><label>Slug (optionnel - auto-généré)</label><input type="text" name="slug" placeholder="ex: poterie"></div>
    <div class="form-actions"><button class="btn-primary" type="submit">Ajouter</button></div>
  </form>
</div>

<?php if (!$rows): ?>
  <div class="empty-state">Aucune catégorie pour le moment.</div>
<?php else: ?>
<table class="adm">
  <thead><tr><th>#</th><th>Nom</th><th>Slug</th><th>Produits</th><th class="actions">Actions</th></tr></thead>
  <tbody>
  <?php // Chaque ligne porte son propre form d'edition inline (nom + slug). ?>
<?php foreach ($rows as $c): ?>
    <tr>
      <td><?= (int)$c['id'] ?></td>
      <td>
        <form method="post" style="display:flex;gap:6px;align-items:center">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
          <input type="text" name="nom" value="<?= h($c['nom']) ?>" required style="padding:5px 8px;border:1px solid var(--border);border-radius:5px;font:inherit">
          <input type="text" name="slug" value="<?= h($c['slug']) ?>" style="padding:5px 8px;border:1px solid var(--border);border-radius:5px;font:inherit;width:140px">
          <button class="btn-edit btn-small" type="submit">Enregistrer</button>
        </form>
      </td>
      <td><code><?= h($c['slug']) ?></code></td>
      <td><?= (int)$c['nb'] ?></td>
      <td class="actions">
        <?php // Bouton Suppr desactive cote UI tant que des produits referencent la categorie. ?>
        <form method="post" onsubmit="return confirm('Supprimer la catégorie « <?= h($c['nom']) ?> » ?')">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
          <button class="btn-danger btn-small" type="submit" <?= $c['nb']>0?'disabled title="Catégorie utilisée par des produits."':'' ?>>Suppr</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
<?php require_once __DIR__ . '/_footer.php'; ?>
