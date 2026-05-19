<?php

/**
 * Suppression d'un produit. Demande confirmation. Verifie aussi le ownership
 * avant de supprimer. Les images liees (image_produit) sont supprimees en
 * cascade via ON DELETE CASCADE.
 */

require_once __DIR__ . '/includes/bootstrap.php';
// Endpoint reserve aux artisans.
require_role('artisan');
// On refuse les GET pour empecher une suppression accidentelle via simple lien partage, et on exige le token CSRF.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('boutique.php'); }
// Token CSRF obligatoire pour bloquer les suppressions cross-site.
csrf_check();
$artisan = current_artisan($pdo);
// Garde-fou : un compte artisan sans fiche boutique ne peut rien supprimer.
if (!$artisan) { flash_set('error', 'Aucune boutique.'); redirect('profile.php'); }
// On combine id + artisan_id dans le WHERE : si l'id ne correspond pas a un produit de cet artisan, rowCount() vaut 0 et on remonte une erreur generique.
// Cette double condition fait le check d'ownership en une seule requete (anti-IDOR).
$id = (int)($_POST['id'] ?? 0);
$st = $pdo->prepare("DELETE FROM produit WHERE id = ? AND artisan_id = ?");
$st->execute([$id, (int)$artisan['id']]);
// rowCount > 0 = produit existait + appartenait a l'artisan + a ete supprime.
if ($st->rowCount() > 0) flash_set('success', 'Produit supprimé.');
// Message generique pour ne pas reveler s'il s'agit d'une non-existence ou d'un mauvais owner.
else flash_set('error', 'Produit introuvable ou non autorisé.');
// Redirect final vers la boutique pour fermer le cycle PRG.
redirect('boutique.php');
