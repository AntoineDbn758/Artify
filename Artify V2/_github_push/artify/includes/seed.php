<?php

/**
 * Utilitaire de seed reutilisable. Pas charge en production, sert uniquement
 * pour repeupler la base de demo en environnement de developpement.
 */

// includes/seed.php - initialise des données de démo si nécessaire.
// Idempotent : safe à appeler à chaque chargement, ne fait rien si les
// données réelles sont déjà en place.
//
// Critère de déclenchement :
//   - Le user admin#1 a un mot de passe placeholder (non bcrypt valide)
//   - OU il n'y a aucun événement à venir dans la base
//
// Conventions de mots de passe (alignées sur _seed_demo.php) :
//   admin    -> admin2026!
//   artisan  -> artisan2026!
//   visiteur -> visiteur2026!

function ensure_demo_seed(PDO $pdo): void {
    try {
        // 1) Réparer les hashs placeholder
        $row = $pdo->query("SELECT id, email, mot_de_passe FROM utilisateur")->fetchAll();
        foreach ($row as $u) {
            $h = $u['mot_de_passe'] ?? '';
            // Hashes seed style "$2y$12$examplehashX" sont trop courts (~25 chars)
            // -> bcrypt valide fait ~60 chars. On détecte les placeholders.
            if (strlen($h) < 50) {
                // Récupère le rôle pour déterminer le mot de passe à poser
                $st2 = $pdo->prepare("SELECT role FROM utilisateur WHERE id = ?");
                $st2->execute([(int)$u['id']]);
                $role = $st2->fetchColumn() ?: 'visiteur';
                $pwdMap = [
                    'admin'    => 'admin2026!',
                    'artisan'  => 'artisan2026!',
                    'visiteur' => 'visiteur2026!',
                ];
                $pwd = $pwdMap[$role] ?? 'visiteur2026!';
                $newHash = password_hash($pwd, PASSWORD_BCRYPT);
                $pdo->prepare("UPDATE utilisateur SET mot_de_passe = ? WHERE id = ?")
                    ->execute([$newHash, (int)$u['id']]);
            }
        }

        // 2) Ajouter quelques événements futurs si la table est vide
        $nb_evt = (int)$pdo->query("SELECT COUNT(*) FROM evenement")->fetchColumn();
        if ($nb_evt === 0) {
            $artisans = $pdo->query("SELECT id, nom_boutique FROM artisan ORDER BY id LIMIT 3")->fetchAll();
            $base = strtotime('+10 days');
            $events = [
                ['titre' => "Atelier découverte céramique",
                 'desc'  => "Une journée immersive autour du grès chamotté : tournage, émaillage, cuisson.",
                 'lieu'  => "Atelier Lucas Céramiques", 'ville' => 'Lyon',
                 'prix'  => 35.00, 'cap' => 10, 'idx' => 1],
                ['titre' => "Marché des créateurs d'hiver",
                 'desc'  => "Vente directe avec une trentaine d'artisans sélectionnés.",
                 'lieu'  => "Halle Saint-Germain", 'ville' => 'Paris',
                 'prix'  => 0.00, 'cap' => null, 'idx' => 0],
                ['titre' => "Initiation au tissage",
                 'desc'  => "Apprenez les bases du tissage main : ourdissage, croisement et finitions.",
                 'lieu'  => "Atelier Fils & Trame", 'ville' => 'Bordeaux',
                 'prix'  => 45.00, 'cap' => 8, 'idx' => 2],
            ];
            $st = $pdo->prepare(
                "INSERT INTO evenement (artisan_id, titre, description, lieu, ville, date_debut, date_fin,
                                        capacite_max, prix_entree, est_publie)
                 VALUES (?,?,?,?,?,?,?,?,?,1)");
            foreach ($events as $i => $e) {
                if (!isset($artisans[$e['idx']])) continue;
                $start = date('Y-m-d H:i:s', $base + $i * 86400 * 7);
                $end   = date('Y-m-d H:i:s', $base + $i * 86400 * 7 + 3 * 3600);
                $st->execute([
                    (int)$artisans[$e['idx']]['id'], $e['titre'], $e['desc'],
                    $e['lieu'], $e['ville'], $start, $end,
                    $e['cap'], $e['prix'],
                ]);
            }
        }

        // 3) Galerie : 3 entrées si vide
        $nb_g = (int)$pdo->query("SELECT COUNT(*) FROM galerie")->fetchColumn();
        if ($nb_g === 0) {
            $rows = $pdo->query(
              "SELECT a.id AS aid, p.id AS pid, p.nom FROM produit p JOIN artisan a ON a.id = p.artisan_id LIMIT 3"
            )->fetchAll();
            $st = $pdo->prepare(
              "INSERT INTO galerie (artisan_id, produit_id, image_url, titre, description, est_publie)
               VALUES (?,?,?,?,?,1)");
            foreach ($rows as $r) {
                $st->execute([$r['aid'], $r['pid'], '', $r['nom'], 'Pièce mise en avant dans la galerie publique.']);
            }
        }
    } catch (\Throwable $e) {
        // silencieux - on ne veut pas bloquer le site sur une erreur de seed
        error_log('[seed] ' . $e->getMessage());
    }
}
