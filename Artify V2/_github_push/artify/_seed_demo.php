<?php

/**
 * Script de seed pour repeupler la base avec un jeu de donnees de
 * demonstration (utilisateurs, artisans, produits, evenements, FAQ, ...).
 * Idempotent : on peut le relancer sans casser, les INSERTs verifient
 * l'existence avant. Acces protege par un token dans l'URL. A SUPPRIMER avant
 * toute mise en production.
 */

/**
 * Artify - Seeder demo (one-shot, idempotent)
 *
 * Usage :
 *   HTTP : http://127.0.0.1/_seed_demo.php?token=artify-demo
 *   CLI  : docker exec artify-web php /var/www/html/_seed_demo.php
 *
 * À SUPPRIMER (avec _seed_demo.data.php) avant la mise en prod.
 */

declare(strict_types=1);

// En CLI le script est appele depuis docker exec, sans token URL.
$cli = (PHP_SAPI === 'cli');
if (!$cli) {
    // Garde-fou minimal en HTTP : token statique dans l'URL. Suffisant pour un projet pedagogique mais a retirer en prod.
    if (($_GET['token'] ?? '') !== 'artify-demo') {
        http_response_code(403); exit("Forbidden. Use ?token=artify-demo\n");
    }
    // text/plain pour un affichage console-like dans le navigateur, pas de markup parasite.
    header('Content-Type: text/plain; charset=utf-8');
}

// Lecture credentials depuis l'env Docker, fallback sur les valeurs par defaut du compose.
$dbHost = getenv('DB_HOST') ?: 'db';
$dbName = getenv('DB_NAME') ?: 'artify';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASSWORD') ?: '';

try {
    $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) { exit("DB error: " . $e->getMessage() . "\n"); }
$pdo->exec("SET NAMES utf8mb4");

$data = require __DIR__ . '/_seed_demo.data.php';
function p(string $m): void { echo $m . "\n"; @ob_flush(); @flush(); }
p("=== Artify seed demo ===");

// Mots de passe de demo : un par role, hashes bcrypt avec sel random a chaque seed.
$hAdmin    = password_hash('admin2026!',    PASSWORD_BCRYPT);
$hArtisan  = password_hash('artisan2026!',  PASSWORD_BCRYPT);
$hVisiteur = password_hash('visiteur2026!', PASSWORD_BCRYPT);
// Mapping role -> hash, evite de faire un switch dans la boucle des users.
$pwByRole = ['admin' => $hAdmin, 'artisan' => $hArtisan, 'visiteur' => $hVisiteur];

// === 1. Utilisateurs ===========================================================
// UPSERT via ON DUPLICATE KEY : si l'email existe deja on remet a jour, sinon on cree. Garantit l'idempotence.
$sql = "INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, role, ville, telephone, est_actif, avatar_url, bio)
        VALUES (:n,:p,:e,:pw,:r,:v,:t,1,:a,:b)
        ON DUPLICATE KEY UPDATE mot_de_passe=VALUES(mot_de_passe), role=VALUES(role),
          ville=VALUES(ville), telephone=VALUES(telephone), est_actif=1,
          avatar_url=VALUES(avatar_url), bio=VALUES(bio)";
$st = $pdo->prepare($sql);
foreach ($data['users'] as $u) {
    [$nom, $prenom, $email, $role, $ville, $tel] = $u;
    $st->execute([
        ':n' => $nom, ':p' => $prenom, ':e' => $email,
        ':pw' => $pwByRole[$role], ':r' => $role, ':v' => $ville, ':t' => $tel,
        ':a' => 'https://i.pravatar.cc/200?u=' . urlencode($email),
        ':b' => $data['bios'][$email] ?? null,
    ]);
}
p("Utilisateurs upsert : " . count($data['users']));

// Recuperation des ids fraichement crees, indexes par email pour les references croisees plus bas.
// Filtre @artify.fr : on ne ramasse que les comptes de la demo, pas les vrais utilisateurs si la BDD en contient.
$userIds = [];
foreach ($pdo->query("SELECT id, email FROM utilisateur WHERE email LIKE '%@artify.fr'") as $r) {
    $userIds[$r['email']] = (int)$r['id'];
}

// === 2. Artisans ===============================================================
$sql = "INSERT INTO artisan (utilisateur_id, nom_boutique, specialite, description, site_web, instagram, note_moyenne, nb_avis, verifie)
        VALUES (:u,:nb,:sp,:d,:w,:ig,:no,:na,1)
        ON DUPLICATE KEY UPDATE nom_boutique=VALUES(nom_boutique), specialite=VALUES(specialite),
          description=VALUES(description), site_web=VALUES(site_web), instagram=VALUES(instagram),
          note_moyenne=VALUES(note_moyenne), nb_avis=VALUES(nb_avis), verifie=1";
$st = $pdo->prepare($sql);
foreach ($data['artisans'] as $email => $a) {
    if (!isset($userIds[$email])) continue;
    [$nb, $sp, $desc, $w, $ig, $no, $na] = $a;
    $st->execute([':u' => $userIds[$email], ':nb' => $nb, ':sp' => $sp, ':d' => $desc,
                  ':w' => $w, ':ig' => $ig, ':no' => $no, ':na' => $na]);
}
p("Artisans upsert : " . count($data['artisans']));

$artIds = [];
foreach ($pdo->query("SELECT a.id, u.email FROM artisan a JOIN utilisateur u ON u.id = a.utilisateur_id WHERE u.email LIKE '%@artify.fr'") as $r) {
    $artIds[$r['email']] = (int)$r['id'];
}

// === 3. Produits + image principale ===========================================
// picsum + seed : URL d'image deterministe par identifiant, garantit toujours la meme image au reseed.
$pic = 'https://picsum.photos/seed/';
$ckP = $pdo->prepare("SELECT id FROM produit WHERE artisan_id=:a AND nom=:n LIMIT 1");
$upP = $pdo->prepare("UPDATE produit SET categorie_id=:c, description=:d, prix=:p, materiaux=:m, dimensions=:dim, delai_fabrication_jours=:de, stock=:s, est_personnalisable=:pe, est_publie=1 WHERE id=:id");
$inP = $pdo->prepare("INSERT INTO produit (artisan_id, categorie_id, nom, description, prix, materiaux, dimensions, delai_fabrication_jours, stock, est_personnalisable, est_publie) VALUES (?,?,?,?,?,?,?,?,?,?,1)");
$ckI = $pdo->prepare("SELECT id FROM image_produit WHERE produit_id=:p AND ordre=0 LIMIT 1");
$inI = $pdo->prepare("INSERT INTO image_produit (produit_id, url, ordre, est_principale) VALUES (?, ?, 0, 1)");
$produitIds = [];
foreach ($data['produits'] as $pd) {
    [$email, $cat, $nom, $desc, $prix, $mat, $dim, $delai, $stock, $perso, $seed] = $pd;
    $aid = $artIds[$email] ?? null;
    // Skip si l'artisan n'a pas ete cree (cas tres rare, type donnees incoherentes).
    if (!$aid) continue;
    // Check d'existence avant decision INSERT vs UPDATE (pas d'unique key composite sur artisan_id + nom).
    $ckP->execute([':a' => $aid, ':n' => $nom]);
    $id = $ckP->fetchColumn();
    if ($id) {
        $upP->execute([':c'=>$cat, ':d'=>$desc, ':p'=>$prix, ':m'=>$mat, ':dim'=>$dim, ':de'=>$delai, ':s'=>$stock, ':pe'=>$perso, ':id'=>$id]);
        $produitIds[$nom] = (int)$id;
    } else {
        $inP->execute([$aid, $cat, $nom, $desc, $prix, $mat, $dim, $delai, $stock, $perso]);
        $produitIds[$nom] = (int)$pdo->lastInsertId();
    }
    // Image principale : on l'ajoute seulement si elle n'existe pas deja, evite les doublons au reseed.
    $ckI->execute([':p' => $produitIds[$nom]]);
    if (!$ckI->fetchColumn()) {
        $inI->execute([$produitIds[$nom], $pic . $seed . '/600/400']);
    }
}
p("Produits upsert : " . count($data['produits']));

// === 4. Événements (futurs) ===================================================
$sql = "INSERT INTO evenement (artisan_id, titre, description, lieu, ville, date_debut, date_fin, image_url, capacite_max, prix_entree, est_publie)
        SELECT :a1, :t1, :d, :l, :v, :ds1, :df, :img, :cap, :prix, 1 FROM DUAL
        WHERE NOT EXISTS (SELECT 1 FROM evenement WHERE artisan_id=:a2 AND titre=:t2 AND date_debut=:ds2)";
$st = $pdo->prepare($sql);
// Date de reference pour calculer les "+N jours" propres a chaque evenement.
$today = new DateTime('today');
foreach ($data['events'] as $e) {
    [$email, $titre, $desc, $lieu, $ville, $days, $durH, $prix, $cap, $seed] = $e;
    $aid = $artIds[$email] ?? null;
    if (!$aid) continue;
    // Dates relatives (+N jours a 10h), pour garder le seed valide quelle que soit la date d'execution.
    $ds = (clone $today)->modify("+{$days} days")->setTime(10, 0);
    $df = (clone $ds)->modify("+{$durH} hours");
    $dsS = $ds->format('Y-m-d H:i:s');
    $st->execute([
        ':a1' => $aid, ':a2' => $aid, ':t1' => $titre, ':t2' => $titre,
        ':d' => $desc, ':l' => $lieu, ':v' => $ville,
        ':ds1' => $dsS, ':ds2' => $dsS, ':df' => $df->format('Y-m-d H:i:s'),
        ':img' => $pic . $seed . '/800/450', ':cap' => $cap, ':prix' => $prix,
    ]);
}
p("Événements upsert : " . count($data['events']));

// === 5. Galerie ================================================================
$ck = $pdo->prepare("SELECT id FROM galerie WHERE artisan_id=:a AND titre=:t LIMIT 1");
$in = $pdo->prepare("INSERT INTO galerie (artisan_id, produit_id, image_url, titre, description, est_publie) VALUES (?,?,?,?,?,1)");
$nG = 0;
foreach ($data['galerie'] as $g) {
    [$email, $prodNom, $seed, $titre, $desc] = $g;
    $aid = $artIds[$email] ?? null;
    if (!$aid) continue;
    $ck->execute([':a' => $aid, ':t' => $titre]);
    if ($ck->fetchColumn()) continue;
    $in->execute([$aid, $prodNom ? ($produitIds[$prodNom] ?? null) : null,
                  $pic . $seed . '/800/600', $titre, $desc]);
    $nG++;
}
p("Galerie ajoutée : {$nG}");

// === 6. FAQ ====================================================================
$ck = $pdo->prepare("SELECT id FROM faq WHERE question=:q LIMIT 1");
$in = $pdo->prepare("INSERT INTO faq (question, reponse, ordre, est_actif) VALUES (?,?,?,1)");
$up = $pdo->prepare("UPDATE faq SET reponse=:r, ordre=:o, est_actif=1 WHERE id=:id");
$nF = 0;
foreach ($data['faqs'] as $f) {
    [$q, $r, $o] = $f;
    $ck->execute([':q' => $q]);
    $id = $ck->fetchColumn();
    if ($id) { $up->execute([':r' => $r, ':o' => $o, ':id' => $id]); }
    else     { $in->execute([$q, $r, $o]); $nF++; }
}
p("FAQ ajoutées : {$nF}");

// === 7. CGU & Mentions légales =================================================
$id = $pdo->query("SELECT id FROM cgu WHERE version='1.0' LIMIT 1")->fetchColumn();
if ($id) {
    $pdo->prepare("UPDATE cgu SET contenu=:c, date_effet=CURDATE(), est_actif=1 WHERE id=:id")->execute([':c' => $data['cgu'], ':id' => $id]);
    p("CGU mise à jour (id={$id})");
} else {
    $pdo->prepare("INSERT INTO cgu (contenu, version, date_effet, est_actif) VALUES (?, '1.0', CURDATE(), 1)")->execute([$data['cgu']]);
    p("CGU créée");
}
$id = $pdo->query("SELECT id FROM mention_legale LIMIT 1")->fetchColumn();
if ($id) {
    $pdo->prepare("UPDATE mention_legale SET contenu=:c WHERE id=:id")->execute([':c' => $data['mentions'], ':id' => $id]);
    p("Mentions légales mises à jour (id={$id})");
} else {
    $pdo->prepare("INSERT INTO mention_legale (contenu) VALUES (?)")->execute([$data['mentions']]);
    p("Mentions légales créées");
}

// === 8. Commandes + lignes =====================================================
// Pour chaque commande de demo on prend le premier produit publie de l'artisan, simplification volontaire du seeder.
$pickProd = $pdo->prepare("SELECT id, prix FROM produit WHERE artisan_id=? AND est_publie=1 ORDER BY id LIMIT 1");
$ck = $pdo->prepare("SELECT id FROM commande WHERE utilisateur_id=:u AND artisan_id=:a AND adresse_livraison=:adr AND statut=:s LIMIT 1");
$inC = $pdo->prepare("INSERT INTO commande (utilisateur_id, artisan_id, montant_total, statut, adresse_livraison, code_postal, ville_livraison, message_personnalisation) VALUES (?,?,?,?,?,?,?,?)");
$inL = $pdo->prepare("INSERT INTO ligne_commande (commande_id, produit_id, quantite, prix_unitaire) VALUES (?,?,?,?)");
$nC = 0;
foreach ($data['commandes'] as $c) {
    [$ue, $ae, $statut, $adr, $cp, $vil, $msg, $qte] = $c;
    $uid = $userIds[$ue] ?? null; $aid = $artIds[$ae] ?? null;
    if (!$uid || !$aid) continue;
    $pickProd->execute([$aid]);
    $prod = $pickProd->fetch(PDO::FETCH_ASSOC);
    if (!$prod) continue;
    $ck->execute([':u' => $uid, ':a' => $aid, ':adr' => $adr, ':s' => $statut]);
    if ($ck->fetchColumn()) continue;
    $inC->execute([$uid, $aid, round($prod['prix'] * $qte, 2), $statut, $adr, $cp, $vil, $msg]);
    $inL->execute([(int)$pdo->lastInsertId(), $prod['id'], $qte, $prod['prix']]);
    $nC++;
}
p("Commandes ajoutées : {$nC}");

// === 9. Avis ===================================================================
$ck = $pdo->prepare("SELECT id FROM avis WHERE utilisateur_id=:u AND artisan_id=:a AND commentaire=:c LIMIT 1");
$in = $pdo->prepare("INSERT INTO avis (utilisateur_id, artisan_id, note, commentaire) VALUES (?,?,?,?)");
$nA = 0;
foreach ($data['avis'] as $a) {
    [$ue, $ae, $note, $cm] = $a;
    $uid = $userIds[$ue] ?? null; $aid = $artIds[$ae] ?? null;
    if (!$uid || !$aid) continue;
    $ck->execute([':u' => $uid, ':a' => $aid, ':c' => $cm]);
    if ($ck->fetchColumn()) continue;
    $in->execute([$uid, $aid, $note, $cm]); $nA++;
}
p("Avis ajoutés : {$nA}");

// === 10. Contacts ==============================================================
$ck = $pdo->prepare("SELECT id FROM contact WHERE email=:e AND sujet=:s LIMIT 1");
$in = $pdo->prepare("INSERT INTO contact (nom, email, sujet, message, traite) VALUES (?,?,?,?,?)");
$nK = 0;
foreach ($data['contacts'] as $c) {
    [$n, $e, $s, $m, $t] = $c;
    $ck->execute([':e' => $e, ':s' => $s]);
    if ($ck->fetchColumn()) continue;
    $in->execute([$n, $e, $s, $m, $t]); $nK++;
}
p("Messages contact ajoutés : {$nK}");

// === Récap =====================================================================
// Compteur final par table pour valider visuellement que le seed a bien rempli ce qu'on attendait.
p("\n=== Récapitulatif ===");
$tables = ['utilisateur','artisan','produit','evenement','galerie','faq','commande','avis','contact','cgu','mention_legale','image_produit'];
foreach ($tables as $t) {
    $n = (int)$pdo->query("SELECT COUNT(*) FROM `{$t}`")->fetchColumn();
    p(str_pad($t, 18) . " : {$n}");
}
p("evenement futurs   : " . (int)$pdo->query("SELECT COUNT(*) FROM evenement WHERE date_debut > NOW()")->fetchColumn());
p("\nSeed terminé. Supprimez _seed_demo.php et _seed_demo.data.php avant la prod.");
