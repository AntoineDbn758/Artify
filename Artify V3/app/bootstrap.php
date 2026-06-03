<?php
/**
 * Bootstrap MVC — démarre session, autoloader PSR-4 simplifié, helpers.
 * Inclus par /artify/app/public/index.php (front controller MVC)
 * ET par les anciennes pages PHP qui veulent utiliser les nouveaux Models.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax',
    ]);
    session_start();
}

// Autoloader PSR-4 : App\Foo\Bar -> app/Foo/Bar.php
spl_autoload_register(function ($class) {
    if (strncmp($class, 'App\\', 4) !== 0) return;
    $path = __DIR__ . '/' . str_replace('\\', '/', substr($class, 4)) . '.php';
    if (is_file($path)) require $path;
});

// Helpers globaux compatibles avec l'ancienne base de code
if (!function_exists('h')) {
    function h($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('e')) {
    function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}
