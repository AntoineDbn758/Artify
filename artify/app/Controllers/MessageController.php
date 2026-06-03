<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Message;
use App\Models\User;

class MessageController extends Controller
{
    private Message $messages;
    private User $users;

    public function __construct()
    {
        parent::__construct();
        $this->messages = new Message();
        $this->users    = new User();
    }

    /** GET /messages — liste des conversations. */
    public function index(): void
    {
        $this->requireLogin();
        $userId = (int)$_SESSION['user_id'];
        $convs  = $this->messages->conversationsFor($userId);
        $this->view('messages/index', [
            'conversations' => $convs,
            'unread'        => $this->messages->unreadCount($userId),
            'title'         => 'Messagerie — Artify',
        ]);
    }

    /** GET /messages/{contact_id} — fil de discussion. */
    public function show(array $params): void
    {
        $this->requireLogin();
        $me      = (int)$_SESSION['user_id'];
        $contact = (int)$params['id'];
        if ($contact === $me) { $this->redirect('messages.php'); }

        $contactUser = $this->users->find($contact);
        if (!$contactUser) { http_response_code(404); die('Contact introuvable.'); }

        $this->messages->markRead($me, $contact);
        $thread = $this->messages->thread($me, $contact);
        $this->view('messages/show', [
            'thread'  => $thread,
            'contact' => $contactUser,
            'title'   => 'Conversation — Artify',
        ]);
    }

    /** POST /messages/send — envoyer un message. */
    public function send(): void
    {
        $this->requireLogin();
        $me      = (int)$_SESSION['user_id'];
        $to      = (int)($_POST['destinataire_id'] ?? 0);
        $contenu = trim($_POST['contenu'] ?? '');
        if (!$to || !$contenu || $to === $me) {
            $this->redirect('messages.php');
        }
        $this->messages->send($me, $to, $contenu);
        $this->redirect('messages.php?action=show&id=' . $to);
    }
}
