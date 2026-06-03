<?php
/**
 * Entry point MVC — Forum.
 * Délègue tout au ForumController.
 */
require __DIR__ . '/app/bootstrap.php';

$c = new App\Controllers\ForumController();
$action = $_GET['action'] ?? 'index';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'store')   { $c->store();  exit; }
    if ($action === 'reply')   { $c->reply();  exit; }
}
switch ($action) {
    case 'show':    $c->show(['id' => (int)($_GET['id'] ?? 0)]); break;
    case 'new':     $c->create(); break;
    default:        $c->index();
}
