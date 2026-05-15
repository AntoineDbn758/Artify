<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();
$page_title = 'Mes commandes - Artify';

$st = $pdo->prepare(
    "SELECT c.id, c.montant_total, c.statut, c.created_at, a.nom_boutique,
            COUNT(lc.id) AS nb_lignes,
            GROUP_CONCAT(p.nom SEPARATOR ', ') AS produits
       FROM commande c
       JOIN artisan a ON a.id = c.artisan_id
       LEFT JOIN ligne_commande lc ON lc.commande_id = c.id
       LEFT JOIN produit p ON p.id = lc.produit_id
      WHERE c.utilisateur_id = ?
   GROUP BY c.id
   ORDER BY c.created_at DESC"
);
$st->execute([current_user_id()]);
$commandes = $st->fetchAll();

$badge = [
    'en_attente'      => 'badge muted',
    'confirmee'       => 'badge ok',
    'en_fabrication'  => 'badge ok',
    'expediee'        => 'badge ok',
    'livree'          => 'badge ok',
    'annulee'         => 'badge err',
];

include __DIR__ . '/includes/header.php';
?>
<div class="crumb"><a href="index.php">Accueil</a> &rsaquo; <a href="profile.php">Mon profil</a> &rsaquo; Mes commandes</div>
<h1>Mes commandes</h1>

<?php if (!$commandes): ?>
  <div class="empty">Aucune commande pour le moment. <a href="creations.php">Découvrir les créations</a>.</div>
<?php else: ?>
  <table class="tbl">
    <thead>
      <tr>
        <th>#</th><th>Date</th><th>Artisan</th><th>Produits</th>
        <th>Montant</th><th>Statut</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($commandes as $c): ?>
        <tr>
          <td>#<?= (int)$c['id'] ?></td>
          <td><?= h(date('d/m/Y H:i', strtotime($c['created_at']))) ?></td>
          <td><?= h($c['nom_boutique']) ?></td>
          <td><?= h($c['produits'] ?? '-') ?></td>
          <td><?= number_format((float)$c['montant_total'], 2, ',', ' ') ?> &euro;</td>
          <td><span class="<?= $badge[$c['statut']] ?? 'badge' ?>"><?= h($c['statut']) ?></span></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
