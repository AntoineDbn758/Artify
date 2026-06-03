<?php
require_once __DIR__ . '/includes/bootstrap.php';
$id = (int)($_GET['id'] ?? 0);

// Inscription POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'inscrire') {
    csrf_check();
    require_login();
    $eid = (int)($_POST['evenement_id'] ?? 0);
    try {
        $pdo->prepare("INSERT INTO inscription_evenement (evenement_id, utilisateur_id)
                       VALUES (?, ?)")
            ->execute([$eid, current_user_id()]);
        flash_set('success', 'Inscription confirmée à cet événement !');
    } catch (PDOException $e) {
        flash_set('info', 'Vous êtes déjà inscrit à cet événement.');
    }
    redirect('evenement.php?id=' . $eid);
}

$st = $pdo->prepare(
  "SELECT e.*, a.nom_boutique, a.id AS aid
     FROM evenement e JOIN artisan a ON a.id = e.artisan_id
    WHERE e.id = ?");
$st->execute([$id]);
$e = $st->fetch();
if (!$e) {
    http_response_code(404);
    $page_title = 'Événement introuvable';
    include __DIR__ . '/includes/header.php';
    echo '<div class="empty">Cet événement n\'existe pas.</div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}
$page_title = $e['titre'] . ' — Artify';

// Compte d'inscrits
$st = $pdo->prepare("SELECT COUNT(*) FROM inscription_evenement WHERE evenement_id = ? AND statut != 'annulee'");
$st->execute([$id]);
$nb_inscrits = (int)$st->fetchColumn();

$deja_inscrit = false;
if (is_logged()) {
    $st = $pdo->prepare("SELECT id FROM inscription_evenement WHERE evenement_id = ? AND utilisateur_id = ?");
    $st->execute([$id, current_user_id()]);
    $deja_inscrit = (bool)$st->fetchColumn();
}

include __DIR__ . '/includes/header.php';
?>
<div class="crumb"><a href="index.php">Accueil</a> &rsaquo; <a href="evenements.php">Événements</a> &rsaquo; <?= h($e['titre']) ?></div>

<div class="detail">
  <img class="visual" src="https://images.unsplash.com/photo-1559563458-527698bf5295?w=800&h=600&fit=crop&q=80" alt="<?= h($e['titre']) ?>">
  <div>
    <h1><?= h($e['titre']) ?></h1>
    <p class="meta">Organisé par <a href="artisan.php?id=<?= (int)$e['aid'] ?>"><?= h($e['nom_boutique']) ?></a></p>
    <dl>
      <dt>Date</dt><dd><?= h(date('d/m/Y H:i', strtotime($e['date_debut']))) ?>
        <?= $e['date_fin'] ? ' &rarr; ' . h(date('d/m/Y H:i', strtotime($e['date_fin']))) : '' ?></dd>
      <?php if (!empty($e['lieu'])): ?>
        <dt>Lieu</dt><dd><?= h($e['lieu']) ?><?= $e['ville'] ? ' (' . h($e['ville']) . ')' : '' ?></dd>
      <?php endif; ?>
      <dt>Prix</dt><dd><?= $e['prix_entree'] > 0 ? number_format((float)$e['prix_entree'], 2, ',', ' ') . ' €' : 'Gratuit' ?></dd>
      <?php if ($e['capacite_max']): ?>
        <dt>Places</dt><dd><?= $nb_inscrits ?> / <?= (int)$e['capacite_max'] ?></dd>
      <?php else: ?>
        <dt>Inscrits</dt><dd><?= $nb_inscrits ?></dd>
      <?php endif; ?>
      <?php if (!empty($e['description'])): ?>
        <dt>Description</dt><dd><?= nl2br(h($e['description'])) ?></dd>
      <?php endif; ?>
    </dl>

    <div style="margin-top:18px">
      <?php if (!is_logged()): ?>
        <a class="btn-primary" href="login_form.php?next=<?= urlencode('evenement.php?id=' . $id) ?>">Se connecter pour s'inscrire</a>
      <?php elseif ($deja_inscrit): ?>
        <div class="flash flash-info">Vous êtes inscrit à cet événement.</div>
      <?php else: ?>
        <form method="post" action="evenement.php?id=<?= (int)$id ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="inscrire">
          <input type="hidden" name="evenement_id" value="<?= (int)$id ?>">
          <button class="btn-primary" type="submit">S'inscrire à l'événement</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
