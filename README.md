# Artify

Plateforme web de vente et de promotion pour artisans et createurs.
Projet ISEP 2025-2026.

## Stack

- **Frontend** : HTML + CSS (rendu cote serveur, sans JavaScript)
- **Backend** : PHP 8.2 + PDO prepare
- **Base de donnees** : MariaDB 10.11 (18 tables)
- **Paiement** : Stripe Checkout (mode test)
- **Conteneurs** : Docker Compose (web + db + phpmyadmin)

## Arborescence

```
.
|- README.md              Ce fichier (presentation du projet)
|- PUSH.md                Instructions Git pour pousser sur GitHub
|- .gitignore             Fichiers exclus du suivi Git (.env, uploads, etc.)
|- artify.sql             Dump du schema BDD + donnees initiales (18 tables)
|
|- artify/                Code source du site PHP (~ 40 fichiers)
|  |
|  |- includes/           Composants partages a tous les fichiers
|  |   |- bootstrap.php   Demarre la session, charge la BDD, helpers (h(), csrf, role)
|  |   |- header.php      Header HTML commun (logo, nav, recherche)
|  |   |- footer.php      Footer HTML commun (liens FAQ, CGU, mentions)
|  |   |- stripe.php      Wrapper API Stripe pour le paiement (curl, sans SDK)
|  |   |- seed.php        Donnees de demonstration insertables (utile en dev)
|  |
|  |- css/
|  |   |- style.css       Feuille de style globale du site (palette ocre)
|  |
|  |- backoffice/         Espace administrateur (require role='admin')
|  |   |- _header.php     Header admin avec sidebar de navigation
|  |   |- _footer.php     Footer admin
|  |   |- css/admin.css   Styles specifiques au backoffice (dashboard, tables)
|  |   |- index.php       Tableau de bord (statistiques cartes)
|  |   |- users.php       Gestion utilisateurs (role, activer, supprimer)
|  |   |- artisans.php    Validation et gestion des artisans
|  |   |- produits.php    Listing et publication des produits
|  |   |- categories.php  CRUD des categories
|  |   |- evenements.php  Publication des evenements
|  |   |- commandes.php   Workflow des commandes (6 statuts)
|  |   |- avis.php        Moderation des avis clients
|  |   |- faq.php         Edition des questions/reponses FAQ
|  |   |- cgu.php         Edition versionnee des CGU
|  |   |- mentions.php    Edition des mentions legales
|  |   |- contacts.php    Messages contact recus (a traiter)
|  |
|  |- uploads/produits/   Dossier (vide) pour les photos uploadees par les artisans
|  |
|  |- index.php           Page d'accueil (hero + creations a la une + artisans)
|  |- artisans.php        Annuaire des artisans
|  |- artisan.php         Fiche detaillee d'un artisan + ses produits
|  |- creations.php       Catalogue des produits (filtres par categorie)
|  |- produit.php         Fiche detaillee d'un produit (avec bouton Commander)
|  |- evenements.php      Liste des evenements futurs
|  |- evenement.php       Fiche d'un evenement + bouton d'inscription
|  |- galerie.php         Galerie virtuelle (mosaique de creations)
|  |- recherche.php       Recherche par mot-cle (produits + artisans)
|  |- faq.php             Foire aux questions
|  |- contact.php         Formulaire de contact
|  |- cgu.php             Conditions generales d'utilisation
|  |- mentions.php        Mentions legales
|  |
|  |- register_form.php   Formulaire d'inscription (visiteur ou artisan)
|  |- inscription.php     Handler POST de l'inscription (CSRF + BCRYPT)
|  |- login_form.php      Formulaire de connexion
|  |- login.php           Handler POST du login (session securisee)
|  |- logout.php          Destruction de la session
|  |
|  |- profile.php         Profil de l'utilisateur connecte
|  |- profile_edit.php    Edition du profil (avec avatar URL)
|  |- change_password.php Changement de mot de passe
|  |- mes_commandes.php   Historique des commandes du user
|  |
|  |- boutique.php        (Artisan) Tableau de bord de sa boutique
|  |- produit_new.php     (Artisan) Creation d'un produit + upload photo
|  |- produit_edit.php    (Artisan) Modification d'un produit
|  |- produit_delete.php  (Artisan) Suppression d'un produit (avec confirmation)
|  |- evenement_new.php   (Artisan) Creation d'un evenement
|  |
|  |- commande_new.php    Cree une commande + session Stripe Checkout
|  |- commande_success.php  Callback Stripe en cas de paiement reussi
|  |- commande_cancel.php   Callback Stripe en cas d'annulation
|  |
|  |- connexion.php       Connexion PDO a la BDD (env DB_HOST, DB_NAME, ...)
|
|- artify_docker/         Stack Docker pour developpement local
|   |- docker-compose.yml   3 services : web (Apache+PHP), db (MariaDB), pma (phpMyAdmin)
|   |- apache-vhost.conf    Vhost Apache (DocumentRoot /var/www/html + alias /artify)
|   |- .env.example         Variables d'environnement a copier en .env (cles Stripe)
|   |- .gitignore           Force l'exclusion du .env reel (secrets)
```

### Notes sur l'arborescence

- **`includes/`** centralise le code partage par toutes les pages
  (session, BDD, CSRF, helpers d'echappement). Une modification y est
  propagee partout.
- **`backoffice/`** est un sous-site protege ; toutes ses pages commencent
  par `require_role('admin')` charge depuis `_header.php`.
- **`uploads/produits/`** est volontairement vide dans le repo (`.gitkeep`).
  Les fichiers uploades par les artisans ne sont pas suivis par Git.
- **`artify.sql`** est importe automatiquement au premier demarrage du
  conteneur MariaDB (mecanisme `docker-entrypoint-initdb.d`).
- **`artify_docker/.env`** (non versionne) contient les vraies cles Stripe.
  Le `.env.example` montre le format attendu.

## Demarrage rapide

### Prerequis

- Docker Desktop (ou docker engine)
- 4 Go de RAM disponibles
- Ports 80, 3306, 8080 libres

### Lancer le projet

```bash
cd artify_docker
docker compose up -d
```

Le site est ensuite accessible sur :

- `http://127.0.0.1/`           Site Artify
- `http://127.0.0.1:8080/`      phpMyAdmin (root, mot de passe vide)

### Recharger les donnees de demonstration

Pour seeder la base avec des utilisateurs, produits, artisans et evenements
de demonstration :

```
http://127.0.0.1/_seed_demo.php?token=artify-demo
```

Voir le tableau des comptes ci-dessous.

## Comptes de test

| Role     | Email             | Mot de passe   |
|----------|-------------------|----------------|
| admin    | admin@artify.fr   | admin2026!     |
| artisan  | sophie@artify.fr  | artisan2026!   |
| artisan  | lucas@artify.fr   | artisan2026!   |
| artisan  | amelie@artify.fr  | artisan2026!   |
| artisan  | marc@artify.fr    | artisan2026!   |
| visiteur | marie@artify.fr   | visiteur2026!  |
| visiteur | paul@artify.fr    | visiteur2026!  |
| visiteur | julie@artify.fr   | visiteur2026!  |
| visiteur | kevin@artify.fr   | visiteur2026!  |

**A desactiver imperativement avant toute mise en ligne publique.**

## Paiement Stripe (optionnel)

Le paiement est integre via Stripe Checkout en mode test.
Sans cle configuree, le bouton `Commander` affiche un message d'aide.

Pour activer :

1. Creer un compte gratuit sur https://dashboard.stripe.com/register
2. Recuperer les cles de test sur https://dashboard.stripe.com/test/apikeys
3. Creer `artify_docker/.env` (cf `.env.example`) :

```
STRIPE_PUBLISHABLE_KEY=pk_test_xxx
STRIPE_SECRET_KEY=sk_test_xxx
APP_BASE_URL=http://127.0.0.1
```

4. Recharger le conteneur :

```bash
cd artify_docker
docker compose up -d --force-recreate web
```

Carte de test : `4242 4242 4242 4242` (n'importe quelle date future + 3 chiffres CVC).

## Securite

- Mots de passe haches avec BCRYPT (`password_hash` PHP)
- Sessions `httponly` et `samesite=Lax`
- PDO prepared statements partout (zero injection SQL)
- CSRF token sur tous les formulaires POST
- Echappement HTML via `h()` (htmlspecialchars)

## Fonctionnalites principales

### Cote visiteur
Accueil, catalogue, fiches artisans, fiches produits, evenements, galerie,
FAQ, CGU, mentions legales, contact, recherche par mot-cle.

### Cote membre connecte
Inscription, connexion, profil, edition profil, changement mot de passe,
historique de commandes, inscription aux evenements, paiement Stripe.

### Cote artisan
Ma boutique, creation/modification/suppression de produits (avec upload
photo URL ou fichier), creation d'evenements.

### Cote administrateur (backoffice)
Dashboard avec statistiques, gestion utilisateurs (changer role, activer,
supprimer en cascade), gestion artisans / produits / categories,
edition FAQ / CGU / mentions legales (avec versions), moderation
des avis et des messages contact, workflow des commandes (6 statuts).

## Equipe

Sara ISIT, Antoine DABONE, Karl OLIVET,
Timothe BRO, Mokakile NGONO, Nicolas BALIGAND.

## Licence

Projet pedagogique ISEP. Tous droits reserves.
