<?php

/**
 * Le bootstrap de toutes les pages : demarre la session avec des cookies
 * httponly + samesite, ouvre la BDD, definit les helpers qu'on utilise
 * partout. Notamment : h() pour echapper du HTML, csrf_token() / csrf_field()
 * / csrf_check() pour proteger les formulaires, require_login() et
 * require_role() pour bloquer l'acces selon le profil utilisateur. Chaque
 * page commence par require_once includes/bootstrap.php.
 */

// includes/bootstrap.php - initialisation commune (session, BDD, helpers).
// À inclure en tout début de chaque page (avant tout output).

// Cookie de session durci : httponly bloque l'acces JS,
// samesite=Lax limite les envois cross-site (anti CSRF basique).
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once __DIR__ . '/../connexion.php';   // expose $pdo
// Seed idempotent : ne fait qu'une fois par session pour éviter
// l'overhead à chaque page (sécurise les hashs placeholder + crée des
// événements/galeries de démo si la base est vide).
if (empty($_SESSION['_seeded'])) {
    require_once __DIR__ . '/seed.php';
    ensure_demo_seed($pdo);
    // Flag en session pour zapper le seed jusqu'a la prochaine fenetre du navigateur.
    $_SESSION['_seeded'] = 1;
}

/* - Helpers d'échappement / utilitaires - */
// h() = raccourci utilise dans tous les templates pour echapper avant affichage.
function h($s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
// exit apres header() sinon la suite du script continue de s'executer.
function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}
// Lecture seule des infos de session : pas de query BDD, pas cher a appeler.
function is_logged(): bool { return !empty($_SESSION['user_id']); }
function current_user_id(): ?int { return $_SESSION['user_id'] ?? null; }
function current_user_role(): string { return $_SESSION['user_role'] ?? 'visiteur'; }
function current_user_name(): string { return $_SESSION['user_nom'] ?? ''; }
function is_admin(): bool   { return current_user_role() === 'admin'; }
function is_artisan(): bool { return current_user_role() === 'artisan'; }

// Garde a placer en haut des pages privees : redirige vers login si pas connecte.
function require_login(): void {
    // ?next= permet de revenir sur la page demandee une fois loggue.
    if (!is_logged()) redirect('login_form.php?next=' . urlencode($_SERVER['REQUEST_URI'] ?? ''));
}
// Verrou de role utilise pour les pages admin / artisan exclusivement.
function require_role(string $role): void {
    require_login();
    if (current_user_role() !== $role) {
        // 403 explicite plutot que redirect, pour debug et pour les bots.
        http_response_code(403);
        die('Accès refusé : rôle "' . h($role) . '" requis.');
    }
}

/* - CSRF - */
// Token genere a la volee et stocke en session, reutilise tant
// qu'il existe pour eviter d'invalider les formulaires ouverts.
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}
// Helper a inserer dans chaque <form method="post"> pour propager le token.
function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . h(csrf_token()) . '">';
}
function csrf_check(): void {
    // Pas de check sur GET : seuls les POST mutent l'etat.
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
    $sent = $_POST['csrf'] ?? '';
    // hash_equals pour eviter les timing attacks lors de la comparaison.
    if (!is_string($sent) || !hash_equals($_SESSION['csrf'] ?? '', $sent)) {
        // 400 + die immediat : on ne traite jamais une requete sans token valide.
        http_response_code(400);
        die('Jeton CSRF invalide. Recharge la page et réessaie.');
    }
}

/* - Récupère l'utilisateur courant (depuis BDD si besoin) - */
function current_user(PDO $pdo): ?array {
    // Cache statique pour ne pas relire l'utilisateur a chaque appel
    // dans la meme requete.
    static $cache = null;
    if ($cache !== null) return $cache;
    if (!is_logged()) return null;
    // Lecture complete : utile pour afficher prenom, avatar, prefs.
    $st = $pdo->prepare("SELECT * FROM utilisateur WHERE id = ? LIMIT 1");
    $st->execute([current_user_id()]);
    $cache = $st->fetch() ?: null;
    return $cache;
}

/* - Récupère la fiche artisan de l'utilisateur courant - */
function current_artisan(PDO $pdo): ?array {
    if (!is_logged()) return null;
    // Renvoie null pour les utilisateurs visiteur/admin : seul l'artisan a une fiche.
    $st = $pdo->prepare("SELECT * FROM artisan WHERE utilisateur_id = ? LIMIT 1");
    $st->execute([current_user_id()]);
    return $st->fetch() ?: null;
}

/* - Flash messages (1 read = 1 pop) - */
// Ecrit un message qui sera affiche par header.php au prochain chargement.
function flash_set(string $type, string $msg): void {
    $_SESSION['flash'][] = ['type' => $type, 'msg' => $msg];
}
// Lecture destructive : on retire les messages apres affichage
// pour qu'ils n'apparaissent pas deux fois.
function flash_pop(): array {
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}
