<?php

/**
 * Suppression d'un produit. Demande confirmation. Verifie aussi le ownership
 * avant de supprimer. Les images liees (image_produit) sont supprimees en
 * cascade via ON DELETE CASCADE.
 */

require_once __DIR__ . '/includes/bootstrap.php';
require_role('artisan');
// On refuse les GET pour empecher une suppression accidentelle via simple lien partage, et on exige le token CSRF.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('boutique.php'); }
csrf_check();
$artisan = current_artisan($pdo);
if (!$artisan) { flash_set('error', 'Aucune boutique.'); redirect('profile.php'); }
// On combine id + artisan_id dans le WHERE : si l'id ne correspond pas a un produit de cet artisan, rowCount() vaut 0 et on remonte une erreur generique.
$id = (int)($_POST['id'] ?? 0);
$st = $pdo->prepare("DELETE FROM produit WHERE id = ? AND artisan_id = ?");
$st->execute([$id, (int)$artisan['id']]);
if ($st->rowCount() > 0) flash_set('success', 'Produit supprimé.');
else flash_set('error', 'Produit introuvable ou non autorisé.');
redirect('boutique.php');
