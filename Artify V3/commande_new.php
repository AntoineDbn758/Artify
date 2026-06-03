<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$page_title = 'Commande — Artify';

$pid = (int)($_POST['produit_id'] ?? $_GET['produit_id'] ?? 0);
$qty = max(1, (int)($_POST['quantite'] ?? 1));
if (!$pid) { http_response_code(400); die('Produit manquant.'); }

$st = $pdo->prepare(
    "SELECT p.id, p.nom, p.prix, p.stock, p.artisan_id
       FROM produit p
      WHERE p.id = ? AND p.est_publie = 1"
);
$st->execute([$pid]);
$p = $st->fetch();
if (!$p) { http_response_code(404); die('Produit introuvable.'); }
if ((int)$p['stock'] <= 0) { http_response_code(400); die('Produit en rupture de stock.'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
}

$user_id   = current_user_id();
$prix_unit = (float)$p['prix'];
$total     = $prix_unit * $qty;

$pdo->beginTransaction();
try {
    $pdo->prepare(
        "INSERT INTO commande (utilisateur_id, artisan_id, montant_total, statut)
         VALUES (?, ?, ?, 'confirmee')"
    )->execute([$user_id, (int)$p['artisan_id'], $total]);
    $commande_id = (int)$pdo->lastInsertId();

    $pdo->prepare(
        "INSERT INTO ligne_commande (commande_id, produit_id, quantite, prix_unitaire)
         VALUES (?, ?, ?, ?)"
    )->execute([$commande_id, $pid, $qty, $prix_unit]);

    $pdo->prepare("UPDATE produit SET stock = GREATEST(stock - ?, 0) WHERE id=?")
        ->execute([$qty, $pid]);

    $pdo->commit();
} catch (\Throwable $e) {
    $pdo->rollBack();
    http_response_code(500);
    die('Erreur création commande.');
}

header('Location: commande_success.php?commande=' . $commande_id);
exit;
