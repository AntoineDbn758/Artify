<?php

/**
 * Formulaire de contact. POST : stocke un nouveau message dans la table
 * contact avec traite=0. L'admin retrouve ces messages dans
 * backoffice/contacts.php pour les traiter.
 */

require_once __DIR__ . '/includes/bootstrap.php';
$page_title = 'Contact - Artify';

// Flag d'affichage du message de succes.
$sent = false;
// Liste des erreurs de validation affichees en haut du formulaire.
$errors = [];

// Le formulaire est ouvert aux visiteurs anonymes, donc on s'appuie uniquement sur le token CSRF + validation cote serveur.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Token CSRF obligatoire meme pour anonymes, evite la soumission depuis un autre domaine.
    csrf_check();
    // trim systematique : evite les espaces accidentels qui fausseraient les controles "champ non vide".
    $nom     = trim($_POST['nom'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $sujet   = trim($_POST['sujet'] ?? '');
    $message = trim($_POST['message'] ?? '');
    // Validations minimales avant insertion en base.
    if (!$nom)     $errors[] = "Le nom est requis.";
    // filter_var verifie le format RFC, plus fiable qu'une regex maison.
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalide.";
    if (!$sujet)   $errors[] = "Le sujet est requis.";
    // Plancher a 10 caracteres pour eviter les spams "ok" / "test".
    if (!$message || mb_strlen($message) < 10) $errors[] = "Le message doit faire au moins 10 caractères.";

    // Insertion avec traite=0 par defaut, l'admin retrouvera le message dans backoffice/contacts.php.
    if (!$errors) {
        $pdo->prepare("INSERT INTO contact (nom, email, sujet, message) VALUES (?, ?, ?, ?)")
            ->execute([$nom, $email, $sujet, $message]);
        $sent = true;
    }
}
include __DIR__ . '/includes/header.php';
?>
<div class="crumb"><a href="index.php">Accueil</a> &rsaquo; Contact</div>

<div class="form-card">
  <h1>Nous contacter</h1>
  <p>Une question, une suggestion, un partenariat ? Écrivez-nous.</p>

  <?php if ($sent): ?>
    <div class="flash flash-success">Merci ! Votre message a été enregistré. Nous vous répondrons rapidement.</div>
  <?php endif; ?>
  <?php foreach ($errors as $e): ?>
    <div class="flash flash-error"><?= h($e) ?></div>
  <?php endforeach; ?>

  <?php // Le formulaire disparait apres envoi pour empecher une double soumission au refresh. ?>
  <?php if (!$sent): ?>
  <form method="post" action="contact.php">
    <?= csrf_field() ?>
    <div class="form-row"><label>Nom</label>
      <?php // Pre-rempli avec le nom du user connecte si disponible, sinon avec ce qu'il vient de taper. ?>
      <input type="text" name="nom" required value="<?= h($_POST['nom'] ?? (current_user_name() ?: '')) ?>"></div>
    <div class="form-row"><label>Email</label>
      <?php // current_user pour pre-remplir l'email automatiquement (pratique pour les comptes existants). ?>
      <?php $cu = current_user($pdo); ?>
      <input type="email" name="email" required value="<?= h($_POST['email'] ?? ($cu['email'] ?? '')) ?>"></div>
    <div class="form-row"><label>Sujet</label>
      <input type="text" name="sujet" required value="<?= h($_POST['sujet'] ?? '') ?>"></div>
    <div class="form-row"><label>Message</label>
      <?php // minlength duplique cote HTML pour message d'erreur native + UX, mais la validation serveur reste la source de verite. ?>
      <textarea name="message" required minlength="10" style="min-height:140px"><?= h($_POST['message'] ?? '') ?></textarea></div>
    <div class="form-actions"><button class="btn-primary" type="submit">Envoyer</button></div>
  </form>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
