<?php
$page_title = 'Utilisateurs - Backoffice Artify';
require_once __DIR__ . '/_header.php';
/** @var PDO $pdo */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    if ($id === current_user_id()) {
        flash_set('error', "Tu ne peux pas modifier ton propre compte d'admin.");
        redirect('users.php');
    }
    if ($action === 'toggle_active') {
        $pdo->prepare("UPDATE utilisateur SET est_actif = 1 - est_actif WHERE id = ?")->execute([$id]);
        flash_set('success', 'Statut activation mis à jour.');
    } elseif ($action === 'set_role') {
        $role = $_POST['role'] ?? 'visiteur';
        if (in_array($role, ['visiteur','artisan','admin'], true)) {
            $pdo->prepare("UPDATE utilisateur SET role = ? WHERE id = ?")->execute([$role, $id]);
            if ($role === 'artisan') {
                $st = $pdo->prepare("SELECT id FROM artisan WHERE utilisateur_id = ?");
                $st->execute([$id]);
                if (!$st->fetchColumn()) {
                    $u = $pdo->prepare("SELECT prenom, nom FROM utilisateur WHERE id = ?");
                    $u->execute([$id]); $u = $u->fetch();
                    $pdo->prepare("INSERT INTO artisan (utilisateur_id, nom_boutique) VALUES (?, ?)")
                        ->execute([$id, ($u['prenom'] ?? '') . ' ' . ($u['nom'] ?? '')]);
                }
            }
            flash_set('success', 'Rôle mis à jour.');
        }
    } elseif ($action === 'delete') {
        // Suppression en cascade manuelle (les ON DELETE CASCADE n'existent
        // pas sur toutes les FK ; on nettoie d'abord les références).
        try {
            $pdo->beginTransaction();
            $pdo->prepare("DELETE FROM avis WHERE utilisateur_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM favori WHERE utilisateur_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM inscription_evenement WHERE utilisateur_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM messagerie WHERE expediteur_id = ? OR destinataire_id = ?")->execute([$id, $id]);
            $pdo->prepare("DELETE FROM recherche_log WHERE utilisateur_id = ?")->execute([$id]);
            // Si artisan : supprimer ses produits + sa boutique d'abord
            $art = $pdo->prepare("SELECT id FROM artisan WHERE utilisateur_id = ?");
            $art->execute([$id]);
            if ($aid = $art->fetchColumn()) {
                $pdo->prepare("DELETE FROM image_produit WHERE produit_id IN (SELECT id FROM produit WHERE artisan_id = ?)")->execute([$aid]);
                $pdo->prepare("DELETE FROM favori WHERE produit_id IN (SELECT id FROM produit WHERE artisan_id = ?)")->execute([$aid]);
                $pdo->prepare("DELETE FROM ligne_commande WHERE produit_id IN (SELECT id FROM produit WHERE artisan_id = ?)")->execute([$aid]);
                $pdo->prepare("DELETE FROM produit WHERE artisan_id = ?")->execute([$aid]);
                $pdo->prepare("DELETE FROM galerie WHERE artisan_id = ?")->execute([$aid]);
                $pdo->prepare("DELETE FROM inscription_evenement WHERE evenement_id IN (SELECT id FROM evenement WHERE artisan_id = ?)")->execute([$aid]);
                $pdo->prepare("DELETE FROM evenement WHERE artisan_id = ?")->execute([$aid]);
                $pdo->prepare("DELETE FROM commande WHERE artisan_id = ?")->execute([$aid]);
                $pdo->prepare("DELETE FROM artisan WHERE id = ?")->execute([$aid]);
            }
            $pdo->prepare("DELETE FROM commande WHERE utilisateur_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM utilisateur WHERE id = ?")->execute([$id]);
            $pdo->commit();
            flash_set('success', 'Utilisateur supprimé.');
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            flash_set('error', 'Suppression impossible : ' . $e->getMessage());
        }
    }
    redirect('users.php' . ($_GET ? '?' . http_build_query($_GET) : ''));
}

// Filtres
$role_f = $_GET['role'] ?? '';
$q      = trim($_GET['q'] ?? '');
$where  = []; $params = [];
if (in_array($role_f, ['visiteur','artisan','admin'], true)) { $where[] = "role = ?"; $params[] = $role_f; }
if ($q !== '') { $where[] = "(email LIKE ? OR nom LIKE ? OR prenom LIKE ?)"; $params[] = "%$q%"; $params[] = "%$q%"; $params[] = "%$q%"; }
$sql = "SELECT id, prenom, nom, email, role, est_actif, ville, created_at FROM utilisateur";
if ($where) $sql .= " WHERE " . implode(' AND ', $where);
$sql .= " ORDER BY created_at DESC";
$st = $pdo->prepare($sql); $st->execute($params);
$users = $st->fetchAll();
?>
<div class="crumb"><a href="index.php">Backoffice</a> &rsaquo; Utilisateurs</div>
<h1>Utilisateurs <span style="color:var(--muted);font-size:18px">(<?= count($users) ?>)</span></h1>

<form class="adm-filters" method="get">
  <div class="fld"><label>Recherche (email, nom, prénom)</label>
    <input type="text" name="q" value="<?= h($q) ?>" placeholder="ex: sophie">
  </div>
  <div class="fld"><label>Rôle</label>
    <select name="role">
      <option value="">- tous -</option>
      <?php foreach (['visiteur','artisan','admin'] as $r): ?>
        <option value="<?= $r ?>" <?= $role_f===$r?'selected':'' ?>><?= $r ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <button class="btn-primary" type="submit">Filtrer</button>
  <a class="btn-ghost" href="users.php">Réinitialiser</a>
</form>

<?php if (!$users): ?>
  <div class="empty-state">Aucun utilisateur ne correspond à ces critères.</div>
<?php else: ?>
<table class="adm">
  <thead><tr>
    <th>#</th><th>Nom</th><th>Email</th><th>Rôle</th><th>Ville</th><th>Actif</th><th>Inscrit le</th><th class="actions">Actions</th>
  </tr></thead>
  <tbody>
  <?php foreach ($users as $u): $self = ((int)$u['id'] === current_user_id()); ?>
    <tr>
      <td><?= (int)$u['id'] ?></td>
      <td><?= h(trim(($u['prenom'] ?? '') . ' ' . ($u['nom'] ?? ''))) ?></td>
      <td><?= h($u['email']) ?></td>
      <td>
        <form method="post" style="margin:0">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="set_role">
          <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
          <select name="role" onchange="this.form.submit()" <?= $self ? 'disabled' : '' ?>>
            <option value="visiteur" <?= $u['role']==='visiteur'?'selected':'' ?>>visiteur</option>
            <option value="artisan"  <?= $u['role']==='artisan'?'selected':''  ?>>artisan</option>
            <option value="admin"    <?= $u['role']==='admin'?'selected':''    ?>>admin</option>
          </select>
        </form>
      </td>
      <td><?= h($u['ville'] ?: '-') ?></td>
      <td><?= $u['est_actif'] ? '<span class="badge ok">oui</span>' : '<span class="badge err">non</span>' ?></td>
      <td><?= h(date('d/m/Y', strtotime($u['created_at']))) ?></td>
      <td class="actions">
        <?php if (!$self): ?>
          <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="toggle_active">
            <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
            <button class="<?= $u['est_actif']?'btn-warn':'btn-success' ?> btn-small" type="submit">
              <?= $u['est_actif'] ? 'Désactiver' : 'Activer' ?>
            </button>
          </form>
          <form method="post" onsubmit="return confirm('Supprimer définitivement ce compte ?')">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
            <button class="btn-danger btn-small" type="submit">Supprimer</button>
          </form>
        <?php else: ?>
          <span class="badge muted">moi</span>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
<?php require_once __DIR__ . '/_footer.php'; ?>
