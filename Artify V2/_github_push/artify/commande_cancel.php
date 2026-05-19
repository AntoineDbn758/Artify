<?php

/**
 * Callback de retour Stripe si l'utilisateur annule. La commande est marquee
 * 'annulee'. Aucun montant n'a ete debite (Stripe ne capture qu'apres
 * confirmation cote acheteur).
 */

require_once __DIR__ . '/includes/bootstrap.php';
// Acces reserve aux utilisateurs connectes (on touche au statut d'une commande).
require_login();
$page_title = 'Paiement annulé - Artify';

// Recupere l'id de commande passe par Stripe dans le cancel_url.
$commande_id = (int)($_GET['commande'] ?? 0);

if ($commande_id) {
    // Triple filtre id + utilisateur + statut : on annule uniquement
    // sa propre commande encore en_attente, jamais une confirmee.
    $pdo->prepare(
        "UPDATE commande SET statut='annulee'
          WHERE id = ? AND utilisateur_id = ? AND statut='en_attente'"
    )->execute([$commande_id, current_user_id()]);
}

// Pre-recupere le produit pour afficher un bouton de retour
// direct vers la fiche article et faciliter une nouvelle tentative.
$produit_id = 0;
if ($commande_id) {
    // LIMIT 1 : panier mono-article aujourd'hui, on prend le premier au cas ou.
    $st = $pdo->prepare("SELECT produit_id FROM ligne_commande WHERE commande_id = ? LIMIT 1");
    $st->execute([$commande_id]);
    $produit_id = (int)($st->fetchColumn() ?: 0);
}

include __DIR__ . '/includes/header.php';
?>
<?php /* Fil d'Ariane minimal pour situer la page. */ ?>
<div class="crumb"><a href="index.php">Accueil</a> &rsaquo; Paiement annulé</div>
<h1>Paiement annulé</h1>
<p>Votre commande a été annulée. Aucun montant n'a été débité.</p>
<div style="margin-top:18px;display:flex;gap:8px;flex-wrap:wrap">
  <?php /* Bouton de retour direct au produit si on a pu le retrouver, sinon on saute. */ ?>
  <?php if ($produit_id): ?>
    <a class="btn-primary" href="produit.php?id=<?= (int)$produit_id ?>">Revenir au produit</a>
  <?php endif; ?>
  <a class="btn-ghost" href="creations.php">Voir d'autres créations</a>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
