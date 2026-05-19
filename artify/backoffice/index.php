<?php

/**
 * Dashboard : cartes de statistiques (nombre d'utilisateurs par role, nombre
 * de produits, nombre de commandes par statut, messages contact non traites,
 * ...). Vue d'ensemble rapide de la plateforme.
 */

$page_title = 'Dashboard - Backoffice Artify';
require_once __DIR__ . '/_header.php';

/** @var PDO $pdo */
// Bloc de comptages pour les cartes du dashboard. Un COUNT() par metric pour
// rester lisible plutot qu'un gros UNION.
$counts = [
  'users_total'      => (int)$pdo->query("SELECT COUNT(*) FROM utilisateur")->fetchColumn(),
  'users_actif'      => (int)$pdo->query("SELECT COUNT(*) FROM utilisateur WHERE est_actif=1")->fetchColumn(),
  'users_admin'      => (int)$pdo->query("SELECT COUNT(*) FROM utilisateur WHERE role='admin'")->fetchColumn(),
  'users_artisan'    => (int)$pdo->query("SELECT COUNT(*) FROM utilisateur WHERE role='artisan'")->fetchColumn(),
  'users_visiteur'   => (int)$pdo->query("SELECT COUNT(*) FROM utilisateur WHERE role='visiteur'")->fetchColumn(),
  'artisans'         => (int)$pdo->query("SELECT COUNT(*) FROM artisan")->fetchColumn(),
  'artisans_verifies'=> (int)$pdo->query("SELECT COUNT(*) FROM artisan WHERE verifie=1")->fetchColumn(),
  'produits'         => (int)$pdo->query("SELECT COUNT(*) FROM produit")->fetchColumn(),
  'produits_publies' => (int)$pdo->query("SELECT COUNT(*) FROM produit WHERE est_publie=1")->fetchColumn(),
  'evenements'       => (int)$pdo->query("SELECT COUNT(*) FROM evenement")->fetchColumn(),
  'contacts_nontr'   => (int)$pdo->query("SELECT COUNT(*) FROM contact WHERE traite=0")->fetchColumn(),
  'avis'             => (int)$pdo->query("SELECT COUNT(*) FROM avis")->fetchColumn(),
  'categories'       => (int)$pdo->query("SELECT COUNT(*) FROM categorie")->fetchColumn(),
];

// Statuts de commandes (group by) ; le total sert au calcul des pourcentages en bas du tableau.
$cmd_stats = $pdo->query(
  "SELECT statut, COUNT(*) AS n FROM commande GROUP BY statut"
)->fetchAll();
$cmd_total = array_sum(array_column($cmd_stats, 'n'));

// Dernière activité : 5 dernières inscriptions et 5 derniers contacts
$last_users = $pdo->query(
  "SELECT id, prenom, nom, email, role, created_at FROM utilisateur ORDER BY created_at DESC LIMIT 5"
)->fetchAll();
$last_contacts = $pdo->query(
  "SELECT id, nom, sujet, traite, created_at FROM contact ORDER BY created_at DESC LIMIT 5"
)->fetchAll();
?>
<div class="crumb"><a href="../index.php">Accueil</a> &rsaquo; Backoffice</div>
<h1>Dashboard</h1>
<p>Vue d'ensemble de la plateforme Artify - données en temps réel.</p>

<div class="stat-grid">
  <a class="stat-card" href="users.php">
    <div class="num"><?= $counts['users_total'] ?></div>
    <div class="lbl">Utilisateurs</div>
  </a>
  <a class="stat-card info" href="users.php?role=artisan">
    <div class="num"><?= $counts['users_artisan'] ?></div>
    <div class="lbl">Comptes artisans</div>
  </a>
  <a class="stat-card" href="users.php?role=visiteur">
    <div class="num"><?= $counts['users_visiteur'] ?></div>
    <div class="lbl">Comptes visiteurs</div>
  </a>
  <a class="stat-card success" href="artisans.php">
    <div class="num"><?= $counts['artisans_verifies'] ?>/<?= $counts['artisans'] ?></div>
    <div class="lbl">Artisans vérifiés</div>
  </a>
  <a class="stat-card" href="produits.php">
    <div class="num"><?= $counts['produits_publies'] ?>/<?= $counts['produits'] ?></div>
    <div class="lbl">Produits publiés</div>
  </a>
  <a class="stat-card info" href="evenements.php">
    <div class="num"><?= $counts['evenements'] ?></div>
    <div class="lbl">Événements</div>
  </a>
  <a class="stat-card success" href="commandes.php">
    <div class="num"><?= $cmd_total ?></div>
    <div class="lbl">Commandes</div>
  </a>
  <?php // La carte passe en rouge des qu'au moins un contact attend une reponse. ?>
  <a class="stat-card <?= $counts['contacts_nontr']>0 ? 'danger' : '' ?>" href="contacts.php">
    <div class="num"><?= $counts['contacts_nontr'] ?></div>
    <div class="lbl">Contacts non traités</div>
  </a>
  <a class="stat-card" href="avis.php">
    <div class="num"><?= $counts['avis'] ?></div>
    <div class="lbl">Avis</div>
  </a>
  <a class="stat-card" href="categories.php">
    <div class="num"><?= $counts['categories'] ?></div>
    <div class="lbl">Catégories</div>
  </a>
</div>

<div class="grid grid-2" style="gap:24px">
  <div class="admin-card">
    <h3>Commandes par statut</h3>
    <?php if (!$cmd_stats): ?>
      <div class="empty-state">Aucune commande pour le moment.</div>
    <?php else: ?>
      <table class="adm">
        <thead><tr><th>Statut</th><th>Nombre</th><th>%</th></tr></thead>
        <tbody>
        <?php // Garde-fou anti-division-par-zero quand il n'y a aucune commande. ?>
        <?php foreach ($cmd_stats as $row):
          $pc = $cmd_total ? round(100*$row['n']/$cmd_total, 1) : 0; ?>
          <tr>
            <td><span class="badge"><?= h($row['statut']) ?></span></td>
            <td><?= (int)$row['n'] ?></td>
            <td><?= h($pc) ?> %</td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <div class="admin-card">
    <h3>Dernières inscriptions</h3>
    <?php if (!$last_users): ?>
      <div class="empty-state">Aucun utilisateur.</div>
    <?php else: ?>
      <table class="adm">
        <thead><tr><th>Nom</th><th>Email</th><th>Rôle</th><th>Date</th></tr></thead>
        <tbody>
        <?php foreach ($last_users as $u): ?>
          <tr>
            <td><?= h(trim(($u['prenom'] ?? '') . ' ' . ($u['nom'] ?? ''))) ?></td>
            <td><?= h($u['email']) ?></td>
            <td><span class="badge role-<?= h($u['role']) ?>"><?= h($u['role']) ?></span></td>
            <td><?= h(date('d/m/Y', strtotime($u['created_at']))) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>

<div class="admin-card" style="margin-top:18px">
  <h3>Derniers messages de contact</h3>
  <?php if (!$last_contacts): ?>
    <div class="empty-state">Aucun message reçu.</div>
  <?php else: ?>
    <table class="adm">
      <thead><tr><th>De</th><th>Sujet</th><th>Statut</th><th>Reçu le</th><th class="actions"></th></tr></thead>
      <tbody>
      <?php foreach ($last_contacts as $c): ?>
        <tr>
          <td><?= h($c['nom']) ?></td>
          <td><?= h($c['sujet']) ?></td>
          <td><?= $c['traite'] ? '<span class="badge ok">traité</span>' : '<span class="badge warn">non traité</span>' ?></td>
          <td><?= h(date('d/m/Y H:i', strtotime($c['created_at']))) ?></td>
          <td class="actions"><a class="btn-ghost btn-small" href="contacts.php">Voir tous</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>
