<?php

/**
 * Callback de retour Stripe si l'utilisateur annule. La commande est marquee
 * 'annulee'. Aucun montant n'a ete debite (Stripe ne capture qu'apres
 * confirmation cote acheteur).
 */

require_once __DIR__ . '/includes/bootstrap.php';
require_login();
$page_title = 'Paiement annulé - Artify';

$commande_id = (int)($_GET['commande'] ?? 0);

if ($commande_id) {
    // Marque la commande comme annulée (sans toucher au stock)
    $pdo->prepare(
        "UPDATE commande SET statut='annulee'
          WHERE id = ? AND utilisateur_id = ? AND statut='en_attente'"
    )->execute([$commande_id, current_user_id()]);
}

// Récupère l'éventuel produit pour proposer un retour
$produit_id = 0;
if ($commande_id) {
    $st = $pdo->prepare("SELECT produit_id FROM ligne_commande WHERE commande_id = ? LIMIT 1");
    $st->execute([$commande_id]);
    $produit_id = (int)($st->fetchColumn() ?: 0);
}

include __DIR__ . '/includes/header.php';
?>
<div class="crumb"><a href="index.php">Accueil</a> &rsaquo; Paiement annulé</div>
<h1>Paiement annulé</h1>
<p>Votre commande a été annulée. Aucun montant n'a été débité.</p>
<div style="margin-top:18px;display:flex;gap:8px;flex-wrap:wrap">
  <?php if ($produit_id): ?>
    <a class="btn-primary" href="produit.php?id=<?= (int)$produit_id ?>">Revenir au produit</a>
  <?php endif; ?>
  <a class="btn-ghost" href="creations.php">Voir d'autres créations</a>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
