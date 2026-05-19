<?php

/**
 * Vue globale des commandes. Permet de faire passer une commande d'un statut
 * a l'autre dans le workflow : en_attente -> confirmee -> en_fabrication ->
 * expediee -> livree (ou annulee a tout moment). Tres utile pour le suivi
 * cote artisan ou support.
 */

$page_title = 'Commandes - Backoffice Artify';
require_once __DIR__ . '/_header.php';
/** @var PDO $pdo */

$STATUTS = ['en_attente','confirmee','en_fabrication','expediee','livree','annulee'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if ($action === 'set_statut') {
        $st = $_POST['statut'] ?? '';
        if (in_array($st, $STATUTS, true)) {
            $pdo->prepare("UPDATE commande SET statut = ? WHERE id = ?")->execute([$st, $id]);
            flash_set('success', "Statut → $st.");
        }
    } elseif ($action === 'delete') {
        try {
            $pdo->prepare("DELETE FROM ligne_commande WHERE commande_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM commande WHERE id = ?")->execute([$id]);
            flash_set('success', 'Commande supprimée.');
        } catch (\Throwable $e) {
            flash_set('error', 'Erreur suppression : ' . $e->getMessage());
        }
    }
    redirect('commandes.php' . ($_GET ? '?' . http_build_query($_GET) : ''));
}

$filter = $_GET['statut'] ?? '';
$view = (int)($_GET['view'] ?? 0);

if ($view > 0) {
    $cmd = $pdo->prepare(
      "SELECT c.*, u.prenom, u.nom, u.email, a.nom_boutique
         FROM commande c
         JOIN utilisateur u ON u.id = c.utilisateur_id
         JOIN artisan a ON a.id = c.artisan_id
        WHERE c.id = ?"
    );
    $cmd->execute([$view]);
    $cmd = $cmd->fetch();
    if ($cmd) {
        $lignes = $pdo->prepare(
          "SELECT lc.*, p.nom AS produit_nom
             FROM ligne_commande lc
             JOIN produit p ON p.id = lc.produit_id
            WHERE lc.commande_id = ?"
        );
        $lignes->execute([$view]);
        $lignes = $lignes->fetchAll();
    }
}

$where = []; $params = [];
if (in_array($filter, $STATUTS, true)) { $where[] = "c.statut = ?"; $params[] = $filter; }
$sql = "SELECT c.*, u.prenom, u.nom, u.email, a.nom_boutique
          FROM commande c
          JOIN utilisateur u ON u.id = c.utilisateur_id
          JOIN artisan a ON a.id = c.artisan_id";
if ($where) $sql .= " WHERE " . implode(' AND ', $where);
$sql .= " ORDER BY c.created_at DESC";
$st = $pdo->prepare($sql); $st->execute($params);
$rows = $st->fetchAll();
?>
<div class="crumb"><a href="index.php">Backoffice</a> &rsaquo; Commandes</div>

<?php if ($view > 0 && isset($cmd) && $cmd): ?>
  <h1>Commande #<?= (int)$cmd['id'] ?></h1>
  <p><a class="btn-ghost btn-small" href="commandes.php">&lsaquo; Retour</a></p>
  <div class="admin-card">
    <p><strong>Client :</strong> <?= h(trim(($cmd['prenom'] ?? '') . ' ' . ($cmd['nom'] ?? ''))) ?> &lt;<?= h($cmd['email']) ?>&gt;</p>
    <p><strong>Artisan :</strong> <?= h($cmd['nom_boutique']) ?></p>
    <p><strong>Statut :</strong> <span class="badge"><?= h($cmd['statut']) ?></span></p>
    <p><strong>Montant total :</strong> <?= number_format((float)$cmd['montant_total'], 2, ',', ' ') ?> €</p>
    <p><strong>Livraison :</strong> <?= h($cmd['adresse_livraison'] ?: '-') ?>
      <?php if ($cmd['ville_livraison']): ?>- <?= h($cmd['ville_livraison']) ?> (<?= h($cmd['code_postal']) ?>)<?php endif; ?></p>
    <p><strong>Créée le :</strong> <?= h(date('d/m/Y H:i', strtotime($cmd['created_at']))) ?></p>
    <?php if ($cmd['message_personnalisation']): ?>
      <p><strong>Message :</strong><br><?= nl2br(h($cmd['message_personnalisation'])) ?></p>
    <?php endif; ?>
  </div>
  <?php if (!empty($lignes)): ?>
    <h3>Lignes de commande</h3>
    <table class="adm">
      <thead><tr><th>Produit</th><th>Qté</th><th>P.U.</th><th>Total</th></tr></thead>
      <tbody>
      <?php foreach ($lignes as $l): ?>
        <tr>
          <td><?= h($l['produit_nom']) ?>
            <?php if ($l['details_personnalisation']): ?>
              <br><span style="font-size:12px;color:var(--muted)"><?= h($l['details_personnalisation']) ?></span>
            <?php endif; ?>
          </td>
          <td><?= (int)$l['quantite'] ?></td>
          <td><?= number_format((float)$l['prix_unitaire'], 2, ',', ' ') ?> €</td>
          <td><?= number_format((float)$l['prix_unitaire'] * (int)$l['quantite'], 2, ',', ' ') ?> €</td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
<?php else: ?>
  <h1>Commandes <span style="color:var(--muted);font-size:18px">(<?= count($rows) ?>)</span></h1>

  <form class="adm-filters" method="get">
    <div class="fld"><label>Statut</label>
      <select name="statut" onchange="this.form.submit()">
        <option value="">- tous -</option>
        <?php foreach ($STATUTS as $s): ?>
          <option value="<?= $s ?>" <?= $filter===$s?'selected':'' ?>><?= $s ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <a class="btn-ghost" href="commandes.php">Réinitialiser</a>
  </form>

  <?php if (!$rows): ?>
    <div class="empty-state">Aucune commande à ce jour.</div>
  <?php else: ?>
  <table class="adm">
    <thead><tr>
      <th>#</th><th>Date</th><th>Client</th><th>Artisan</th>
      <th>Montant</th><th>Statut</th><th class="actions">Actions</th>
    </tr></thead>
    <tbody>
    <?php foreach ($rows as $c): ?>
      <tr>
        <td><?= (int)$c['id'] ?></td>
        <td><?= h(date('d/m/Y H:i', strtotime($c['created_at']))) ?></td>
        <td><?= h(trim(($c['prenom'] ?? '') . ' ' . ($c['nom'] ?? ''))) ?><br>
            <span style="font-size:12px;color:var(--muted)"><?= h($c['email']) ?></span></td>
        <td><?= h($c['nom_boutique']) ?></td>
        <td><?= number_format((float)$c['montant_total'], 2, ',', ' ') ?> €</td>
        <td>
          <form method="post" style="margin:0">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="set_statut">
            <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
            <select name="statut" onchange="this.form.submit()" style="padding:4px 8px;border:1px solid var(--border);border-radius:5px;font:inherit">
              <?php foreach ($STATUTS as $s): ?>
                <option value="<?= $s ?>" <?= $c['statut']===$s?'selected':'' ?>><?= $s ?></option>
              <?php endforeach; ?>
            </select>
          </form>
        </td>
        <td class="actions">
          <a class="btn-ghost btn-small" href="commandes.php?view=<?= (int)$c['id'] ?>">Détails</a>
          <form method="post" onsubmit="return confirm('Supprimer cette commande ?')">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
            <button class="btn-danger btn-small" type="submit">Suppr</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
<?php endif; ?>
<?php require_once __DIR__ . '/_footer.php'; ?>
