<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

csrf_check();

$produit_id = (int)($_POST['produit_id'] ?? 0);
$note       = (int)($_POST['note'] ?? 0);
$commentaire = trim($_POST['commentaire'] ?? '');
$commande_id = (int)($_POST['commande_id'] ?? 0) ?: null;

if (!$produit_id || $note < 1 || $note > 5) {
    flash_set('error', 'Note invalide.');
    redirect('produit.php?id=' . $produit_id);
}

// Vérifier que l'utilisateur a bien commandé ce produit
$st = $pdo->prepare(
    "SELECT c.id FROM commande c
       JOIN ligne_commande lc ON lc.commande_id = c.id
      WHERE c.utilisateur_id = ? AND lc.produit_id = ? AND c.statut = 'confirmee'
      LIMIT 1"
);
$st->execute([current_user_id(), $produit_id]);
if (!$st->fetch()) {
    flash_set('error', 'Vous devez avoir commandé ce produit pour le noter.');
    redirect('produit.php?id=' . $produit_id);
}

// Upsert : met à jour si déjà noté, crée sinon
$pdo->prepare(
    "INSERT INTO avis_produit (produit_id, utilisateur_id, commande_id, note, commentaire)
     VALUES (?, ?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE note=VALUES(note), commentaire=VALUES(commentaire)"
)->execute([
    $produit_id,
    current_user_id(),
    $commande_id,
    $note,
    $commentaire ?: null,
]);

flash_set('success', 'Merci pour votre avis !');
redirect('produit.php?id=' . $produit_id);
