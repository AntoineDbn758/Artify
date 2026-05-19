<?php /* Fermeture du <main> ouvert dans header.php : a chaque include header doit correspondre un include footer. */ ?>
</main>
<footer class="footer">
  <!-- Logo footer reprend l'identite visuelle de la nav. -->
  <div class="footer-logo"><span class="logo-a">A</span><span>Artify</span></div>
  <!-- Liens legaux et utilitaires : obligatoires pour conformite (CGU, mentions). -->
  <div class="footer-links">
    <?php /* h($base ?? '') : fallback chaine vide si $base n'a pas ete defini par la page. */ ?>
    <a href="<?= h($base ?? '') ?>index.php">Accueil</a>
    <a href="<?= h($base ?? '') ?>faq.php">FAQ</a>
    <a href="<?= h($base ?? '') ?>cgu.php">CGU</a>
    <a href="<?= h($base ?? '') ?>mentions.php">Mentions légales</a>
    <a href="<?= h($base ?? '') ?>contact.php">Contact</a>
  </div>
  <?php /* date('Y') : annee dynamique, pas a maintenir chaque 1er janvier. */ ?>
  <div class="footer-copy">&copy; <?= date('Y') ?> Artify - Plateforme artisans &amp; créateurs (projet ISEP)</div>
</footer>
</body>
</html>
