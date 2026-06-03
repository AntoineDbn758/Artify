<?php
/**
 * Édition complète d'un utilisateur par un admin.
 * Champs modifiables : nom, prenom, email, ville, role, est_actif.
 */
$page_title = 'Modifier utilisateur — Backoffice';
require_once __DIR__ . '/_header.php';
/** @var PDO $pdo */

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if (!$id) { flash_set('error', 'Identifiant manquant.'); redirect('users.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $nom    = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $ville  = trim($_POST['ville'] ?? '');
    $role   = $_POST['role'] ?? 'visiteur';
    $actif  = isset($_POST['est_actif']) ? 1 : 0;

    $errors = [];
    if (!$nom || !$prenom)            $errors[] = "Nom et prénom requis.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalide.";
    if (!in_array($role, ['visiteur','artisan','admin'], true)) $errors[] = "Rôle invalide.";

    // Email unique (sauf le user actuel)
    if (!$errors) {
        $st = $pdo->prepare("SELECT id FROM utilisateur WHERE email = ? AND id <> ? LIMIT 1");
        $st->execute([$email, $id]);
        if ($st->fetchColumn()) $errors[] = "Cet email est déjà utilisé par un autre compte.";
    }

    if (!$errors) {
        $pdo->prepare(
            "UPDATE utilisateur
                SET nom = ?, prenom = ?, email = ?, ville = ?, role = ?, est_actif = ?
              WHERE id = ?"
        )->execute([$nom, $prenom, $email, $ville ?: null, $role, $actif, $id]);

        // Si on bascule l'utilisateur en artisan, créer la fiche boutique si absente
        if ($role === 'artisan') {
            $st = $pdo->prepare("SELECT id FROM artisan WHERE utilisateur_id = ?");
            $st->execute([$id]);
            if (!$st->fetchColumn()) {
                $pdo->prepare("INSERT INTO artisan (utilisateur_id, nom_boutique) VALUES (?, ?)")
                    ->execute([$id, "$prenom $nom"]);
            }
        }

        flash_set('success', "Utilisateur #$id mis à jour.");
        redirect('users.php');
    }
    // Sinon : reaffichage du formulaire avec erreurs
} else {
    $errors = [];
}

$st = $pdo->prepare("SELECT * FROM utilisateur WHERE id = ? LIMIT 1");
$st->execute([$id]);
$u = $st->fetch();
if (!$u) { flash_set('error', "Utilisateur introuvable."); redirect('users.php'); }
?>

<div class="crumb"><a href="index.php">Backoffice</a> &rsaquo; <a href="users.php">Utilisateurs</a> &rsaquo; Modifier #<?= (int)$u['id'] ?></div>
<h1>Modifier utilisateur</h1>

<?php foreach ($errors as $e): ?><div class="flash flash-error"><?= h($e) ?></div><?php endforeach; ?>

<div class="admin-card" style="max-width:680px">
  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">

    <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
      <div><label>Prénom *</label>
        <input type="text" name="prenom" required value="<?= h($_POST['prenom'] ?? $u['prenom']) ?>"></div>
      <div><label>Nom *</label>
        <input type="text" name="nom" required value="<?= h($_POST['nom'] ?? $u['nom']) ?>"></div>
    </div>

    <div class="form-row"><label>Email *</label>
      <input type="email" name="email" required value="<?= h($_POST['email'] ?? $u['email']) ?>"></div>

    <div class="form-row"><label>Ville</label>
      <input type="text" name="ville" value="<?= h($_POST['ville'] ?? ($u['ville'] ?? '')) ?>"></div>

    <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
      <div><label>Rôle</label>
        <select name="role">
          <?php foreach (['visiteur','artisan','admin'] as $r): ?>
            <option value="<?= $r ?>" <?= ($_POST['role'] ?? $u['role'])===$r?'selected':'' ?>><?= $r ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div style="display:flex;align-items:end;padding-bottom:8px">
        <label style="display:flex;gap:6px;align-items:center">
          <input type="checkbox" name="est_actif" value="1" <?= (isset($_POST['est_actif']) ? $_POST['est_actif'] : $u['est_actif']) ? 'checked' : '' ?> style="width:auto">
          Compte actif
        </label>
      </div>
    </div>

    <div class="form-row" style="background:var(--ocre-pale);padding:10px;border-radius:8px;font-size:.85rem">
      <strong>Infos compte :</strong><br>
      Inscrit le <?= h(date('d/m/Y H:i', strtotime($u['created_at']))) ?>
      · Hash mot de passe : <code><?= h(substr($u['mot_de_passe'], 0, 12)) ?>…</code>
    </div>

    <div class="form-actions">
      <button class="btn-primary" type="submit">Enregistrer</button>
      <a class="btn-ghost" href="users.php">Annuler</a>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>
