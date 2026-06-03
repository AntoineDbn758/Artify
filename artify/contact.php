<?php
require_once __DIR__ . '/includes/bootstrap.php';
$page_title = 'Contact — Artify';
$bodyClass  = 'compact-contact';

$sent = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $nom     = trim($_POST['nom'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $sujet   = trim($_POST['sujet'] ?? '');
    $message = trim($_POST['message'] ?? '');
    if (!$nom)     $errors[] = "Le nom est requis.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalide.";
    if (!$sujet)   $errors[] = "Le sujet est requis.";
    if (!$message || mb_strlen($message) < 10) $errors[] = "Le message doit faire au moins 10 caractères.";

    if (!$errors) {
        $pdo->prepare("INSERT INTO contact (nom, email, sujet, message) VALUES (?, ?, ?, ?)")
            ->execute([$nom, $email, $sujet, $message]);
        $sent = true;
    }
}
include __DIR__ . '/includes/header.php';
?>
<div class="crumb"><a href="index.php">Accueil</a> &rsaquo; Contact</div>

<div class="contact-compact">
  <div class="form-card">
    <h1>Nous contacter</h1>
    <?php if ($sent): ?>
      <div class="flash flash-success">Merci ! Votre message a été enregistré.</div>
    <?php endif; ?>
    <?php foreach ($errors as $e): ?>
      <div class="flash flash-error"><?= h($e) ?></div>
    <?php endforeach; ?>
    <?php if (!$sent): ?>
    <form method="post" action="contact.php">
      <?= csrf_field() ?>
      <div class="form-row"><label>Nom</label>
        <input type="text" name="nom" required value="<?= h($_POST['nom'] ?? (current_user_name() ?: '')) ?>"></div>
      <div class="form-row"><label>Email</label>
        <?php $cu = current_user($pdo); ?>
        <input type="email" name="email" required value="<?= h($_POST['email'] ?? ($cu['email'] ?? '')) ?>"></div>
      <div class="form-row"><label>Sujet</label>
        <input type="text" name="sujet" required value="<?= h($_POST['sujet'] ?? '') ?>"></div>
      <div class="form-row"><label>Message</label>
        <textarea name="message" required minlength="10"><?= h($_POST['message'] ?? '') ?></textarea></div>
      <div class="form-actions"><button class="btn-primary" type="submit">Envoyer</button></div>
    </form>
    <?php endif; ?>
  </div>

  <div class="contact-info">
    <h2>Coordonnées</h2>
    <ul>
      <li><strong>Email :</strong> contact@artify.fr</li>
      <li><strong>Téléphone :</strong> 01 23 45 67 89</li>
      <li><strong>Adresse :</strong> 28 rue Notre-Dame des Champs, 75006 Paris</li>
      <li><strong>Horaires :</strong> Lun–Ven 9h–18h</li>
    </ul>
    <h2 style="margin-top:14px">Suivez-nous</h2>
    <ul>
      <li>Instagram : <a href="#">@artify.fr</a></li>
      <li>Facebook : <a href="#">Artify France</a></li>
      <li>LinkedIn : <a href="#">Artify</a></li>
    </ul>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
