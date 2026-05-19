<?php

/**
 * Detruit la session courante puis redirige vers l'accueil. Court et simple :
 * session_destroy() + header('Location: ...').
 */

require_once __DIR__ . '/includes/bootstrap.php';
// On vide la session puis on expire le cookie cote navigateur,
// sinon le SID resterait valide jusqu'a sa peremption naturelle.
// Vidage du tableau $_SESSION : tous les flags (user_id, role, csrf) tombent.
$_SESSION = [];
// On expire le cookie de session uniquement si l'app utilise bien les cookies (cas standard).
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    // time() - 42000 : date passee largement, force le navigateur a supprimer le cookie.
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
// Detruit definitivement les donnees de session cote serveur.
session_destroy();
// Retour accueil : pas de flash possible ici car la session est deja detruite.
header('Location: index.php');
exit;
