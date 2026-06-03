<?php
/**
 * Entry point MVC — Messagerie.
 * Délègue tout au MessageController.
 */
require __DIR__ . '/app/bootstrap.php';

$controller = new App\Controllers\MessageController();
$action     = $_GET['action'] ?? 'index';

if ($action === 'show' && isset($_GET['id'])) {
    $controller->show(['id' => (int)$_GET['id']]);
} elseif ($action === 'send' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->send();
} else {
    $controller->index();
}
