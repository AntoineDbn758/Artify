<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\ForumSujet;
use App\Models\ForumMessage;

class ForumController extends Controller
{
    private ForumSujet $sujets;
    private ForumMessage $messages;

    public function __construct()
    {
        parent::__construct();
        $this->sujets   = new ForumSujet();
        $this->messages = new ForumMessage();
    }

    /** GET /forum — liste des sujets paginée. */
    public function index(): void
    {
        $page     = max(1, (int)($_GET['page'] ?? 1));
        $perPage  = 15;
        $cat      = $_GET['cat'] ?? null;
        $cat      = in_array($cat, ['general','artisanat','technique','annonces','aide'], true) ? $cat : null;
        $sujets   = $this->sujets->listing($cat, ($page - 1) * $perPage, $perPage);
        $total    = $this->sujets->count($cat);
        $this->view('forum/index', [
            'sujets'   => $sujets,
            'page'     => $page,
            'pages'    => max(1, (int)ceil($total / $perPage)),
            'cat'      => $cat,
            'total'    => $total,
            'title'    => 'Forum — Artify',
        ]);
    }

    /** GET /forum/sujet/{id} — détail + réponses. */
    public function show(array $params): void
    {
        $id    = (int)$params['id'];
        $sujet = $this->sujets->withMessages($id);
        if (!$sujet) { http_response_code(404); die('Sujet introuvable.'); }
        $this->view('forum/show', ['sujet' => $sujet, 'title' => $sujet['titre'] . ' — Forum Artify']);
    }

    /** GET /forum/nouveau — formulaire de création (connecté). */
    public function create(): void
    {
        $this->requireLogin();
        $this->view('forum/create', ['title' => 'Nouveau sujet — Forum']);
    }

    /** POST /forum/store — créer un sujet. */
    public function store(): void
    {
        $this->requireLogin();
        $titre     = trim($_POST['titre'] ?? '');
        $contenu   = trim($_POST['contenu'] ?? '');
        $categorie = $_POST['categorie'] ?? 'general';
        if (!in_array($categorie, ['general','artisanat','technique','annonces','aide'], true)) {
            $categorie = 'general';
        }
        if (!$titre || !$contenu) {
            $_SESSION['flash'][] = ['type' => 'error', 'msg' => 'Titre et message requis.'];
            $this->redirect('forum.php?action=new');
        }
        $sid = $this->sujets->insert([
            'titre'          => mb_substr($titre, 0, 200),
            'categorie'      => $categorie,
            'utilisateur_id' => (int)$_SESSION['user_id'],
        ]);
        $this->messages->post($sid, (int)$_SESSION['user_id'], $contenu);
        $this->redirect('forum.php?action=show&id=' . $sid);
    }

    /** POST /forum/repondre — répondre à un sujet. */
    public function reply(): void
    {
        $this->requireLogin();
        $sid     = (int)($_POST['sujet_id'] ?? 0);
        $contenu = trim($_POST['contenu'] ?? '');
        if (!$sid || !$contenu) { $this->redirect('forum.php'); }
        $sujet = $this->sujets->find($sid);
        if (!$sujet || $sujet['est_ferme']) {
            $this->redirect('forum.php?action=show&id=' . $sid);
        }
        $this->messages->post($sid, (int)$_SESSION['user_id'], $contenu);
        $this->redirect('forum.php?action=show&id=' . $sid . '#bas');
    }
}
