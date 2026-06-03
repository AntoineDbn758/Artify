# Artify — Tableau des fonctionnalités

> Document remis au client (RDV 2) — récapitulatif des fonctionnalités livrées et techniques utilisées.

## 1. Vue d'ensemble

| Domaine | Statut |
|---|---|
| Architecture MVC | ✅ Mise en place (`/app`) |
| Authentification (inscription, connexion, déconnexion) | ✅ |
| Mot de passe oublié (token sécurisé) | ✅ |
| Force du mot de passe (front + back) | ✅ |
| Accessibilité (dyslexie, contraste, taille police) | ✅ |
| Messagerie interne | ✅ |
| Forum communautaire (catégories + pagination) | ✅ |
| Recherche multicritère | ✅ |
| Pagination | ✅ |
| Sidebar globale | ✅ |
| Backoffice admin complet | ✅ |
| Paiement Stripe Checkout | ✅ |
| Sécurité (CSRF, bcrypt, prepared statements, htmlspecialchars) | ✅ |

## 2. Fonctionnalités utilisateur (front)

| # | Fonctionnalité | Fichier | Notes |
|---|---|---|---|
| 1 | Accueil avec produits / artisans / événements | [index.php](index.php) | Mise en avant des derniers contenus |
| 2 | Catalogue créations + filtres + tri + pagination | [creations.php](creations.php) | Recherche multicritère (catégorie, mots-clés, ville, prix min/max, tri) |
| 3 | Fiche produit + ajout au panier / commande | [produit.php](produit.php) | |
| 4 | Annuaire artisans + fiche détaillée | [artisans.php](artisans.php), [artisan.php](artisan.php) | |
| 5 | Événements à venir / passés / tous | [evenements.php](evenements.php) | Onglets filtre |
| 6 | Inscription événement | [evenement.php](evenement.php) | |
| 7 | Recherche transversale | [recherche.php](recherche.php) | Produits + artisans |
| 8 | Contact (formulaire compact) | [contact.php](contact.php) | 2 colonnes, sans scroll |
| 9 | FAQ accordéon | [faq.php](faq.php) | |
| 10 | Forum (sujets + réponses + catégories + pagination) | [forum.php](forum.php) | MVC — `ForumController` |
| 11 | Messagerie interne (conversations + threads) | [messages.php](messages.php) | MVC — `MessageController` |
| 12 | Mot de passe oublié (lien de reset) | [forgot.php](forgot.php) | MVC — `AuthController`, token SHA-256 valable 1h |
| 13 | Réinitialisation MdP + force MdP | [reset.php](reset.php) | |
| 14 | Profil utilisateur + édition | [profile.php](profile.php), [profile_edit.php](profile_edit.php) | |
| 15 | Mes commandes (historique) | [mes_commandes.php](mes_commandes.php) | |
| 16 | Panneau accessibilité (dyslexie, contraste, taille police) | [js/a11y.js](js/a11y.js) | Préférences persistées (localStorage) |
| 17 | Sidebar navigation contextuelle | [includes/header.php](includes/header.php) | Globale sur toutes les pages |

## 3. Espace artisan

| # | Fonctionnalité | Fichier |
|---|---|---|
| 18 | Boutique / dashboard artisan | [boutique.php](boutique.php) |
| 19 | Création produit | [produit_new.php](produit_new.php) |
| 20 | Édition / suppression produit | [produit_edit.php](produit_edit.php), [produit_delete.php](produit_delete.php) |
| 21 | Création événement | [evenement_new.php](evenement_new.php) |
| 22 | Réception commandes | [boutique.php](boutique.php) |

## 4. Backoffice admin

| # | Fonctionnalité | Fichier |
|---|---|---|
| 23 | Dashboard | [backoffice/index.php](backoffice/index.php) |
| 24 | Gestion utilisateurs (liste + édition complète) | [backoffice/users.php](backoffice/users.php), [backoffice/user_edit.php](backoffice/user_edit.php) |
| 25 | Modifier tous les champs d'un utilisateur (nom, prénom, email, ville, rôle, actif) | [backoffice/user_edit.php](backoffice/user_edit.php) |
| 26 | Gestion artisans | [backoffice/artisans.php](backoffice/artisans.php) |
| 27 | Gestion produits | [backoffice/produits.php](backoffice/produits.php) |
| 28 | Gestion catégories | [backoffice/categories.php](backoffice/categories.php) |
| 29 | Gestion événements (publication, suppression) | [backoffice/evenements.php](backoffice/evenements.php) |
| 30 | Gestion commandes | [backoffice/commandes.php](backoffice/commandes.php) |
| 31 | Gestion avis | [backoffice/avis.php](backoffice/avis.php) |
| 32 | Gestion contacts reçus | [backoffice/contacts.php](backoffice/contacts.php) |
| 33 | Gestion FAQ | [backoffice/faq.php](backoffice/faq.php) |
| 34 | Édition CGU / mentions légales | [backoffice/cgu.php](backoffice/cgu.php), [backoffice/mentions.php](backoffice/mentions.php) |

## 5. Architecture MVC

```
artify/
├── app/                          ← Couche MVC
│   ├── bootstrap.php             ← Autoloader PSR-4
│   ├── Core/
│   │   ├── Database.php          ← Singleton PDO
│   │   ├── Controller.php        ← Controller de base
│   │   ├── Model.php             ← Active Record minimaliste
│   │   ├── Router.php            ← Routeur HTTP
│   │   └── View.php              ← Renderer de vues
│   ├── Models/
│   │   ├── User.php              ← + validation force MdP
│   │   ├── Message.php           ← Messagerie interne
│   │   ├── ForumSujet.php
│   │   ├── ForumMessage.php
│   │   └── PasswordReset.php     ← Tokens sécurisés
│   ├── Controllers/
│   │   ├── MessageController.php
│   │   ├── ForumController.php
│   │   └── AuthController.php
│   └── Views/
│       ├── layouts/main.php
│       ├── messages/{index,show}.php
│       ├── forum/{index,show,create}.php
│       └── auth/{forgot,reset,reset_invalid,forgot_sent}.php
│
├── migrations/
│   └── 001_forum_and_reset.sql   ← Tables forum_sujet, forum_message, password_reset
│
├── messages.php / forum.php /    ← Entry-points qui délèguent aux Controllers
├── forgot.php / reset.php
│
└── (pages legacy : index, creations, contact, etc.)
```

## 6. Sécurité

| Mesure | Implémentation |
|---|---|
| Mots de passe | `password_hash(PASSWORD_BCRYPT)` + `password_verify` |
| Force MdP | 8+ car., 1 maj, 1 min, 1 chiffre, 1 spécial — front (JS live meter) + back (`User::validatePassword`) |
| Sessions | `httponly`, `samesite=Lax`, `session_regenerate_id` après login |
| CSRF | Token aléatoire 32 hex + comparaison `hash_equals` |
| Injection SQL | PDO prepared statements partout (jamais de concat) |
| XSS | `htmlspecialchars(..., ENT_QUOTES, UTF-8)` (helper `h()`) |
| Open redirect | Regex de validation du paramètre `next` |
| Mot de passe oublié | Token SHA-256 stocké hashé en BDD, TTL 1h, usage unique |

## 7. Accessibilité

| Critère | Implémentation |
|---|---|
| Skip link | « Aller au contenu principal » au focus clavier |
| Focus visible | `outline:3px solid var(--ocre)` sur tous les éléments interactifs |
| Police dyslexique | OpenDyslexic activable via le panneau A+ |
| Haut contraste | Mode sombre haute lisibilité activable |
| Réduction d'animations | Respect `prefers-reduced-motion` + override manuel |
| Taille du texte | 4 niveaux (A-, A, A+, A++) persistés en localStorage |
| Lang | `<html lang="fr">` |
| `aria-label` sur les contrôles non textuels (boutons icônes, recherche) |

## 8. Technologies

- **PHP 8.x** (typage strict des paramètres, match expression)
- **PDO MySQL** (prepared statements)
- **MariaDB / MySQL** (utf8mb4_unicode_ci)
- **HTML5 / CSS3** (custom properties, grid, flexbox)
- **JavaScript ES5+** vanilla, non-intrusif (progressive enhancement)
- **Stripe Checkout** (paiement)
- **Docker** (déploiement)

## 9. Migrations à exécuter

```bash
# Une seule fois sur la BDD existante
mysql -u root artify < migrations/001_forum_and_reset.sql
```

## 10. URLs de démo (nouvelles)

- `/messages.php` — messagerie
- `/forum.php` — forum
- `/forum.php?action=new` — nouveau sujet
- `/forum.php?action=show&id=X` — sujet détaillé
- `/forgot.php` — mot de passe oublié
- `/reset.php?token=...` — réinitialisation
- `/backoffice/user_edit.php?id=X` — édition complète utilisateur
