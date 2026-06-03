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

// Note moyenne du produit
$note_row = $pdo->prepare(
    "SELECT ROUND(AVG(note),1) AS moy, COUNT(*) AS nb FROM avis_produit WHERE produit_id = ?"
);
$note_row->execute([$id]);
$note_row = $note_row->fetch();
$note_moy = (float)($note_row['moy'] ?? 0);
$note_nb  = (int)($note_row['nb'] ?? 0);

// Avis existants
$avis_list = $pdo->prepare(
    "SELECT ap.note, ap.commentaire, ap.created_at, u.prenom, u.nom
       FROM avis_produit ap JOIN utilisateur u ON u.id = ap.utilisateur_id
      WHERE ap.produit_id = ? ORDER BY ap.created_at DESC"
);
$avis_list->execute([$id]);
$avis_list = $avis_list->fetchAll();

// Est-ce que l'utilisateur connecté a commandé ce produit et peut noter ?
$user_commande_id = null;
$user_avis = null;
if (is_logged()) {
    $st2 = $pdo->prepare(
        "SELECT c.id FROM commande c
           JOIN ligne_commande lc ON lc.commande_id = c.id
          WHERE c.utilisateur_id = ? AND lc.produit_id = ? AND c.statut = 'confirmee'
          LIMIT 1"
    );
    $st2->execute([current_user_id(), $id]);
    $user_commande_id = $st2->fetchColumn() ?: null;

    if ($user_commande_id) {
        $st3 = $pdo->prepare("SELECT * FROM avis_produit WHERE utilisateur_id = ? AND produit_id = ?");
        $st3->execute([current_user_id(), $id]);
        $user_avis = $st3->fetch() ?: null;
    }
}

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
    <p style="font-size:15px;margin-top:12px">
      Par <a href="artisan.php?id=<?= (int)$p['aid'] ?>"><?= h($p['nom_boutique']) ?></a>
    </p>

    <?php if ($note_nb > 0): ?>
      <div style="display:flex;align-items:center;gap:8px;margin:8px 0">
        <span class="stars-display">
          <?php for ($i=1;$i<=5;$i++): ?>
            <?= $i <= round($note_moy) ? '★' : '<span class="star-empty">★</span>' ?>
          <?php endfor; ?>
        </span>
        <span style="font-size:14px;color:var(--muted)"><?= number_format($note_moy,1) ?>/5 (<?= $note_nb ?> avis)</span>
      </div>
    <?php endif; ?>

    <div class="price" style="font-size:28px;margin:12px 0">
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
      <?php if (is_logged()): ?>
        <form method="post" action="panier_ajouter.php"
              style="margin-top:16px;display:flex;gap:10px;align-items:center;flex-wrap:wrap">
          <?= csrf_field() ?>
          <input type="hidden" name="produit_id" value="<?= (int)$p['id'] ?>">
          <label style="display:flex;align-items:center;gap:6px;font-size:14px">
            Qté
            <input type="number" name="quantite" value="1" min="1" max="<?= (int)$p['stock'] ?>"
                   style="width:60px;padding:5px 8px;border:1.5px solid var(--border);border-radius:8px;font:inherit">
          </label>
          <button type="submit" class="btn-primary">🛒 Ajouter au panier</button>
          <a class="btn-ghost" href="panier.php">Voir le panier</a>
        </form>
      <?php else: ?>
        <div style="margin-top:16px">
          <a class="btn-primary"
             href="login_form.php?next=<?= h('produit.php?id=' . (int)$p['id']) ?>">Se connecter pour commander</a>
        </div>
      <?php endif; ?>
    <?php else: ?>
      <p style="margin-top:16px;font-size:13px;color:var(--muted)">
        Actuellement en rupture de stock. Contactez l'artisan via sa fiche pour une commande personnalisée.
      </p>
    <?php endif; ?>
  </div>
</div>

<!-- Section avis produit -->
<section style="margin-top:36px">
  <h2>Avis clients<?= $note_nb > 0 ? ' (' . $note_nb . ')' : '' ?></h2>

  <?php if ($user_commande_id): ?>
    <div class="card" style="max-width:540px;margin-bottom:20px">
      <h3 style="margin-top:0"><?= $user_avis ? 'Modifier votre avis' : 'Laisser un avis' ?></h3>
      <form method="post" action="noter_produit.php">
        <?= csrf_field() ?>
        <input type="hidden" name="produit_id" value="<?= $id ?>">
        <input type="hidden" name="commande_id" value="<?= (int)$user_commande_id ?>">
        <div class="form-row">
          <label>Note</label>
          <div class="stars-form">
            <?php for ($i=5;$i>=1;$i--): ?>
              <input type="radio" id="star<?= $i ?>" name="note" value="<?= $i ?>"
                     <?= ($user_avis && (int)$user_avis['note']===$i) ? 'checked' : '' ?> required>
              <label for="star<?= $i ?>">★</label>
            <?php endfor; ?>
          </div>
        </div>
        <div class="form-row" style="margin-top:10px">
          <label>Commentaire (optionnel)</label>
          <textarea name="commentaire" rows="3"><?= h($user_avis['commentaire'] ?? '') ?></textarea>
        </div>
        <div class="form-actions">
          <button class="btn-primary" type="submit"><?= $user_avis ? 'Mettre à jour' : 'Publier' ?></button>
        </div>
      </form>
    </div>
  <?php endif; ?>

  <?php if (!$avis_list): ?>
    <div class="empty">Aucun avis pour le moment.<?= is_logged() && !$user_commande_id ? ' Commandez ce produit pour pouvoir le noter.' : '' ?></div>
  <?php else: ?>
    <div class="review-list">
      <?php foreach ($avis_list as $av): ?>
        <div class="review-item">
          <div class="review-item-head">
            <span class="stars-display" style="font-size:15px">
              <?php for ($i=1;$i<=5;$i++): ?>
                <?= $i <= (int)$av['note'] ? '★' : '<span class="star-empty">★</span>' ?>
              <?php endfor; ?>
            </span>
            <strong style="color:var(--dark)"><?= h($av['prenom'] . ' ' . mb_substr($av['nom'],0,1) . '.') ?></strong>
            <span>· <?= h(date('d/m/Y', strtotime($av['created_at']))) ?></span>
          </div>
          <?php if (!empty($av['commentaire'])): ?>
            <div class="review-item-body"><?= nl2br(h($av['commentaire'])) ?></div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
