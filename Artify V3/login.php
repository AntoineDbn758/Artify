<?php
require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('login_form.php');
}
csrf_check();

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$next     = $_POST['next'] ?? 'index.php';
// Empêche redirection ouverte
if (!preg_match('#^[A-Za-z0-9_\-./?=&%]+$#', $next) || str_starts_with($next, '//')) {
    $next = 'index.php';
}

$stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    redirect('login_form.php?err=1');
}
if (!$user['est_actif']) {
    redirect('login_form.php?err=disabled');
}
if (!password_verify($password, $user['mot_de_passe'])) {
    redirect('login_form.php?err=1');
}

session_regenerate_id(true);
$_SESSION['user_id']   = (int)$user['id'];
$_SESSION['user_nom']  = $user['prenom'] . ' ' . $user['nom'];
$_SESSION['user_role'] = $user['role'];
flash_set('success', 'Bienvenue, ' . $user['prenom'] . ' !');
redirect($next);
