<?php
require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('register_form.php');
}
csrf_check();

$nom              = trim($_POST['nom'] ?? '');
$prenom           = trim($_POST['prenom'] ?? '');
$email            = trim($_POST['email'] ?? '');
$password         = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';
$role             = ($_POST['role'] ?? 'visiteur') === 'artisan' ? 'artisan' : 'visiteur';

if (!$nom || !$prenom || !$email || !$password) {
    redirect('register_form.php?err=3');
}
if ($password !== $password_confirm) {
    redirect('register_form.php?err=2');
}

$st = $pdo->prepare("SELECT id FROM utilisateur WHERE email = ? LIMIT 1");
$st->execute([$email]);
if ($st->fetch()) {
    redirect('register_form.php?err=1');
}

$hash = password_hash($password, PASSWORD_BCRYPT);
$pdo->prepare("INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, role)
               VALUES (?, ?, ?, ?, ?)")
    ->execute([$nom, $prenom, $email, $hash, $role]);
$userId = (int)$pdo->lastInsertId();

if ($role === 'artisan') {
    $boutique = trim($_POST['nom_boutique'] ?? '');
    if (!$boutique) $boutique = $prenom . ' ' . $nom;
    $pdo->prepare("INSERT INTO artisan (utilisateur_id, nom_boutique)
                   VALUES (?, ?)")->execute([$userId, $boutique]);
}

// Connexion auto
session_regenerate_id(true);
$_SESSION['user_id']   = $userId;
$_SESSION['user_nom']  = $prenom . ' ' . $nom;
$_SESSION['user_role'] = $role;
flash_set('success', 'Compte créé. Bienvenue sur Artify !');
redirect('index.php');
