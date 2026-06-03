<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();
csrf_check();

if (empty($_SESSION['panier'])) {
    flash_set('error', 'Votre panier est vide.');
    redirect('panier.php');
}

$panier = $_SESSION['panier'];
$commandes_ids = [];

// Regroupe par artisan pour créer une commande par artisan
$par_artisan = [];
foreach ($panier as $pid => $item) {
    $st = $pdo->prepare("SELECT artisan_id, stock FROM produit WHERE id = ? AND est_publie = 1");
    $st->execute([$pid]);
    $row = $st->fetch();
    if (!$row || $row['stock'] < $item['quantite']) continue;
    $par_artisan[$row['artisan_id']][] = ['pid' => $pid, 'item' => $item];
}

if (!$par_artisan) {
    flash_set('error', 'Aucun produit disponible dans votre panier.');
    redirect('panier.php');
}

foreach ($par_artisan as $artisan_id => $lignes) {
    $total = array_sum(array_map(fn($l) => $l['item']['prix'] * $l['item']['quantite'], $lignes));

    $pdo->beginTransaction();
    try {
        $pdo->prepare(
            "INSERT INTO commande (utilisateur_id, artisan_id, montant_total, statut) VALUES (?,?,?,'confirmee')"
        )->execute([current_user_id(), $artisan_id, $total]);
        $cid = (int)$pdo->lastInsertId();

        foreach ($lignes as $l) {
            $pdo->prepare(
                "INSERT INTO ligne_commande (commande_id, produit_id, quantite, prix_unitaire) VALUES (?,?,?,?)"
            )->execute([$cid, $l['pid'], $l['item']['quantite'], $l['item']['prix']]);
            $pdo->prepare("UPDATE produit SET stock = GREATEST(stock - ?, 0) WHERE id = ?")
                ->execute([$l['item']['quantite'], $l['pid']]);
        }
        $pdo->commit();
        $commandes_ids[] = $cid;
    } catch (\Throwable $e) {
        $pdo->rollBack();
    }
}

unset($_SESSION['panier']);
flash_set('success', 'Commande(s) confirmée(s) !');
redirect('mes_commandes.php');
