<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();
csrf_check();

$produit_id = (int)($_POST['produit_id'] ?? 0);
$action     = $_POST['action'] ?? '';
$quantite   = (int)($_POST['quantite'] ?? 1);

if (isset($_SESSION['panier'][$produit_id])) {
    if ($action === 'supprimer' || $quantite <= 0) {
        unset($_SESSION['panier'][$produit_id]);
    } else {
        $stock = $_SESSION['panier'][$produit_id]['stock'];
        $_SESSION['panier'][$produit_id]['quantite'] = min($quantite, $stock);
    }
}

redirect('panier.php');
