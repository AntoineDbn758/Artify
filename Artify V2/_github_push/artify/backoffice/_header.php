<?php
// backoffice/_header.php - header dédié au backoffice :
//   - inclut la nav publique (style cohérent) via includes/header.php
//   - ouvre un layout flex avec une sidebar gauche
// Toutes les pages backoffice doivent inclure CE fichier (et _footer.php)
// après avoir défini $page_title et $base = '../'.

require_once __DIR__ . '/../includes/bootstrap.php';
require_role('admin');

$page_title = $page_title ?? 'Backoffice - Artify';
$base = '../';

// Page active (pour le surlignage de la sidebar)
$current = basename($_SERVER['SCRIPT_NAME'] ?? '');

include __DIR__ . '/../includes/header.php';

// Surcharge CSS dédiée admin (chargée APRÈS style.css du header)
?>
<link rel="stylesheet" href="<?= h($base) ?>backoffice/css/admin.css">
<?php
// Items de la sidebar : [file => [label, icone]]
$nav = [
  'index.php'      => ['Dashboard',     'DB'],
  'users.php'      => ['Utilisateurs',  'US'],
  'artisans.php'   => ['Artisans',      'AR'],
  'produits.php'   => ['Produits',      'PR'],
  'categories.php' => ['Catégories',    'CT'],
  'evenements.php' => ['Événements',    'EV'],
  'commandes.php'  => ['Commandes',     'CM'],
  'avis.php'       => ['Avis',          'AV'],
  '_sep'           => null,
  'contacts.php'   => ['Contacts',      'CN'],
  'faq.php'        => ['FAQ',           'FQ'],
  'cgu.php'        => ['CGU',           'CG'],
  'mentions.php'   => ['Mentions',      'ML'],
];
?>
<div class="admin-shell">
  <aside class="admin-sidebar">
    <h2>Admin Artify</h2>
    <ul>
      <?php foreach ($nav as $file => $info): ?>
        <?php if ($file === '_sep'): ?>
          <li class="sep"></li>
        <?php else:
          $active = ($current === $file) ? ' class="active"' : ''; ?>
          <li><a href="<?= h($file) ?>"<?= $active ?>>
            <span class="ico"><?= h($info[1]) ?></span>
            <span class="lbl"><?= h($info[0]) ?></span>
          </a></li>
        <?php endif; ?>
      <?php endforeach; ?>
    </ul>
  </aside>
  <section class="admin-content">
