<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();
$page_title = 'Mon panier — Artify';

$panier = $_SESSION['panier'] ?? [];
$total  = array_sum(array_map(fn($i) => $i['prix'] * $i['quantite'], $panier));

include __DIR__ . '/includes/header.php';
?>
<div class="crumb"><a href="index.php">Accueil</a> &rsaquo; Mon panier</div>
<h1>Mon panier</h1>

<?php if (!$panier): ?>
  <div class="empty">Votre panier est vide. <a href="creations.php">Découvrir les créations</a>.</div>
<?php else: ?>

<div style="max-width:680px">
  <div class="card" style="padding:0;overflow:hidden">
    <table style="width:100%;border-collapse:collapse">
      <thead>
        <tr style="background:var(--warm)">
          <th style="padding:12px 16px;text-align:left;font-size:12px;text-transform:uppercase;letter-spacing:.04em;color:var(--muted)">Produit</th>
          <th style="padding:12px 16px;text-align:center;font-size:12px;text-transform:uppercase;letter-spacing:.04em;color:var(--muted)">Qté</th>
          <th style="padding:12px 16px;text-align:right;font-size:12px;text-transform:uppercase;letter-spacing:.04em;color:var(--muted)">Prix unit.</th>
          <th style="padding:12px 16px;text-align:right;font-size:12px;text-transform:uppercase;letter-spacing:.04em;color:var(--muted)">Sous-total</th>
          <th style="padding:12px 16px"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($panier as $pid => $item): ?>
          <tr style="border-top:1px solid var(--border)">
            <td style="padding:14px 16px">
              <a href="produit.php?id=<?= (int)$pid ?>" style="font-weight:600;color:var(--dark)"><?= h($item['nom']) ?></a>
              <div style="font-size:12px;color:var(--muted);margin-top:2px"><?= number_format($item['prix'],2,',',' ') ?> € / unité</div>
            </td>
            <td style="padding:14px 16px;text-align:center">
              <form method="post" action="panier_modifier.php" style="display:inline-flex;align-items:center;gap:6px">
                <?= csrf_field() ?>
                <input type="hidden" name="produit_id" value="<?= (int)$pid ?>">
                <button type="submit" name="action" value="supprimer"
                        onclick="this.form.querySelector('[name=quantite]').value=0"
                        style="background:none;border:none;font-size:18px;cursor:pointer;color:var(--muted);line-height:1" title="−">−</button>
                <input type="number" name="quantite" value="<?= (int)$item['quantite'] ?>"
                       min="1" max="<?= (int)$item['stock'] ?>"
                       style="width:52px;text-align:center;padding:4px 6px;border:1.5px solid var(--border);border-radius:6px;font:inherit"
                       onchange="this.form.submit()">
                <button type="submit" style="background:none;border:none;font-size:18px;cursor:pointer;color:var(--muted);line-height:1" title="+">+</button>
              </form>
            </td>
            <td style="padding:14px 16px;text-align:right;white-space:nowrap"><?= number_format($item['prix'],2,',',' ') ?> €</td>
            <td style="padding:14px 16px;text-align:right;white-space:nowrap;font-weight:700;color:var(--ocre)"><?= number_format($item['prix'] * $item['quantite'],2,',',' ') ?> €</td>
            <td style="padding:14px 12px;text-align:right">
              <form method="post" action="panier_modifier.php">
                <?= csrf_field() ?>
                <input type="hidden" name="produit_id" value="<?= (int)$pid ?>">
                <input type="hidden" name="action" value="supprimer">
                <button type="submit" style="background:none;border:none;cursor:pointer;font-size:18px;color:var(--muted)" title="Supprimer">✕</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr style="border-top:2px solid var(--border);background:var(--ocre-pale)">
          <td colspan="3" style="padding:14px 16px;font-weight:700;text-align:right;font-size:15px">Total</td>
          <td style="padding:14px 16px;text-align:right;font-weight:700;font-size:20px;color:var(--ocre)"><?= number_format($total,2,',',' ') ?> €</td>
          <td></td>
        </tr>
      </tfoot>
    </table>
  </div>

  <div style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;flex-wrap:wrap;gap:10px">
    <a class="btn-ghost" href="creations.php">← Continuer mes achats</a>
    <form method="post" action="panier_commander.php">
      <?= csrf_field() ?>
      <button class="btn-primary" type="submit" style="font-size:15px;padding:10px 24px">
        Commander (<?= count($panier) ?> article<?= count($panier)>1?'s':'' ?> · <?= number_format($total,2,',',' ') ?> €)
      </button>
    </form>
  </div>
</div>

<?php endif; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
