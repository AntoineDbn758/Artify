<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\PasswordReset;

class AuthController extends Controller
{
    /** GET /mot-de-passe-oublie — formulaire email. */
    public function forgotForm(): void
    {
        $this->view('auth/forgot', ['title' => 'Mot de passe oublié — Artify']);
    }

    /** POST /mot-de-passe-oublie — génère le lien (en démo on l'affiche). */
    public function forgotSend(): void
    {
        $email = trim($_POST['email'] ?? '');
        $users = new User();
        $u = $users->byEmail($email);

        // Génère toujours un message identique pour ne pas leaker l'existence des emails.
        $resetUrl = null;
        if ($u) {
            $reset = (new PasswordReset())->create((int)$u['id'], 60);
            // En prod : envoyer par mail via PHPMailer. En démo : on affiche le lien.
            $resetUrl = 'reset.php?token=' . $reset;
        }
        $this->view('auth/forgot_sent', [
            'title' => 'Lien envoyé — Artify',
            'demoLink' => $resetUrl,
        ]);
    }

    /** GET /reinitialiser-mot-de-passe?token=... */
    public function resetForm(): void
    {
        $token = $_GET['token'] ?? '';
        $reset = (new PasswordReset())->findValid($token);
        if (!$reset) {
            $this->view('auth/reset_invalid', ['title' => 'Lien invalide — Artify']);
            return;
        }
        $this->view('auth/reset', ['token' => $token, 'title' => 'Nouveau mot de passe — Artify']);
    }

    /** POST /reinitialiser-mot-de-passe */
    public function resetSubmit(): void
    {
        $token = $_POST['token'] ?? '';
        $new   = $_POST['password'] ?? '';
        $conf  = $_POST['password_confirm'] ?? '';
        $resetModel = new PasswordReset();
        $reset = $resetModel->findValid($token);
        if (!$reset) {
            $this->view('auth/reset_invalid', ['title' => 'Lien invalide — Artify']);
            return;
        }
        $errors = User::validatePassword($new);
        if ($new !== $conf) $errors[] = "Les mots de passe ne correspondent pas.";
        if ($errors) {
            $this->view('auth/reset', [
                'token' => $token, 'errors' => $errors,
                'title' => 'Nouveau mot de passe — Artify',
            ]);
            return;
        }
        (new User())->setPassword((int)$reset['utilisateur_id'], $new);
        $resetModel->markUsed((int)$reset['id']);
        $_SESSION['flash'][] = ['type' => 'success', 'msg' => 'Mot de passe modifié. Vous pouvez vous connecter.'];
        $this->redirect('login_form.php');
    }
}
