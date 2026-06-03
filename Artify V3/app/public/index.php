<?php
/**
 * Front Controller MVC — point d'entrée unique pour les nouvelles routes.
 * Les anciennes pages PHP continuent de fonctionner directement.
 *
 * URLs gérées ici :
 *   /artify/messages                 (messagerie)
 *   /artify/messages/{id}
 *   /artify/messages/send (POST)
 *   /artify/forum                    (forum)
 *   /artify/forum/sujet/{id}
 *   /artify/forum/nouveau
 *   /artify/forum/store (POST)
 *   /artify/forum/repondre (POST)
 *   /artify/mot-de-passe-oublie
 *   /artify/reinitialiser-mot-de-passe
 */

require __DIR__ . '/../bootstrap.php';

use App\Core\Router;
use App\Controllers\MessageController;
use App\Controllers\ForumController;
use App\Controllers\AuthController;

$router = new Router();

// Messagerie
$router->get ('/messages',            [MessageController::class, 'index']);
$router->post('/messages/send',       [MessageController::class, 'send']);
$router->get ('/messages/{id}',       [MessageController::class, 'show']);

// Forum
$router->get ('/forum',                  [ForumController::class, 'index']);
$router->get ('/forum/nouveau',          [ForumController::class, 'create']);
$router->post('/forum/store',            [ForumController::class, 'store']);
$router->post('/forum/repondre',         [ForumController::class, 'reply']);
$router->get ('/forum/sujet/{id}',       [ForumController::class, 'show']);

// Mot de passe oublié
$router->get ('/mot-de-passe-oublie',         [AuthController::class, 'forgotForm']);
$router->post('/mot-de-passe-oublie',         [AuthController::class, 'forgotSend']);
$router->get ('/reinitialiser-mot-de-passe',  [AuthController::class, 'resetForm']);
$router->post('/reinitialiser-mot-de-passe',  [AuthController::class, 'resetSubmit']);

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
