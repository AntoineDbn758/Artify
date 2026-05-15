# Comment pousser sur GitHub

Le repo local est pret avec un commit initial. Il ne reste qu'a le brancher
sur ton repo GitHub existant et a pousser.

## Option A. Repo GitHub deja existant (avec collaborateurs)

```powershell
cd C:\Users\KARL\OneDrive\Bureau\Artify\_github_push

# Lier le repo local au remote GitHub
git remote add origin https://github.com/<ton-user>/<nom-repo>.git

# Premier push
git push -u origin main
```

Si le repo distant n'est pas vide (commits anterieurs), tu auras besoin de
`git pull origin main --allow-unrelated-histories` avant le push, puis de
resoudre les conflits le cas echeant.

## Option B. Creer le repo GitHub depuis zero

1. Sur https://github.com/new, cree un repo prive (sans README ni .gitignore).
2. Note l'URL HTTPS proposee (ex `https://github.com/user/Artify.git`).
3. Execute les commandes de l'Option A.

## Option C. Avec le CLI GitHub (gh)

```powershell
cd C:\Users\KARL\OneDrive\Bureau\Artify\_github_push
gh auth login    # une seule fois
gh repo create Artify --private --source=. --remote=origin --push
```

## Verification apres push

- Le repo GitHub doit contenir 3 dossiers (`artify`, `artify_docker`) plus
  les fichiers racine (`README.md`, `artify.sql`, `.gitignore`, `PUSH.md`).
- Aucune trace de cles Stripe en clair (`.env` est dans `.gitignore`).
- Le README s'affiche correctement sur la page d'accueil du repo.
