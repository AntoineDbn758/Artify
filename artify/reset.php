<?php
require __DIR__ . '/app/bootstrap.php';
$c = new App\Controllers\AuthController();
if ($_SERVER['REQUEST_METHOD'] === 'POST') { $c->resetSubmit(); } else { $c->resetForm(); }
