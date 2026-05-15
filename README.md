# Artify

Plateforme web de vente et de promotion pour artisans et createurs.
Projet ISEP 2025-2026.

## Stack

- **Frontend** : HTML / CSS / JavaScript vanilla
- **Backend** : PHP 8.2 + PDO prepare
- **Base de donnees** : MariaDB 10.11 (18 tables)
- **Paiement** : Stripe Checkout (mode test)
- **Conteneurs** : Docker Compose (web + db + phpmyadmin)

## Arborescence

```
.
| artify/                 Site PHP (front public + espace user + artisan + backoffice)
|  |- backoffice/         Pages admin (12 pages)
|  |- includes/           Header, footer, bootstrap, CSRF, auth
|  |- css/                Styles globaux
|  |- *.php               Pages publiques et endpoints
| artify_docker/          Stack Docker (compose, vhost Apache, env)
| artify.sql              Schema BDD + donnees de base (18 tables)
| README.md               Ce fichier
```

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
