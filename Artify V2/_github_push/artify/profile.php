<?php

/**
 * Page de profil de l'utilisateur connecte. Affiche son avatar, ses infos
 * personnelles, son role, et propose des actions (modifier profil, changer
 * mot de passe, voir sa boutique si artisan, acceder au backoffice si admin).
 */

require_once __DIR__ . '/includes/bootstrap.php';
// Page reservee aux utilisateurs connectes, redirect vers login si visiteur anonyme.
require_login();
$page_title = 'Mon profil - Artify';

// current_artisan renvoie null si l'utilisateur n'a pas le role artisan, ce qui sert ensuite a masquer le menu Ma boutique.
// Recupere les donnees utilisateur completes (jointure avec table utilisateur).
$u = current_user($pdo);
$artisan = current_artisan($pdo);
include __DIR__ . '/includes/header.php';
?>
<div class="crumb"><a href="index.php">Accueil</a> &rsaquo; Mon profil</div>
<h1>Bonjour, <?= h($u['prenom']) ?> !</h1>

<div class="detail">
  <?php // Si l'utilisateur a charge un avatar, on l'affiche, sinon fallback initiales pour evitez les image cassees. ?>
  <?php if (!empty($u['avatar_url'])): ?>
    <img class="visual" src="<?= h($u['avatar_url']) ?>" alt="<?= h($u['prenom'] . ' ' . $u['nom']) ?>">
  <?php else: ?>
    <?php // Initiales en grand format Playfair pour rester elegant en l'absence de photo. ?>
    <div class="visual" style="display:flex;align-items:center;justify-content:center;font-family:'Playfair Display',serif;font-size:96px;color:var(--ocre)">
      <?= h(mb_substr($u['prenom'], 0, 1) . mb_substr($u['nom'], 0, 1)) ?>
    </div>
  <?php endif; ?>
  <div>
    <h2 style="margin-top:0"><?= h($u['prenom'] . ' ' . $u['nom']) ?></h2>
    <span class="tag"><?= h($u['role']) ?></span>
    <dl>
      <dt>Email</dt><dd><?= h($u['email']) ?></dd>
      <dt>Ville</dt><dd><?= h($u['ville'] ?: '-') ?></dd>
      <dt>Téléphone</dt><dd><?= h($u['telephone'] ?: '-') ?></dd>
      <dt>Bio</dt><dd><?= nl2br(h($u['bio'] ?: '-')) ?></dd>
      <dt>Membre depuis</dt><dd><?= h(date('d/m/Y', strtotime($u['created_at']))) ?></dd>
    </dl>
    <div style="margin-top:18px;display:flex;gap:8px;flex-wrap:wrap">
      <a class="btn-primary" href="profile_edit.php">Modifier mon profil</a>
      <a class="btn-ghost" href="change_password.php">Changer mon mot de passe</a>
      <?php // Bouton "Ma boutique" affiche uniquement si l'user est artisan. ?>
      <?php if ($artisan): ?>
        <a class="btn-ghost" href="boutique.php">Ma boutique</a>
      <?php endif; ?>
      <?php // Lien backoffice reserve aux admins, le bouton ne sert qu'a faciliter la navigation, le controle d'acces reste cote backoffice. ?>
      <?php if (is_admin()): ?>
        <a class="btn-ghost" href="backoffice/index.php">Administration</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php if ($artisan): ?>
  <h2 style="margin-top:30px">Mon activité d'artisan</h2>
  <p>Boutique : <strong><?= h($artisan['nom_boutique']) ?></strong>
     <a class="btn-ghost btn-small" href="boutique.php" style="margin-left:8px">Gérer ma boutique</a></p>
<?php endif; ?>

<?php
// Inscriptions aux evenements : on garde les 10 dernieres pour un apercu, le detail complet n'est pas necessaire ici.
// Tri DESC pour mettre l'evenement le plus recent en haut (passe ou futur, peu importe).
$st = $pdo->prepare(
  "SELECT e.id, e.titre, e.date_debut, ie.statut
     FROM inscription_evenement ie JOIN evenement e ON e.id = ie.evenement_id
    WHERE ie.utilisateur_id = ? ORDER BY e.date_debut DESC LIMIT 10");
$st->execute([current_user_id()]);
$insc = $st->fetchAll();
if ($insc):
?>
  <h2 style="margin-top:30px">Mes inscriptions aux événements</h2>
  <table class="tbl">
    <tr><th>Événement</th><th>Date</th><th>Statut</th></tr>
    <?php foreach ($insc as $i): ?>
      <tr>
        <td><a href="evenement.php?id=<?= (int)$i['id'] ?>"><?= h($i['titre']) ?></a></td>
        <td><?= h(date('d/m/Y H:i', strtotime($i['date_debut']))) ?></td>
        <td><?= h($i['statut']) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
