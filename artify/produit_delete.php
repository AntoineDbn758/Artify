<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_role('artisan');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('boutique.php'); }
csrf_check();
$artisan = current_artisan($pdo);
if (!$artisan) { flash_set('error', 'Aucune boutique.'); redirect('profile.php'); }
$id = (int)($_POST['id'] ?? 0);
$st = $pdo->prepare("DELETE FROM produit WHERE id = ? AND artisan_id = ?");
$st->execute([$id, (int)$artisan['id']]);
if ($st->rowCount() > 0) flash_set('success', 'Produit supprimé.');
else flash_set('error', 'Produit introuvable ou non autorisé.');
redirect('boutique.php');
