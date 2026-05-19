<?php

/**
 * Historique des commandes de l'utilisateur connecte. Triees de la plus
 * recente a la plus ancienne. Statut affiche en couleur (en attente,
 * confirmee, en fabrication, expediee, livree, annulee).
 */

require_once __DIR__ . '/includes/bootstrap.php';
// Page reservee aux utilisateurs connectes : on ne voit que ses propres commandes.
require_login();
$page_title = 'Mes commandes - Artify';

// GROUP_CONCAT permet d'afficher tous les produits d'une commande sur une seule ligne du tableau sans faire un sous-SELECT par ligne.
// LEFT JOIN sur ligne_commande / produit pour ne pas perdre une commande qui n'aurait plus de ligne suite a un delete cascade.
// WHERE utilisateur_id : filtre fort qui garantit qu'un user ne peut pas voir les commandes d'un autre.
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

// Mapping statut vers classe CSS du badge, plus simple a faire en PHP qu'avec une cascade de if dans la vue.
// Codes ENUM de la base => classe d'affichage : muted (neutre), ok (vert), err (rouge).
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
          <?php // GROUP_CONCAT renvoie null si pas de ligne, on bascule sur "-" pour rester propre. ?>
          <td><?= h($c['produits'] ?? '-') ?></td>
          <td><?= number_format((float)$c['montant_total'], 2, ',', ' ') ?> &euro;</td>
          <?php // Fallback classe "badge" si on ajoute un nouveau statut sans mapping, evite un span sans style. ?>
          <td><span class="<?= $badge[$c['statut']] ?? 'badge' ?>"><?= h($c['statut']) ?></span></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
