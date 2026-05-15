<?php
// includes/bootstrap.php - initialisation commune (session, BDD, helpers).
// À inclure en tout début de chaque page (avant tout output).

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
    $_SESSION['_seeded'] = 1;
}

/* - Helpers d'échappement / utilitaires - */
function h($s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}
function is_logged(): bool { return !empty($_SESSION['user_id']); }
function current_user_id(): ?int { return $_SESSION['user_id'] ?? null; }
function current_user_role(): string { return $_SESSION['user_role'] ?? 'visiteur'; }
function current_user_name(): string { return $_SESSION['user_nom'] ?? ''; }
function is_admin(): bool   { return current_user_role() === 'admin'; }
function is_artisan(): bool { return current_user_role() === 'artisan'; }

function require_login(): void {
    if (!is_logged()) redirect('login_form.php?next=' . urlencode($_SERVER['REQUEST_URI'] ?? ''));
}
function require_role(string $role): void {
    require_login();
    if (current_user_role() !== $role) {
        http_response_code(403);
        die('Accès refusé : rôle "' . h($role) . '" requis.');
    }
}

/* - CSRF - */
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}
function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . h(csrf_token()) . '">';
}
function csrf_check(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
    $sent = $_POST['csrf'] ?? '';
    if (!is_string($sent) || !hash_equals($_SESSION['csrf'] ?? '', $sent)) {
        http_response_code(400);
        die('Jeton CSRF invalide. Recharge la page et réessaie.');
    }
}

/* - Récupère l'utilisateur courant (depuis BDD si besoin) - */
function current_user(PDO $pdo): ?array {
    static $cache = null;
    if ($cache !== null) return $cache;
    if (!is_logged()) return null;
    $st = $pdo->prepare("SELECT * FROM utilisateur WHERE id = ? LIMIT 1");
    $st->execute([current_user_id()]);
    $cache = $st->fetch() ?: null;
    return $cache;
}

/* - Récupère la fiche artisan de l'utilisateur courant - */
function current_artisan(PDO $pdo): ?array {
    if (!is_logged()) return null;
    $st = $pdo->prepare("SELECT * FROM artisan WHERE utilisateur_id = ? LIMIT 1");
    $st->execute([current_user_id()]);
    return $st->fetch() ?: null;
}

/* - Flash messages (1 read = 1 pop) - */
function flash_set(string $type, string $msg): void {
    $_SESSION['flash'][] = ['type' => $type, 'msg' => $msg];
}
function flash_pop(): array {
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}
