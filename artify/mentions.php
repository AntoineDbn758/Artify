<?php

/**
 * Page des mentions legales. Une seule ligne en BDD (table mention_legale),
 * mise a jour depuis le backoffice.
 */

require_once __DIR__ . '/includes/bootstrap.php';
$page_title = 'Mentions légales - Artify';
// On lit la derniere ligne mise a jour, meme si en theorie la table n'en contient qu'une seule.
$ml = $pdo->query("SELECT contenu, updated_at FROM mention_legale ORDER BY updated_at DESC LIMIT 1")->fetch();
include __DIR__ . '/includes/header.php';
?>
<div class="crumb"><a href="index.php">Accueil</a> &rsaquo; Mentions légales</div>
<h1>Mentions légales</h1>

<?php if (!$ml): ?>
  <div class="card">
    <h3>Éditeur</h3>
    <p>Artify - projet pédagogique réalisé dans le cadre de la formation ISEP.<br>
       28 Rue Notre-Dame-des-Champs, 75006 Paris.</p>
    <h3>Hébergement</h3>
    <p>Plateforme hébergée en local sur stack Docker (Apache + PHP 8.2 + MariaDB).</p>
    <h3>Responsable de la publication</h3>
    <p>L'équipe projet Artify.</p>
    <h3>Données personnelles</h3>
    <p>Les données collectées (compte utilisateur, messages de contact) sont utilisées exclusivement pour le
       fonctionnement de la plateforme et ne sont pas transmises à des tiers. Vous disposez d'un droit d'accès,
       de rectification et de suppression de vos données conformément au RGPD.</p>
    <h3>Cookies</h3>
    <p>Artify utilise uniquement un cookie de session technique nécessaire à l'authentification. Aucun cookie
       publicitaire ou de traçage tiers n'est déposé.</p>
  </div>
<?php else: ?>
  <div class="card">
    <?= nl2br(h($ml['contenu'])) ?>
    <p class="meta" style="margin-top:14px">Dernière mise à jour : <?= h(date('d/m/Y', strtotime($ml['updated_at']))) ?></p>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
