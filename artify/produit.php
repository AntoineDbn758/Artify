<?php
require_once __DIR__ . '/includes/bootstrap.php';
$id = (int)($_GET['id'] ?? 0);

$st = $pdo->prepare(
  "SELECT p.*, c.nom AS categorie, a.nom_boutique, a.id AS aid,
          ip.url AS image_url
     FROM produit p
     JOIN categorie c ON c.id = p.categorie_id
     JOIN artisan a   ON a.id = p.artisan_id
     LEFT JOIN image_produit ip ON ip.produit_id = p.id AND ip.est_principale = 1
    WHERE p.id = ? AND p.est_publie = 1");
$st->execute([$id]);
$p = $st->fetch();

if (!$p) {
    http_response_code(404);
    $page_title = 'Produit introuvable';
    include __DIR__ . '/includes/header.php';
    echo '<div class="empty">Ce produit n\'existe pas ou n\'est plus publié.</div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}
$page_title = $p['nom'] . ' — Artify';

include __DIR__ . '/includes/header.php';
?>
<div class="crumb">
  <a href="index.php">Accueil</a> &rsaquo;
  <a href="creations.php">Créations</a> &rsaquo;
  <?= h($p['nom']) ?>
</div>

<div class="detail">
  <img class="visual" src="<?= h($p['image_url'] ?: 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=800&h=600&fit=crop&q=80') ?>" alt="<?= h($p['nom']) ?>">
  <div>
    <h1><?= h($p['nom']) ?></h1>
    <span class="tag"><?= h($p['categorie']) ?></span>
    <?php if ($p['est_personnalisable']): ?>
      <span class="tag" style="background:#e6f4eb;color:#1e5a35">Personnalisable</span>
    <?php endif; ?>
    <p style="font-size:17px;margin-top:14px">
      Par <a href="artisan.php?id=<?= (int)$p['aid'] ?>"><?= h($p['nom_boutique']) ?></a>
    </p>
    <div class="price" style="font-size:30px;margin:14px 0">
      <?= number_format((float)$p['prix'], 2, ',', ' ') ?> &euro;
    </div>
    <dl>
      <?php if (!empty($p['description'])): ?>
        <dt>Description</dt><dd><?= nl2br(h($p['description'])) ?></dd>
      <?php endif; ?>
      <?php if (!empty($p['materiaux'])): ?>
        <dt>Matériaux</dt><dd><?= h($p['materiaux']) ?></dd>
      <?php endif; ?>
      <?php if (!empty($p['dimensions'])): ?>
        <dt>Dimensions</dt><dd><?= h($p['dimensions']) ?></dd>
      <?php endif; ?>
      <?php if ($p['delai_fabrication_jours']): ?>
        <dt>Délai</dt><dd><?= (int)$p['delai_fabrication_jours'] ?> jours</dd>
      <?php endif; ?>
      <dt>Stock</dt><dd><?= (int)$p['stock'] > 0 ? (int)$p['stock'] . ' disponible(s)' : 'Sur commande' ?></dd>
    </dl>
    <?php if ((int)$p['stock'] > 0): ?>
      <?php if (current_user_id() !== null): ?>
        <form method="post" action="commande_new.php"
              style="margin-top:18px;display:flex;gap:10px;align-items:center;flex-wrap:wrap">
          <?= csrf_field() ?>
          <input type="hidden" name="produit_id" value="<?= (int)$p['id'] ?>">
          <label style="display:flex;align-items:center;gap:6px">
            Quantité
            <input type="number" name="quantite" value="1" min="1" max="<?= (int)$p['stock'] ?>"
                   style="width:70px;padding:8px;border:1.5px solid var(--border);border-radius:8px">
          </label>
          <button type="submit" class="btn-primary">Commander</button>
        </form>
        <p style="margin-top:8px;font-size:12px;color:var(--muted)">
          Paiement sécurisé via Stripe. Aucune donnée bancaire n'est stockée par Artify.
        </p>
      <?php else: ?>
        <div style="margin-top:18px">
          <a class="btn-primary"
             href="login_form.php?next=<?= h('produit.php?id=' . (int)$p['id']) ?>">Se connecter pour commander</a>
        </div>
      <?php endif; ?>
    <?php else: ?>
      <p style="margin-top:18px;font-size:13px;color:var(--muted)">
        Actuellement en rupture de stock. Contactez l'artisan via sa fiche pour une commande personnalisée.
      </p>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
