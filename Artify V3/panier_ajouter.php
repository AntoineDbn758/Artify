<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();
csrf_check();

$produit_id = (int)($_POST['produit_id'] ?? 0);
$quantite   = max(1, (int)($_POST['quantite'] ?? 1));

if (!$produit_id) { redirect($_SERVER['HTTP_REFERER'] ?? 'index.php'); }

$st = $pdo->prepare("SELECT id, nom, prix, stock FROM produit WHERE id = ? AND est_publie = 1");
$st->execute([$produit_id]);
$p = $st->fetch();
if (!$p) { flash_set('error', 'Produit introuvable.'); redirect('creations.php'); }

if (!isset($_SESSION['panier'])) $_SESSION['panier'] = [];

$qte_actuelle = $_SESSION['panier'][$produit_id]['quantite'] ?? 0;
$nouvelle_qte = min($qte_actuelle + $quantite, (int)$p['stock']);

$_SESSION['panier'][$produit_id] = [
    'nom'      => $p['nom'],
    'prix'     => (float)$p['prix'],
    'quantite' => $nouvelle_qte,
    'stock'    => (int)$p['stock'],
];

flash_set('success', h($p['nom']) . ' ajouté au panier.');
redirect($_SERVER['HTTP_REFERER'] ?? 'panier.php');
