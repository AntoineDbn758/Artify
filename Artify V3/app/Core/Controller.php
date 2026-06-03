<?php
namespace App\Core;

/**
 * Controller de base.
 * Tous les controllers de l'app héritent de cette classe.
 */
abstract class Controller
{
    protected \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::pdo();
    }

    /** Rend une vue. */
    protected function view(string $tpl, array $data = []): void
    {
        View::render($tpl, $data);
    }

    /** Redirige et stoppe le script. */
    protected function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }

    /** Vérifie la connexion, redirige sinon. */
    protected function requireLogin(): void
    {
        if (empty($_SESSION['user_id'])) {
            $this->redirect('login_form.php');
        }
    }

    /** Vérifie un rôle (admin / artisan). */
    protected function requireRole(string $role): void
    {
        $this->requireLogin();
        if (($_SESSION['user_role'] ?? '') !== $role) {
            http_response_code(403);
            die('Accès refusé.');
        }
    }
}
