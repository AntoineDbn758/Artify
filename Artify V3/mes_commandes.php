<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();
$page_title = 'Mes commandes — Artify';

$st = $pdo->prepare(
    "SELECT c.id, c.montant_total, c.statut, c.created_at, a.nom_boutique,
            lc.produit_id, p.nom AS produit_nom,
            lc.quantite, lc.prix_unitaire
       FROM commande c
       JOIN artisan a ON a.id = c.artisan_id
       JOIN ligne_commande lc ON lc.commande_id = c.id
       JOIN produit p ON p.id = lc.produit_id
      WHERE c.utilisateur_id = ?
      ORDER BY c.created_at DESC"
);
$st->execute([current_user_id()]);
$rows = $st->fetchAll();

// Regroupe par commande + récupère les notes déjà données
$commandes = [];
$produit_ids = array_column($rows, 'produit_id');
$mes_notes = [];
if ($produit_ids) {
    $in = implode(',', array_fill(0, count($produit_ids), '?'));
    $params = array_merge([current_user_id()], $produit_ids);
    $st2 = $pdo->prepare("SELECT produit_id, note FROM avis_produit WHERE utilisateur_id = ? AND produit_id IN ($in)");
    $st2->execute($params);
    foreach ($st2->fetchAll() as $r) $mes_notes[$r['produit_id']] = (int)$r['note'];
}
foreach ($rows as $r) {
    $cid = $r['id'];
    if (!isset($commandes[$cid])) {
        $commandes[$cid] = ['id'=>$r['id'],'montant_total'=>$r['montant_total'],'statut'=>$r['statut'],'created_at'=>$r['created_at'],'nom_boutique'=>$r['nom_boutique'],'lignes'=>[]];
    }
    $commandes[$cid]['lignes'][] = ['produit_id'=>$r['produit_id'],'nom'=>$r['produit_nom'],'qte'=>$r['quantite'],'pu'=>$r['prix_unitaire']];
}

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
  <div style="display:flex;flex-direction:column;gap:16px">
    <?php foreach ($commandes as $c): ?>
      <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:baseline;flex-wrap:wrap;gap:8px;margin-bottom:10px">
          <span style="font-weight:600">Commande #<?= (int)$c['id'] ?> · <?= h($c['nom_boutique']) ?></span>
          <span style="font-size:13px;color:var(--muted)"><?= h(date('d/m/Y H:i', strtotime($c['created_at']))) ?></span>
        </div>
        <div style="display:flex;flex-direction:column;gap:8px">
          <?php foreach ($c['lignes'] as $l): ?>
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:6px;padding:8px 0;border-top:1px solid var(--border)">
              <div>
                <a href="produit.php?id=<?= (int)$l['produit_id'] ?>" style="font-weight:500;color:var(--dark)"><?= h($l['nom']) ?></a>
                <span style="font-size:13px;color:var(--muted)"> · <?= (int)$l['qte'] ?> × <?= number_format((float)$l['pu'],2,',',' ') ?> €</span>
              </div>
              <?php if ($c['statut'] === 'confirmee'): ?>
                <?php $note_deja = $mes_notes[$l['produit_id']] ?? null; ?>
                <?php if ($note_deja): ?>
                  <span class="stars-display" style="font-size:15px" title="Votre note">
                    <?php for ($i=1;$i<=5;$i++) echo $i<=$note_deja?'★':'<span class="empty">★</span>'; ?>
                  </span>
                <?php else: ?>
                  <a class="btn-ghost btn-small" href="produit.php?id=<?= (int)$l['produit_id'] ?>#avis">
                    ★ Noter
                  </a>
                <?php endif; ?>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:10px">
          <span class="<?= $badge[$c['statut']] ?? 'badge' ?>"><?= h($c['statut']) ?></span>
          <strong><?= number_format((float)$c['montant_total'],2,',',' ') ?> €</strong>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
