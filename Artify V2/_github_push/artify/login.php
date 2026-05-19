<?php

/**
 * Handler du POST de connexion. password_verify() pour comparer le mot de
 * passe au hash BCRYPT stocke. En cas de succes, regenere l'identifiant de
 * session (anti session-fixation) puis remplit $_SESSION avec id, nom, role.
 */

require_once __DIR__ . '/includes/bootstrap.php';

// Acces GET interdit : on renvoie le visiteur vers le formulaire affichable.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('login_form.php');
}
// CSRF systematique avant traitement des credentials.
csrf_check();

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
// next = page demandee initialement, capturee par require_login() pour revenir dessus.
$next     = $_POST['next'] ?? 'index.php';
// Empeche les open redirects : on rejette les URL absolues
// (// ou protocole) et tout caractere non autorise.
if (!preg_match('#^[A-Za-z0-9_\-./?=&%]+$#', $next) || str_starts_with($next, '//')) {
    $next = 'index.php';
}

// LIMIT 1 explicite : l'email a un unique en base, mais protege contre une eventuelle anomalie.
$stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
$user = $stmt->fetch();

// Meme message d'erreur si l'utilisateur n'existe pas ou si le mot
// de passe est mauvais : pas de fuite d'info sur l'existence du compte.
if (!$user) {
    redirect('login_form.php?err=1');
}
// Compte desactive par un admin : on bloque l'auth meme si le password est correct.
if (!$user['est_actif']) {
    redirect('login_form.php?err=disabled');
}
// password_verify gere le sel et la comparaison constante (anti timing attack).
if (!password_verify($password, $user['mot_de_passe'])) {
    redirect('login_form.php?err=1');
}

// Nouvel ID de session apres authentification (anti session-fixation).
session_regenerate_id(true);
// Hydratation session : cast int sur l'id pour eviter toute string injectee.
$_SESSION['user_id']   = (int)$user['id'];
$_SESSION['user_nom']  = $user['prenom'] . ' ' . $user['nom'];
$_SESSION['user_role'] = $user['role'];
flash_set('success', 'Bienvenue, ' . $user['prenom'] . ' !');
// Redirection finale vers la page initialement demandee (ou accueil par defaut).
redirect($next);
