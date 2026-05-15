<?php
/**
 * commande_new.php - Crée une commande "en_attente" puis redirige vers
 * la page Stripe Checkout (mode test).
 *
 * Pré-requis :
 *   - utilisateur connecté
 *   - variables d'env STRIPE_SECRET_KEY + STRIPE_PUBLISHABLE_KEY
 *
 * Usage :
 *   POST commande_new.php  produit_id=4  quantite=1  csrf=...
 *   GET  commande_new.php  affiche un avertissement si la conf est absente
 */
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/stripe.php';
require_login();

$page_title = 'Paiement - Artify';

// Si Stripe n'est pas configuré, on affiche un message clair plutôt que de
// renvoyer une erreur cryptique.
if (!stripe_configured()) {
    include __DIR__ . '/includes/header.php';
    ?>
    <h1>Paiement temporairement indisponible</h1>
    <p>Le moyen de paiement n'est pas encore activé sur cet environnement.</p>
    <p>Pour l'administrateur : renseigner les variables d'environnement
       <code>STRIPE_PUBLISHABLE_KEY</code> et <code>STRIPE_SECRET_KEY</code>
       dans <code>artify_docker/.env</code> (voir <code>STRIPE.md</code>),
       puis <code>docker compose up -d --force-recreate web</code>.</p>
    <p><a class="btn-ghost" href="index.php">Retour à l'accueil</a></p>
    <?php
    include __DIR__ . '/includes/footer.php';
    exit;
}

// Récupère le produit
$pid = (int)($_POST['produit_id'] ?? $_GET['produit_id'] ?? 0);
$qty = max(1, (int)($_POST['quantite'] ?? 1));
if (!$pid) { http_response_code(400); die('Produit manquant.'); }

$st = $pdo->prepare(
    "SELECT p.id, p.nom, p.prix, p.stock, p.artisan_id, ip.url AS image_url
       FROM produit p
       LEFT JOIN image_produit ip ON ip.produit_id = p.id AND ip.est_principale = 1
      WHERE p.id = ? AND p.est_publie = 1"
);
$st->execute([$pid]);
$p = $st->fetch();
if (!$p) { http_response_code(404); die('Produit introuvable.'); }
if ((int)$p['stock'] <= 0) { http_response_code(400); die('Produit en rupture de stock.'); }

// CSRF si POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
}

$user_id   = current_user_id();
$prix_unit = (float)$p['prix'];
$total     = $prix_unit * $qty;

// Crée la commande en BDD (statut en_attente)
$pdo->beginTransaction();
try {
    $pdo->prepare(
        "INSERT INTO commande (utilisateur_id, artisan_id, montant_total, statut)
         VALUES (?, ?, ?, 'en_attente')"
    )->execute([$user_id, (int)$p['artisan_id'], $total]);
    $commande_id = (int)$pdo->lastInsertId();

    $pdo->prepare(
        "INSERT INTO ligne_commande (commande_id, produit_id, quantite, prix_unitaire)
         VALUES (?, ?, ?, ?)"
    )->execute([$commande_id, $pid, $qty, $prix_unit]);
    $pdo->commit();
} catch (\Throwable $e) {
    $pdo->rollBack();
    http_response_code(500);
    die('Erreur création commande : ' . h($e->getMessage()));
}

// Email du client pour pré-remplir Stripe Checkout
$u = $pdo->prepare("SELECT email FROM utilisateur WHERE id = ?");
$u->execute([$user_id]);
$email = $u->fetchColumn() ?: null;

// Crée la session Stripe Checkout
$base    = rtrim(getenv('APP_BASE_URL') ?: 'http://127.0.0.1/artify', '/');
$success = "$base/commande_success.php?commande={$commande_id}&session={CHECKOUT_SESSION_ID}";
$cancel  = "$base/commande_cancel.php?commande={$commande_id}";

$session = stripe_create_checkout(
    [[ 'nom' => $p['nom'], 'prix' => $prix_unit, 'quantite' => $qty, 'image_url' => $p['image_url'] ?? '' ]],
    $success, $cancel, $email
);

if (($session['_http_code'] ?? 0) !== 200 || empty($session['url'])) {
    $pdo->prepare("UPDATE commande SET statut='annulee' WHERE id=?")->execute([$commande_id]);
    include __DIR__ . '/includes/header.php';
    ?>
    <h1>Erreur Stripe</h1>
    <p>Stripe a refusé la création de la session de paiement.</p>
    <pre style="background:#fff;padding:12px;border:1px solid var(--border);border-radius:8px;overflow:auto;max-width:100%"><?php
      echo h(json_encode($session, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    ?></pre>
    <p><a class="btn-ghost" href="produit.php?id=<?= (int)$pid ?>">Revenir au produit</a></p>
    <?php
    include __DIR__ . '/includes/footer.php';
    exit;
}

// Stocke l'id de session Stripe pour la réconciliation côté success
$pdo->prepare("UPDATE commande SET message_personnalisation=? WHERE id=?")
    ->execute([$session['id'], $commande_id]);

// Redirection vers la page Stripe (hostée)
header('Location: ' . $session['url']);
exit;
