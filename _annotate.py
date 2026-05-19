"""
_annotate.py - injecte un commentaire d'en-tete (et quelques inline) dans
chaque fichier source du repo. Ton naturel, pas d'emoji, pas de tiret long.
"""
import os, re

ROOT = os.path.dirname(os.path.abspath(__file__))

# (chemin relatif depuis ROOT, texte du commentaire d'en-tete)
HEADERS = {
    # ===== Racine =====
    "artify.sql": (
        "Dump SQL initial de la base 'artify'. Importe automatiquement au "
        "premier demarrage du conteneur MariaDB grace au mecanisme "
        "docker-entrypoint-initdb.d. Contient les 18 tables (utilisateur, "
        "artisan, produit, categorie, evenement, ...) et quelques lignes "
        "de demarrage (categories de base, FAQ initiale).",
        "sql"
    ),

    # ===== artify/ : connexion BDD =====
    "artify/connexion.php": (
        "Ouvre la connexion PDO a la base. Tous les autres scripts l'incluent "
        "via require_once. Le DSN est construit a partir des variables "
        "d'environnement (DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD) "
        "fixees dans docker-compose. Permet de switcher facilement entre "
        "local Docker, machine de dev, ou prod sans toucher au code.",
        "php"
    ),

    # ===== includes/ : briques partagees =====
    "artify/includes/bootstrap.php": (
        "Le bootstrap de toutes les pages : demarre la session avec des "
        "cookies httponly + samesite, ouvre la BDD, definit les helpers "
        "qu'on utilise partout. Notamment : h() pour echapper du HTML, "
        "csrf_token() / csrf_field() / csrf_check() pour proteger les "
        "formulaires, require_login() et require_role() pour bloquer "
        "l'acces selon le profil utilisateur. Chaque page commence par "
        "require_once includes/bootstrap.php.",
        "php"
    ),
    "artify/includes/header.php": (
        "Header HTML commun a toutes les pages publiques : logo Artify, "
        "navigation principale, barre de recherche, boutons de connexion "
        "ou avatar si l'utilisateur est deja loggue. La nav est sticky "
        "(reste en haut au scroll) et reagit au responsive via media "
        "queries dans style.css.",
        "php"
    ),
    "artify/includes/footer.php": (
        "Footer commun. Reprend exactement le meme composant logo que le "
        "header pour la coherence visuelle. Liens utilitaires (FAQ, CGU, "
        "Mentions, Contact) plus le copyright.",
        "php"
    ),
    "artify/includes/stripe.php": (
        "Wrapper minimal autour de l'API Stripe. Volontairement sans SDK "
        "officiel pour ne pas ajouter de dependance composer : on parle "
        "directement a https://api.stripe.com via curl en envoyant des "
        "donnees form-urlencoded. Fonctions principales : stripe_configured() "
        "pour savoir si les cles d'env sont presentes, stripe_create_checkout() "
        "pour ouvrir une session Stripe Checkout. Les cles sont lues depuis "
        "STRIPE_PUBLISHABLE_KEY et STRIPE_SECRET_KEY.",
        "php"
    ),
    "artify/includes/seed.php": (
        "Utilitaire de seed reutilisable. Pas charge en production, sert "
        "uniquement pour repeupler la base de demo en environnement de "
        "developpement.",
        "php"
    ),

    # ===== Pages publiques =====
    "artify/index.php": (
        "Page d'accueil. Compose le hero editorial avec un appel a l'action, "
        "puis affiche les creations recentes et les artisans en vedette. "
        "Toutes les requetes utilisent un LEFT JOIN sur image_produit pour "
        "recuperer la photo principale directement.",
        "php"
    ),
    "artify/creations.php": (
        "Catalogue de toutes les creations publiees. Supporte un filtre par "
        "categorie via le parametre GET ?cat=ID. Limite a 60 produits par "
        "page pour eviter les listes interminables, suffisant tant qu'on "
        "reste sur le marche francais.",
        "php"
    ),
    "artify/produit.php": (
        "Fiche detaillee d'un produit. Affiche la photo principale, les "
        "informations vendeur (artisan), le prix, les materiaux, les "
        "dimensions et le stock. Le bouton 'Commander' n'apparait que si "
        "l'utilisateur est connecte et si stock > 0. Si visiteur anonyme, "
        "redirige vers login_form.php avec ?next= pour revenir ici apres "
        "connexion.",
        "php"
    ),
    "artify/artisans.php": (
        "Annuaire des artisans actifs. Chaque artisan a une photo d'atelier "
        "choisie selon sa specialite (mapping en debut de fichier vers des "
        "photos Unsplash thematiques). Affiche aussi sa note moyenne et le "
        "nombre d'avis si > 0.",
        "php"
    ),
    "artify/artisan.php": (
        "Fiche d'un artisan : description, ville, site web, instagram, note "
        "moyenne et liste de ses produits publies. La photo d'illustration "
        "varie selon la specialite (Bijouterie, Ceramique, Textile, ...).",
        "php"
    ),
    "artify/evenements.php": (
        "Liste des evenements futurs uniquement (clause WHERE date_debut >= "
        "NOW()). Chaque evenement a une photo, un lieu, une date et un prix "
        "d'entree (souvent gratuit).",
        "php"
    ),
    "artify/evenement.php": (
        "Fiche d'un evenement. Affiche les details + un bouton pour s'inscrire "
        "(insertion dans la table inscription_evenement). Necessite un user "
        "connecte ; sinon redirection vers la page de login.",
        "php"
    ),
    "artify/galerie.php": (
        "Galerie virtuelle : mosaique de visuels des creations soumis par les "
        "artisans. Les images sont stockees dans la table galerie avec leur "
        "URL et un titre/description optionnels.",
        "php"
    ),
    "artify/recherche.php": (
        "Recherche par mot-cle. Cherche le terme dans le nom du produit, sa "
        "description, son materiau et le nom de boutique de l'artisan via "
        "des LIKE en SQL. Les recherches sont aussi journalisees dans "
        "recherche_log pour stats futures.",
        "php"
    ),
    "artify/faq.php": (
        "Page FAQ. Lit les questions/reponses depuis la table faq, filtrees "
        "sur est_actif=1 et triees par champ ordre. L'admin peut editer le "
        "contenu depuis le backoffice sans toucher au code.",
        "php"
    ),
    "artify/contact.php": (
        "Formulaire de contact. POST : stocke un nouveau message dans la "
        "table contact avec traite=0. L'admin retrouve ces messages dans "
        "backoffice/contacts.php pour les traiter.",
        "php"
    ),
    "artify/cgu.php": (
        "Page d'affichage des Conditions Generales d'Utilisation. Charge "
        "la version active (est_actif=1) la plus recente depuis la table "
        "cgu. Versionning gere depuis le backoffice.",
        "php"
    ),
    "artify/mentions.php": (
        "Page des mentions legales. Une seule ligne en BDD (table "
        "mention_legale), mise a jour depuis le backoffice.",
        "php"
    ),

    # ===== Authentification =====
    "artify/register_form.php": (
        "Formulaire d'inscription (le visuel). Le POST est traite par "
        "inscription.php. Propose deux types de comptes : visiteur (par "
        "defaut) ou artisan (cree aussi automatiquement une boutique liee).",
        "php"
    ),
    "artify/inscription.php": (
        "Handler du POST d'inscription. Verifie l'unicite de l'email, hash "
        "le mot de passe en BCRYPT, cree le compte. Si le role choisi est "
        "'artisan', cree aussi automatiquement une fiche dans la table "
        "artisan (nom_boutique par defaut = prenom + nom).",
        "php"
    ),
    "artify/login_form.php": (
        "Formulaire de connexion (visuel). Le POST est traite par login.php. "
        "Accepte un parametre next= pour rediriger l'utilisateur vers la "
        "page qu'il voulait initialement consulter apres la connexion.",
        "php"
    ),
    "artify/login.php": (
        "Handler du POST de connexion. password_verify() pour comparer le "
        "mot de passe au hash BCRYPT stocke. En cas de succes, regenere "
        "l'identifiant de session (anti session-fixation) puis remplit "
        "$_SESSION avec id, nom, role.",
        "php"
    ),
    "artify/logout.php": (
        "Detruit la session courante puis redirige vers l'accueil. Court "
        "et simple : session_destroy() + header('Location: ...').",
        "php"
    ),

    # ===== Espace utilisateur =====
    "artify/profile.php": (
        "Page de profil de l'utilisateur connecte. Affiche son avatar, ses "
        "infos personnelles, son role, et propose des actions (modifier "
        "profil, changer mot de passe, voir sa boutique si artisan, "
        "acceder au backoffice si admin).",
        "php"
    ),
    "artify/profile_edit.php": (
        "Formulaire d'edition du profil. L'email n'est pas modifiable "
        "(c'est la cle d'identification). On peut changer nom, prenom, "
        "ville, telephone, bio, URL de l'avatar.",
        "php"
    ),
    "artify/change_password.php": (
        "Changement du mot de passe. Demande l'ancien mot de passe pour "
        "verification avant d'accepter le nouveau. Le hash est regenere "
        "avec password_hash().",
        "php"
    ),
    "artify/mes_commandes.php": (
        "Historique des commandes de l'utilisateur connecte. Triees de la "
        "plus recente a la plus ancienne. Statut affiche en couleur "
        "(en attente, confirmee, en fabrication, expediee, livree, "
        "annulee).",
        "php"
    ),

    # ===== Espace artisan =====
    "artify/boutique.php": (
        "Tableau de bord de l'artisan connecte. Liste ses produits, ses "
        "evenements et ses ventes. Boutons pour ajouter un nouveau produit "
        "ou un nouvel evenement.",
        "php"
    ),
    "artify/produit_new.php": (
        "Creation d'un produit pour l'artisan. Le formulaire accepte une "
        "URL d'image externe OU un upload de fichier (jpg/png/webp, max "
        "5 Mo, stocke dans uploads/produits/). La photo est ajoutee dans "
        "image_produit avec est_principale=1.",
        "php"
    ),
    "artify/produit_edit.php": (
        "Edition d'un produit existant. Verifie au prealable que le produit "
        "appartient bien a l'artisan connecte (anti-IDOR). Permet aussi de "
        "remplacer la photo principale.",
        "php"
    ),
    "artify/produit_delete.php": (
        "Suppression d'un produit. Demande confirmation. Verifie aussi le "
        "ownership avant de supprimer. Les images liees (image_produit) "
        "sont supprimees en cascade via ON DELETE CASCADE.",
        "php"
    ),
    "artify/evenement_new.php": (
        "Creation d'un evenement par l'artisan : titre, description, lieu, "
        "ville, date debut, date fin optionnelle, capacite max, prix "
        "d'entree. L'evenement apparait ensuite dans evenements.php.",
        "php"
    ),

    # ===== Paiement Stripe =====
    "artify/commande_new.php": (
        "Point d'entree du parcours de paiement. Cree une commande "
        "'en_attente' dans la BDD, demande a Stripe une URL de Checkout "
        "(carte test 4242 4242 4242 4242 en mode test), puis redirige le "
        "client vers cette URL. Stripe gere tout l'affichage du formulaire "
        "carte, on n'a rien a coder cote front.",
        "php"
    ),
    "artify/commande_success.php": (
        "Callback de retour Stripe en cas de paiement reussi. On verifie "
        "cote serveur que la session Stripe a bien payment_status=paid (on "
        "ne fait jamais confiance a une simple redirection URL). Si OK : "
        "passe la commande en 'confirmee' et decremente le stock.",
        "php"
    ),
    "artify/commande_cancel.php": (
        "Callback de retour Stripe si l'utilisateur annule. La commande "
        "est marquee 'annulee'. Aucun montant n'a ete debite (Stripe ne "
        "capture qu'apres confirmation cote acheteur).",
        "php"
    ),

    # ===== Seed demo =====
    "artify/_seed_demo.php": (
        "Script de seed pour repeupler la base avec un jeu de donnees de "
        "demonstration (utilisateurs, artisans, produits, evenements, "
        "FAQ, ...). Idempotent : on peut le relancer sans casser, les "
        "INSERTs verifient l'existence avant. Acces protege par un token "
        "dans l'URL. A SUPPRIMER avant toute mise en production.",
        "php"
    ),
    "artify/_seed_demo.data.php": (
        "Donnees de demo brutes : tableaux PHP listant les utilisateurs, "
        "artisans, produits, evenements, etc. Pas de logique ici, juste "
        "des arrays. _seed_demo.php les boucle pour les inserer.",
        "php"
    ),

    # ===== Backoffice =====
    "artify/backoffice/_header.php": (
        "Header du backoffice administrateur : sidebar de navigation avec "
        "les 12 sections (Dashboard, Users, Artisans, Produits, ...) et "
        "un appel a require_role('admin') qui bloque l'acces aux "
        "non-admins. Charge admin.css.",
        "php"
    ),
    "artify/backoffice/_footer.php": (
        "Footer du backoffice. Vide ou presque, juste pour fermer "
        "proprement le HTML.",
        "php"
    ),
    "artify/backoffice/index.php": (
        "Dashboard : cartes de statistiques (nombre d'utilisateurs par "
        "role, nombre de produits, nombre de commandes par statut, "
        "messages contact non traites, ...). Vue d'ensemble rapide de la "
        "plateforme.",
        "php"
    ),
    "artify/backoffice/users.php": (
        "Gestion des utilisateurs. Liste avec filtres par role et recherche "
        "par email. Actions par ligne : changer le role, activer/desactiver "
        "le compte, supprimer (suppression en cascade manuelle car les FK "
        "vers avis, commande, favori, etc. empechent un DELETE direct).",
        "php"
    ),
    "artify/backoffice/artisans.php": (
        "Validation et gestion des artisans. Permet de marquer un artisan "
        "comme verifie (badge sur sa fiche publique) et de voir ses "
        "statistiques.",
        "php"
    ),
    "artify/backoffice/produits.php": (
        "Liste de tous les produits avec filtres par categorie et artisan. "
        "Toggle de publication (est_publie 0/1) sans suppression definitive.",
        "php"
    ),
    "artify/backoffice/categories.php": (
        "CRUD complet des categories (Bijouterie, Ceramique, Textile, ...). "
        "Le slug est genere automatiquement a partir du nom.",
        "php"
    ),
    "artify/backoffice/evenements.php": (
        "Gestion des evenements : publication, depublication, suppression. "
        "Bouton pour voir la liste des inscrits par evenement.",
        "php"
    ),
    "artify/backoffice/faq.php": (
        "Edition de la FAQ. Ajout / modification inline / suppression / "
        "reordering via le champ ordre. Active ou desactive une question "
        "sans la supprimer.",
        "php"
    ),
    "artify/backoffice/cgu.php": (
        "Gestion versionnee des CGU. Creer une nouvelle version desactive "
        "automatiquement les anciennes. La page publique cgu.php affiche "
        "toujours la version active la plus recente.",
        "php"
    ),
    "artify/backoffice/mentions.php": (
        "Edition de l'unique entree de mention_legale. Pas de versionning "
        "ici, contrairement aux CGU.",
        "php"
    ),
    "artify/backoffice/contacts.php": (
        "Liste des messages de contact recus, filtrable par statut "
        "(traite / non traite). Bouton 'marquer comme traite' pour clore "
        "un ticket.",
        "php"
    ),
    "artify/backoffice/commandes.php": (
        "Vue globale des commandes. Permet de faire passer une commande "
        "d'un statut a l'autre dans le workflow : en_attente -> confirmee "
        "-> en_fabrication -> expediee -> livree (ou annulee a tout "
        "moment). Tres utile pour le suivi cote artisan ou support.",
        "php"
    ),
    "artify/backoffice/avis.php": (
        "Moderation des avis clients. Permet de supprimer un avis "
        "inapproprie. Quand un avis est supprime, on recalcule la note "
        "moyenne et le nb_avis de l'artisan associe.",
        "php"
    ),

    # ===== CSS =====
    "artify/css/style.css": (
        "Feuille de style globale du site public. Definit la palette ocre/"
        "cuivre dans :root (variables CSS), la nav sticky, les composants "
        "(card, button, badge, form), et les media queries pour le "
        "responsive (4 breakpoints : 1100, 900, 760, 480px).",
        "css"
    ),
    "artify/backoffice/css/admin.css": (
        "Styles specifiques au backoffice. Reutilise les variables de "
        "style.css mais ajoute le layout dashboard (sidebar fixe + "
        "contenu droite scrollable), les tables zebra striped, et les "
        "boutons d'action colories selon le verbe (success/danger/warn).",
        "css"
    ),

    # ===== Docker =====
    "artify_docker/docker-compose.yml": (
        "Stack Docker pour le developpement local. Trois services qui "
        "tournent ensemble : 'web' (Apache 2 + PHP 8.2 servant le code "
        "PHP), 'db' (MariaDB 10.11 avec le dump artify.sql importe au "
        "premier demarrage), 'pma' (phpMyAdmin sur le port 8080 pour "
        "consulter la base facilement). Les variables d'environnement "
        "DB_* et STRIPE_* sont injectees dans le conteneur web pour que "
        "connexion.php et includes/stripe.php les retrouvent. Le code "
        "source est monte en bind volume (les modifs sont visibles "
        "instantanement, sans rebuild).",
        "yml"
    ),
    "artify_docker/apache-vhost.conf": (
        "Configuration du virtual host Apache pour le conteneur web. "
        "DocumentRoot pointe sur /var/www/html (= le bind mount du code). "
        "AllowOverride All active la prise en compte des .htaccess. Un "
        "alias /artify est aussi defini pour compat avec d'anciens liens.",
        "conf"
    ),
}


def inject(rel_path, comment, kind):
    """Ajoute le commentaire d'en-tete s'il n'est pas deja present."""
    p = os.path.join(ROOT, rel_path)
    if not os.path.exists(p):
        return False
    with open(p, encoding="utf-8") as f:
        src = f.read()

    # Wrap au format ad hoc
    if kind == "sql":
        block = "-- " + "\n-- ".join(_wrap(comment, 78)) + "\n\n"
        # detecter si deja commente
        if src.startswith("-- " + comment.split('.')[0][:40]):
            return False
        new = block + src
    elif kind == "css":
        block = "/*\n" + "\n".join("  " + l for l in _wrap(comment, 76)) + "\n*/\n\n"
        if src.lstrip().startswith("/*\n  " + comment[:40]):
            return False
        new = block + src
    elif kind == "yml":
        block = "# " + "\n# ".join(_wrap(comment, 78)) + "\n\n"
        if src.startswith("# " + comment[:40]):
            return False
        new = block + src
    elif kind == "conf":
        block = "# " + "\n# ".join(_wrap(comment, 78)) + "\n\n"
        if src.startswith("# " + comment[:40]):
            return False
        new = block + src
    elif kind == "php":
        # Insertion juste apres <?php
        if not src.startswith("<?php"):
            return False
        block = "/**\n" + "\n".join(" * " + l for l in _wrap(comment, 75)) + "\n */\n"
        # detecter doublon
        if "/**\n * " + comment[:40] in src:
            return False
        # remplacer "<?php\n" par "<?php\n/** ... */\n"
        new = re.sub(r"^<\?php\s*\n?", "<?php\n\n" + block + "\n", src, count=1)
    else:
        return False

    if new != src:
        with open(p, "w", encoding="utf-8") as f:
            f.write(new)
        return True
    return False


def _wrap(text, width):
    """Coupe le texte en lignes de width max, sans casser les mots."""
    words = text.split()
    lines, cur = [], ""
    for w in words:
        if len(cur) + 1 + len(w) > width and cur:
            lines.append(cur)
            cur = w
        else:
            cur = (cur + " " + w).strip()
    if cur:
        lines.append(cur)
    return lines


def main():
    done, skipped = [], []
    for path, (comment, kind) in HEADERS.items():
        if inject(path, comment, kind):
            done.append(path)
        else:
            skipped.append(path)
    print(f"\nAnnotes : {len(done)}")
    for p in done:
        print("  +", p)
    if skipped:
        print(f"\nIgnores ({len(skipped)}) :")
        for p in skipped:
            print("  -", p)


if __name__ == "__main__":
    main()
