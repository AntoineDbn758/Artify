<?php

/**
 * Callback de retour Stripe en cas de paiement reussi. On verifie cote
 * serveur que la session Stripe a bien payment_status=paid (on ne fait jamais
 * confiance a une simple redirection URL). Si OK : passe la commande en
 * 'confirmee' et decremente le stock.
 */

/**
 * commande_success.php - Callback de retour Stripe après paiement réussi.
 * Vérifie la session côté Stripe, met la commande en 'confirmee'.
 */
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/stripe.php';
require_login();
$page_title = 'Commande confirmée - Artify';

$commande_id = (int)($_GET['commande'] ?? 0);
$session_id  = trim($_GET['session'] ?? '');

if (!$commande_id || !$session_id) { http_response_code(400); die('Paramètres manquants.'); }

// Filtrage par utilisateur_id pour empecher qu'un client
// puisse consulter la commande d'un autre via l'URL.
$st = $pdo->prepare(
    "SELECT c.id, c.statut, c.montant_total, c.utilisateur_id, c.message_personnalisation,
            p.nom AS produit, lc.quantite, p.id AS produit_id
       FROM commande c
       JOIN ligne_commande lc ON lc.commande_id = c.id
       JOIN produit p ON p.id = lc.produit_id
      WHERE c.id = ? AND c.utilisateur_id = ?"
);
$st->execute([$commande_id, current_user_id()]);
$c = $st->fetch();
if (!$c) { http_response_code(404); die('Commande introuvable.'); }

// Source de verite cote Stripe : on ne se fie pas a l'URL de retour
// car elle peut etre forgee, on interroge l'API pour le vrai statut.
$verdict = 'inconnu';
$amount_paid = null;
if (stripe_configured() && $session_id) {
    $session = stripe_get("checkout/sessions/$session_id");
    if (($session['_http_code'] ?? 0) === 200) {
        $verdict = $session['payment_status'] ?? 'inconnu';
        $amount_paid = isset($session['amount_total']) ? ((int)$session['amount_total']) / 100 : null;
    }
}

// Garde 'en_attente' pour ne pas confirmer deux fois et redecrementer
// le stock si l'utilisateur rafraichit la page success.
if ($verdict === 'paid' && $c['statut'] === 'en_attente') {
    $pdo->beginTransaction();
    try {
        $pdo->prepare("UPDATE commande SET statut='confirmee' WHERE id=?")
            ->execute([$commande_id]);
        // GREATEST(... , 0) pour ne pas tomber en stock negatif en cas de race.
        $pdo->prepare("UPDATE produit SET stock = GREATEST(stock - ?, 0) WHERE id=?")
            ->execute([(int)$c['quantite'], (int)$c['produit_id']]);
        $pdo->commit();
        $c['statut'] = 'confirmee';
    } catch (\Throwable $e) {
        $pdo->rollBack();
    }
}

include __DIR__ . '/includes/header.php';
?>
<div class="crumb"><a href="index.php">Accueil</a> &rsaquo; Commande #<?= (int)$commande_id ?></div>

<?php if ($verdict === 'paid'): ?>
  <h1 style="color:var(--ocre)">Paiement réussi</h1>
  <div class="aretenir" style="margin:18px 0">
    <strong>Merci pour votre commande !</strong><br>
    Numéro : <strong>#<?= (int)$commande_id ?></strong> ·
    Montant payé : <strong><?= number_format((float)($amount_paid ?? $c['montant_total']), 2, ',', ' ') ?> €</strong> ·
    Statut : <strong><?= h($c['statut']) ?></strong>
  </div>
  <p>Récapitulatif :</p>
  <ul>
    <li><?= h($c['quantite']) ?> × <strong><?= h($c['produit']) ?></strong></li>
  </ul>
  <p>Vous recevrez prochainement un message de l'artisan concernant la livraison.</p>
  <div style="margin-top:18px;display:flex;gap:8px;flex-wrap:wrap">
    <a class="btn-primary" href="mes_commandes.php">Mes commandes</a>
    <a class="btn-ghost" href="creations.php">Continuer le shopping</a>
  </div>
<?php elseif ($verdict === 'unpaid'): ?>
  <h1>Paiement en attente</h1>
  <p>Stripe indique que le paiement n'est pas encore validé. Si vous venez de payer,
     patientez quelques secondes et rafraîchissez cette page.</p>
  <p><a class="btn-primary" href="commande_success.php?commande=<?= (int)$commande_id ?>&session=<?= h($session_id) ?>">Rafraîchir</a>
     <a class="btn-ghost" href="produit.php?id=<?= (int)$c['produit_id'] ?>">Retour au produit</a></p>
<?php else: ?>
  <h1>Confirmation impossible</h1>
  <p>Nous n'avons pas pu confirmer le paiement de cette commande.
     Statut Stripe : <strong><?= h($verdict) ?></strong>.</p>
  <p>Si le paiement a bien été débité, contactez le support.</p>
  <p><a class="btn-ghost" href="mes_commandes.php">Voir mes commandes</a></p>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
