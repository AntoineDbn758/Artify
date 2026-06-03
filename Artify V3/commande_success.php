<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();
$page_title = 'Commande confirmée — Artify';

$commande_id = (int)($_GET['commande'] ?? 0);
if (!$commande_id) { http_response_code(400); die('Paramètre manquant.'); }

$st = $pdo->prepare(
    "SELECT c.id, c.statut, c.montant_total, c.created_at,
            p.nom AS produit, lc.quantite, p.id AS produit_id, a.nom_boutique
       FROM commande c
       JOIN ligne_commande lc ON lc.commande_id = c.id
       JOIN produit p ON p.id = lc.produit_id
       JOIN artisan a ON a.id = c.artisan_id
      WHERE c.id = ? AND c.utilisateur_id = ?"
);
$st->execute([$commande_id, current_user_id()]);
$c = $st->fetch();
if (!$c) { http_response_code(404); die('Commande introuvable.'); }

include __DIR__ . '/includes/header.php';
?>
<div class="crumb"><a href="index.php">Accueil</a> &rsaquo; Commande #<?= $commande_id ?></div>

<div style="max-width:560px;margin:32px auto;text-align:center">
  <div style="font-size:48px;margin-bottom:16px">🎉</div>
  <h1 style="color:var(--ocre);margin-bottom:8px">Merci pour votre achat&nbsp;!</h1>
  <p style="font-size:15px;margin-bottom:24px">Votre commande a bien été enregistrée.</p>

  <div class="card" style="text-align:left;margin-bottom:20px">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
      <span style="font-size:13px;color:var(--muted)">Commande</span>
      <strong>#<?= $commande_id ?></strong>
    </div>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
      <span style="color:var(--mid)"><?= (int)$c['quantite'] ?> × <?= h($c['produit']) ?></span>
      <span class="price" style="font-size:16px"><?= number_format((float)$c['montant_total'], 2, ',', ' ') ?> €</span>
    </div>
    <div style="font-size:13px;color:var(--muted)">Vendu par <?= h($c['nom_boutique']) ?></div>
    <div style="margin-top:10px;font-size:13px">
      Statut : <span class="badge ok">Confirmée</span>
    </div>
  </div>

  <p style="font-size:13px;color:var(--muted);margin-bottom:20px">
    L'artisan vous contactera prochainement pour la livraison.
  </p>

  <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap">
    <a class="btn-primary" href="mes_commandes.php">Mes commandes</a>
    <a class="btn-ghost" href="creations.php">Continuer mes achats</a>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
