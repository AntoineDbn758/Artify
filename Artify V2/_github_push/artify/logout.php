<?php

/**
 * Detruit la session courante puis redirige vers l'accueil. Court et simple :
 * session_destroy() + header('Location: ...').
 */

require_once __DIR__ . '/includes/bootstrap.php';
// On vide la session puis on expire le cookie cote navigateur,
// sinon le SID resterait valide jusqu'a sa peremption naturelle.
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();
header('Location: index.php');
exit;
