# Artify — Plateforme des Créateurs

Projet réalisé dans le cadre du cours de **Développement Web** à l'ISEP.

Artify est une plateforme web dédiée aux artisans et créateurs, permettant de découvrir des œuvres, des artisans, des événements et de gérer son espace personnel.

---

## Structure du projet

```
ARTIFY/
├── index.html        # Page principale — contient l'ensemble du markup et des pages
├── css/
│   └── styles.css    # Feuille de styles extraite du fichier original
└── js/
    └── main.js       # Scripts d'interaction (navigation, onglets, FAQ, chips, sidebar)
```

---

## Pages disponibles

| Page | Description |
|---|---|
| Accueil | Présentation de la plateforme et mise en avant des créateurs |
| Catalogue | Exploration des œuvres avec filtres par catégorie |
| Artisans | Liste des créateurs référencés sur la plateforme |
| Événements | Agenda des événements artisanaux |
| Galerie | Galerie visuelle des créations |
| Connexion / Inscription | Authentification et création de compte |
| Mot de passe oublié | Réinitialisation du mot de passe |
| Profil | Espace personnel de l'utilisateur connecté |
| Modifier le profil | Formulaire de mise à jour des informations personnelles |
| FAQ | Foire aux questions |
| Contact | Formulaire de contact |
| Mentions légales | Informations légales |
| Back-office | Interface d'administration de la plateforme |

---

## Technologies utilisées

- **HTML5** — Structure et contenu
- **CSS3** — Mise en page et design (variables CSS, Flexbox, animations)
- **JavaScript Vanilla** — Interactions dynamiques (navigation SPA, filtres, sidebar)
- **Google Fonts** — Playfair Display & DM Sans

---

## Lancer le projet

Aucune dépendance à installer. Il suffit d'ouvrir `index.html` dans un navigateur.

```bash
# Ou avec un serveur local (recommandé)
npx serve .
```
