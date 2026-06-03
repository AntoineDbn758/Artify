<?php
/**
 * _fix_encoding.php — Répare les accents cassés en BDD en mettant à jour
 * les lignes existantes avec les valeurs propres du fichier _seed_demo.data.php.
 *
 * À supprimer après usage. URL protégée par token.
 */
require_once __DIR__ . '/connexion.php';
if (($_GET['token'] ?? '') !== 'artify-fix') {
    http_response_code(403); die('Token manquant.');
}
header('Content-Type: text/plain; charset=utf-8');
$data = require __DIR__ . '/_seed_demo.data.php';
echo "=== Fix encoding accents ===\n\n";

// ---- Produits (match par artisan email + nom partiel) ------------------
$nP = 0;
foreach ($data['produits'] as $pdef) {
    [$art_email, $cat_id, $nom, $prix, $mat, $dim, $delai, $stock] = $pdef;
    // On reconnait le produit cassé par son artisan + son ordre/prix unique
    // Plus sûr : par artisan + prix exact + même longueur
    $st = $pdo->prepare(
      "SELECT p.id FROM produit p
         JOIN artisan a ON a.id=p.artisan_id
         JOIN utilisateur u ON u.id=a.utilisateur_id
        WHERE u.email = ? AND p.prix = ?
          AND CHAR_LENGTH(p.nom) = CHAR_LENGTH(?)
        LIMIT 1");
    $st->execute([$art_email, $prix, $nom]);
    $pid = $st->fetchColumn();
    if ($pid) {
        $pdo->prepare("UPDATE produit SET nom=?, materiaux=?, dimensions=? WHERE id=?")
            ->execute([$nom, $mat, $dim, $pid]);
        $nP++;
    }
}
echo "Produits réparés : $nP\n";

// ---- Galerie (match par titre cassé approximatif via index) ------------
// Vu qu'il n'y a pas d'identifiant naturel, on remplace les titres par ordre
$st = $pdo->query("SELECT id FROM galerie ORDER BY id ASC");
$ids = $st->fetchAll(PDO::FETCH_COLUMN);
$nG = 0;
foreach ($data['galerie'] as $i => $gdef) {
    [$artist_email, $prod_nom, $img_seed, $titre, $desc] = $gdef;
    if (!isset($ids[$i])) continue;
    $pdo->prepare("UPDATE galerie SET titre=?, description=? WHERE id=?")
        ->execute([$titre, $desc, $ids[$i]]);
    $nG++;
}
echo "Galerie réparée : $nG\n";

// ---- Avis (match par utilisateur+artisan, on ré-écrit le commentaire) --
$nA = 0;
foreach ($data['avis'] as $adef) {
    [$ue, $ae, $note, $cm] = $adef;
    $st = $pdo->prepare(
      "SELECT av.id FROM avis av
         JOIN utilisateur u ON u.id=av.utilisateur_id
         JOIN artisan a    ON a.id=av.artisan_id
         JOIN utilisateur ua ON ua.id=a.utilisateur_id
        WHERE u.email=? AND ua.email=? AND av.note=?
        LIMIT 1");
    $st->execute([$ue, $ae, $note]);
    $aid = $st->fetchColumn();
    if ($aid) {
        $pdo->prepare("UPDATE avis SET commentaire=? WHERE id=?")
            ->execute([$cm, $aid]);
        $nA++;
    }
}
echo "Avis réparés : $nA\n";

// ---- Contacts : on supprime ceux avec '?' suivi d'une lettre minuscule
$pdo->exec("DELETE FROM contact WHERE nom REGEXP '\\?[a-zA-Z]' OR message REGEXP '\\?[a-zA-Z]'");
echo "Contacts cassés supprimés.\n";

// Les FAQ et événements : check
$pdo->exec("DELETE FROM evenement WHERE titre REGEXP '\\?[a-zA-Z]' OR description REGEXP '\\?[a-zA-Z]'");
echo "Événements cassés supprimés.\n";

// Pour les images produit, on garde (URLs ASCII)

echo "\n=== Récap final ===\n";
foreach (['produit', 'galerie', 'avis', 'contact', 'evenement', 'utilisateur'] as $t) {
    $n = $pdo->query("SELECT COUNT(*) FROM $t")->fetchColumn();
    echo str_pad($t, 14) . " : $n\n";
}
echo "\nRelancer _seed_demo.php?token=artify-demo pour re-créer les rangs supprimés.\n";
