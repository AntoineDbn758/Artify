<?php

/**
 * Handler du POST d'inscription. Verifie l'unicite de l'email, hash le mot de
 * passe en BCRYPT, cree le compte. Si le role choisi est 'artisan', cree
 * aussi automatiquement une fiche dans la table artisan (nom_boutique par
 * defaut = prenom + nom).
 */

require_once __DIR__ . '/includes/bootstrap.php';

// Acces direct en GET interdit, on renvoie sur le formulaire.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('register_form.php');
}
csrf_check();

$nom              = trim($_POST['nom'] ?? '');
$prenom           = trim($_POST['prenom'] ?? '');
$email            = trim($_POST['email'] ?? '');
$password         = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';
// Whitelist du role : on accepte uniquement artisan ou visiteur,
// jamais admin meme si quelqu'un trafique le formulaire.
$role             = ($_POST['role'] ?? 'visiteur') === 'artisan' ? 'artisan' : 'visiteur';

if (!$nom || !$prenom || !$email || !$password) {
    redirect('register_form.php?err=3');
}
if ($password !== $password_confirm) {
    redirect('register_form.php?err=2');
}

// Verification unicite de l'email avant insertion pour eviter
// l'erreur SQL et donner un message utilisateur clair.
$st = $pdo->prepare("SELECT id FROM utilisateur WHERE email = ? LIMIT 1");
$st->execute([$email]);
if ($st->fetch()) {
    redirect('register_form.php?err=1');
}

// BCRYPT inclut le sel, on ne stocke jamais le mot de passe en clair.
$hash = password_hash($password, PASSWORD_BCRYPT);
$pdo->prepare("INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, role)
               VALUES (?, ?, ?, ?, ?)")
    ->execute([$nom, $prenom, $email, $hash, $role]);
$userId = (int)$pdo->lastInsertId();

// Si artisan, on cree la fiche boutique liee dans la foulee
// pour eviter un etat intermediaire sans boutique.
if ($role === 'artisan') {
    $boutique = trim($_POST['nom_boutique'] ?? '');
    if (!$boutique) $boutique = $prenom . ' ' . $nom;
    $pdo->prepare("INSERT INTO artisan (utilisateur_id, nom_boutique)
                   VALUES (?, ?)")->execute([$userId, $boutique]);
}

// Regeneration de l'ID de session apres login pour contrer la session fixation.
session_regenerate_id(true);
$_SESSION['user_id']   = $userId;
$_SESSION['user_nom']  = $prenom . ' ' . $nom;
$_SESSION['user_role'] = $role;
flash_set('success', 'Compte créé. Bienvenue sur Artify !');
redirect('index.php');
