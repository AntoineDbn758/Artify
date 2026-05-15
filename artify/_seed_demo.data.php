<?php
/**
 * Données utilisées par _seed_demo.php (séparé pour rester sous 300 lignes).
 * À supprimer en même temps que _seed_demo.php avant la mise en prod.
 */

declare(strict_types=1);

return [
    // - Bios par email (utilisateur) -
    'bios' => [
        'admin@artify.fr'  => 'Compte administrateur Artify.',
        'sophie@artify.fr' => 'Bijoutière passionnée installée à Paris depuis 2015. Travail de l\'or et des pierres fines.',
        'lucas@artify.fr'  => 'Céramiste à Lyon. Tournage et émaillage haute température.',
        'amelie@artify.fr' => 'Tisseuse et brodeuse. Textiles contemporains aux fibres naturelles.',
        'marc@artify.fr'   => 'Ébéniste à Nantes. Mobilier sur mesure en bois massif issu de forêts gérées durablement.',
        'marie@artify.fr'  => 'Amatrice de pièces uniques et de céramique.',
        'paul@artify.fr'   => 'Collectionneur d\'objets artisanaux.',
        'julie@artify.fr'  => 'Toujours à la recherche d\'idées cadeaux originales.',
        'kevin@artify.fr'  => 'Curieux des métiers d\'art.',
    ],

    // - Utilisateurs : [nom, prenom, email, role, ville, telephone] -
    'users' => [
        ['Admin',      'Système', 'admin@artify.fr',  'admin',    'Paris',     '+33 1 23 45 67 89'],
        ['Martin',     'Sophie',  'sophie@artify.fr', 'artisan',  'Paris',     '+33 6 12 34 56 78'],
        ['Renard',     'Lucas',   'lucas@artify.fr',  'artisan',  'Lyon',      '+33 6 23 45 67 89'],
        ['Tisserand',  'Amélie',  'amelie@artify.fr', 'artisan',  'Bordeaux',  '+33 6 34 56 78 90'],
        ['Boisseau',   'Marc',    'marc@artify.fr',   'artisan',  'Nantes',    '+33 6 45 67 89 01'],
        ['Dupont',     'Marie',   'marie@artify.fr',  'visiteur', 'Paris',     '+33 6 11 22 33 44'],
        ['Lemoine',    'Paul',    'paul@artify.fr',   'visiteur', 'Marseille', '+33 6 22 33 44 55'],
        ['Garnier',    'Julie',   'julie@artify.fr',  'visiteur', 'Toulouse',  '+33 6 33 44 55 66'],
        ['Robert',     'Kevin',   'kevin@artify.fr',  'visiteur', 'Lille',     '+33 6 44 55 66 77'],
    ],

    // - Artisans : email_user => [nom_boutique, specialite, description, site, ig, note, nb_avis] -
    'artisans' => [
        'sophie@artify.fr' => ['Atelier Sophie M.',  'Bijouterie',   'Créations en or et argent faites main depuis 2015. Pièces uniques, pierres choisies une à une.', 'https://atelier-sophie.example',    'atelier_sophie_m', 4.80, 47],
        'lucas@artify.fr'  => ['Lucas Céramiques',   'Céramique',    'Pièces uniques en grès et porcelaine, cuisson au gaz à 1280°C. Influences japonaises.',              'https://lucas-ceramiques.example',  'lucas_ceramiques', 4.65, 32],
        'amelie@artify.fr' => ['Fils & Trame',       'Textile',      'Tissage artisanal et broderies contemporaines. Laines, lins et soies sourcés en France.',           'https://filsettrame.example',       'fils_et_trame',    4.92, 21],
        'marc@artify.fr'   => ['Boisseau Ébéniste',  'Ébénisterie',  'Mobilier sur mesure en bois massif (chêne, noyer, frêne). Forêts gérées durablement.',              'https://boisseau-ebeniste.example', 'boisseau_ebeniste',4.40, 12],
    ],

    // - Produits : [artisan_email, cat_id, nom, desc, prix, materiaux, dim, delai, stock, perso, seed_image] -
    'produits' => [
        ['sophie@artify.fr', 1, 'Bague dorée à l\'or fin',
            'Bague unique en or 18 carats serti d\'un saphir bleu de Ceylan. Poli main et finitions soignées. Fabrication à la cire perdue dans notre atelier parisien. Livraison en écrin offert.',
            128.00, 'Or 18 carats, saphir naturel de Ceylan', 'Tour de doigt 50-58', 10, 5, 1, 'bijou-bague-or'],
        ['sophie@artify.fr', 1, 'Collier perles d\'eau douce',
            'Sautoir long de 80 cm en perles d\'eau douce baroques, fermoir argent massif. Chaque perle est sélectionnée pour son orient unique. Pièce sobre et intemporelle.',
            185.00, 'Perles d\'eau douce, argent 925', '80 cm', 7, 4, 1, 'bijou-collier-perles'],
        ['sophie@artify.fr', 1, 'Boucles d\'oreilles puces topaze',
            'Puces minimalistes en or jaune 9 carats serties d\'une topaze bleue ronde. Idéales pour tous les jours, fournies avec poussoirs sécurité.',
            72.00, 'Or 9 carats, topaze bleue', '5 mm de diamètre', 5, 8, 0, 'bijou-puces-topaze'],
        ['lucas@artify.fr', 2, 'Bol en grès émaillé',
            'Bol tourné main en grès chamotté avec émail mat blanc cassé. Cuisson au gaz à 1280°C. Va au lave-vaisselle et au micro-ondes. Chaque pièce est unique.',
            65.00, 'Grès chamotté, émail mat', 'Ø 14 cm, h. 7 cm', 12, 6, 0, 'ceramique-bol-gres'],
        ['lucas@artify.fr', 2, 'Théière en porcelaine craquelée',
            'Théière de 80 cl en porcelaine fine, émail craquelé céladon inspiré des céramiques coréennes. Filtre intégré, anse confortable. Pour amateurs de thés délicats.',
            145.00, 'Porcelaine, émail céladon craquelé', '80 cl', 18, 3, 0, 'ceramique-theiere'],
        ['lucas@artify.fr', 2, 'Set de 4 tasses à espresso',
            'Set de quatre tasses à espresso de 8 cl, grès noir au touché velouté. Couleurs variées entre bleu nuit, vert mousse, brun terre et noir mat.',
            88.00, 'Grès noir, émail mat', 'Ø 6 cm, h. 5 cm', 14, 5, 0, 'ceramique-tasses-espresso'],
        ['lucas@artify.fr', 2, 'Grand plat à tarte',
            'Plat à tarte de 32 cm en grès roux, émail transparent brillant. Convient au four jusqu\'à 250°C. Bord ondulé typique du tournage main.',
            78.00, 'Grès roux, émail transparent', 'Ø 32 cm', 12, 4, 0, 'ceramique-plat-tarte'],
        ['amelie@artify.fr', 3, 'Écharpe tissée main',
            'Écharpe en laine mérinos et soie tissée sur métier à bras. Motif tartan revisité dans des tons ocre et vert mousse. Légère, douce et chaude.',
            89.00, 'Laine mérinos 70%, soie 30%', '180 x 35 cm', 14, 7, 1, 'textile-echarpe'],
        ['amelie@artify.fr', 3, 'Coussin brodé motif botanique',
            'Coussin 40x40 cm en lin naturel brodé main de motifs botaniques (fougères, fleurs séchées). Housse amovible avec fermeture éclair invisible.',
            65.00, 'Lin lourd, fil de coton DMC', '40 x 40 cm', 10, 9, 1, 'textile-coussin-brode'],
        ['amelie@artify.fr', 3, 'Tenture murale macramé',
            'Tenture murale en macramé de coton naturel, fixée sur branche de bois flotté. Décoration bohème pour chambre ou salon. Pièce unique.',
            135.00, 'Coton recyclé, bois flotté', '90 x 120 cm', 21, 2, 0, 'textile-macrame'],
        ['marc@artify.fr', 4, 'Tabouret tripode en chêne',
            'Tabouret tripode en chêne massif tourné, finition huile de lin. Assise galbée confortable. Hauteur idéale 45 cm. Construction sans clou ni vis (tenons chevilles).',
            220.00, 'Chêne massif, huile de lin', 'h. 45 cm, Ø 32 cm', 30, 6, 0, 'bois-tabouret'],
        ['marc@artify.fr', 4, 'Planche à découper en noyer',
            'Planche à découper professionnelle en noyer américain, finie à l\'huile alimentaire. Rainure de récupération de jus. Va à l\'huilage régulier.',
            78.00, 'Noyer américain massif', '40 x 28 x 3 cm', 7, 10, 1, 'bois-planche'],
        ['marc@artify.fr', 4, 'Étagère murale en frêne',
            'Étagère murale flottante en frêne massif clair, 80 cm de long. Système de fixation invisible inclus. Style scandinave épuré.',
            165.00, 'Frêne massif, fixation acier', '80 x 22 x 4 cm', 14, 4, 0, 'bois-etagere'],
        ['sophie@artify.fr', 5, 'Pochette en cuir tannage végétal',
            'Pochette zippée en cuir tannage végétal teinté à la main. Cousue main au point sellier. Patine au fil du temps. Doublure coton.',
            145.00, 'Cuir de vachette tannage végétal, fil de lin', '24 x 16 cm', 18, 3, 1, 'cuir-pochette'],
        ['lucas@artify.fr', 6, 'Vase soufflé bouche bicolore',
            'Vase en verre soufflé bouche, dégradé ambre vers transparent. Fabriqué à la canne dans la tradition des verriers. Chaque pièce est unique.',
            185.00, 'Verre borosilicate soufflé', 'h. 22 cm', 21, 2, 0, 'verre-vase'],
    ],

    // - Événements : [artisan_email, titre, desc, lieu, ville, days_offset, dur_h, prix, cap, seed] -
    'events' => [
        ['sophie@artify.fr', 'Marché des créateurs - Place des Vosges',
            'Exposition-vente de bijoux contemporains. Rencontrez 30 artisans européens autour d\'un café offert.',
            'Place des Vosges', 'Paris', 14, 8, 0.00, 200, 'event-marche-paris'],
        ['lucas@artify.fr', 'Salon de la céramique contemporaine',
            'Trois jours dédiés à la céramique d\'art. Démonstrations de tournage et raku live tous les jours à 15h.',
            'Halle Tony Garnier', 'Lyon', 28, 72, 8.00, 500, 'event-salon-ceramique'],
        ['amelie@artify.fr', 'Atelier tissage débutant',
            'Initiez-vous au tissage sur métier à bras. Repartez avec votre sous-verre tissé. Tout le matériel est fourni, places limitées.',
            'Atelier Fils & Trame', 'Bordeaux', 21, 3, 65.00, 8, 'event-atelier-tissage'],
        ['marc@artify.fr', 'Portes ouvertes ébénisterie',
            'Visite gratuite de l\'atelier, démonstrations d\'assemblage traditionnel et vente exceptionnelle de pièces uniques.',
            'Atelier Boisseau', 'Nantes', 10, 6, 0.00, 80, 'event-portes-ouvertes'],
        ['sophie@artify.fr', 'Nocturne joaillerie - exposition',
            'Soirée exceptionnelle autour des pièces de la collection automne. Cocktail offert, présentation des nouvelles créations.',
            'Galerie Quintessence', 'Paris', 35, 4, 15.00, 60, 'event-nocturne-bijoux'],
        ['lucas@artify.fr', 'Stage week-end raku',
            'Stage intensif de deux jours sur la technique du raku japonais. Émaillage, cuisson, enfumage. Repas de midi inclus.',
            'Atelier Lucas Céramiques', 'Lyon', 45, 48, 220.00, 6, 'event-stage-raku'],
        ['amelie@artify.fr', 'Marché de Noël des artisans',
            'Le rendez-vous incontournable du textile artisanal pour les fêtes. Plus de 40 exposants, animations enfants.',
            'Place Pey Berland', 'Bordeaux', 60, 96, 0.00, 1000, 'event-marche-noel'],
        ['marc@artify.fr', 'Conférence : bois et durabilité',
            'Conférence-débat avec un ingénieur forestier sur les essences locales et le mobilier durable. Entrée libre, inscription conseillée.',
            'École du bois', 'Nantes', 7, 2, 0.00, 120, 'event-conference-bois'],
    ],

    // - Galerie : [artisan_email, produit_nom_or_null, seed_image, titre, desc] -
    'galerie' => [
        ['sophie@artify.fr', 'Bague dorée à l\'or fin',         'gallery-bague-1',       'Bague saphir - vue de profil', 'Détail du sertissage sur la bague saphir.'],
        ['sophie@artify.fr', 'Collier perles d\'eau douce',     'gallery-collier-1',     'Sautoir en porté',             'Le sautoir en situation, sur une tenue d\'été.'],
        ['sophie@artify.fr', null,                              'gallery-atelier-sophie','L\'atelier rue de Turenne',    'Vue d\'ensemble de l\'atelier de Sophie.'],
        ['lucas@artify.fr',  'Bol en grès émaillé',             'gallery-bol-1',         'Bol grès - lumière naturelle', 'Le bol photographié à la lumière du matin.'],
        ['lucas@artify.fr',  'Théière en porcelaine craquelée', 'gallery-theiere-1',     'Théière céladon en service',   'La théière utilisée pour une cérémonie du thé.'],
        ['lucas@artify.fr',  null,                              'gallery-four-raku',     'Cuisson raku',                 'Sortie de four lors d\'une cuisson raku traditionnelle.'],
        ['amelie@artify.fr', 'Écharpe tissée main',             'gallery-echarpe-1',     'Détail tissage tartan',        'Gros plan sur les fils croisés du motif tartan.'],
        ['amelie@artify.fr', 'Tenture murale macramé',          'gallery-macrame-1',     'Macramé suspendu',             'La tenture installée dans un salon lumineux.'],
        ['amelie@artify.fr', null,                              'gallery-metier-tisser', 'Métier à bras',                'Le métier à bras Leclerc utilisé à l\'atelier.'],
        ['marc@artify.fr',   'Tabouret tripode en chêne',       'gallery-tabouret-1',    'Tabouret - finitions',         'Détail des tenons chevilles du tabouret.'],
        ['marc@artify.fr',   'Planche à découper en noyer',     'gallery-planche-1',     'Planche en service',           'La planche en utilisation dans une cuisine.'],
        ['marc@artify.fr',   'Étagère murale en frêne',         'gallery-etagere-1',     'Étagère installée',            'Étagère mise en scène avec quelques céramiques.'],
        ['marc@artify.fr',   null,                              'gallery-copeaux',       'Ambiance d\'atelier',          'Les copeaux de chêne après une journée de travail.'],
    ],

    // - FAQ : [question, reponse, ordre] -
    'faqs' => [
        ['Comment créer un compte ?',                       'Cliquez sur « S\'inscrire » en haut à droite, choisissez votre profil (visiteur ou artisan), remplissez le formulaire. Un email de confirmation peut être demandé selon le mode de déploiement.', 10],
        ['Quels sont les moyens de paiement ?',             'Nous acceptons les cartes Visa, Mastercard et American Express. Le paiement est sécurisé par notre prestataire certifié PCI-DSS. Aucune donnée bancaire n\'est stockée sur nos serveurs.', 11],
        ['Quels sont les délais de livraison ?',            'La majorité des pièces sont fabriquées sur commande. Comptez le délai de fabrication indiqué sur la fiche produit + 3 à 5 jours ouvrés de livraison en France métropolitaine.', 12],
        ['Puis-je retourner un article ?',                  'Vous disposez de 14 jours après réception pour retourner une pièce non personnalisée. Les pièces sur mesure ou personnalisées ne sont pas reprises sauf défaut avéré.', 13],
        ['Comment devenir artisan sur Artify ?',            'Créez un compte « Artisan », complétez votre profil (boutique, spécialité, description) puis ajoutez vos premières créations. Notre équipe vérifie chaque boutique sous 48h ouvrées.', 14],
        ['Les pièces sont-elles personnalisables ?',        'De nombreuses pièces le sont, regardez l\'icône « Personnalisation » sur la fiche produit. Vous pourrez préciser vos souhaits lors de la commande (gravure, dimensions, couleurs).', 15],
        ['Comment m\'inscrire à un événement ?',            'Sur la page Événements, cliquez sur la fiche qui vous intéresse puis sur « Réserver ». Une confirmation vous est envoyée par email avec le détail pratique.', 16],
        ['Comment contacter un artisan ?',                  'Depuis la fiche boutique de l\'artisan, utilisez le bouton « Contacter ». La messagerie interne garantit la confidentialité de vos échanges.', 17],
        ['Mes données personnelles sont-elles protégées ?', 'Oui, Artify respecte le RGPD. Consultez nos mentions légales et notre politique de confidentialité. Vous pouvez à tout moment demander la suppression de votre compte.', 18],
        ['Y a-t-il des frais de port ?',                    'Les frais de port sont calculés au moment du panier en fonction du poids et de la destination. La livraison est offerte dès 150 € d\'achat en France métropolitaine.', 19],
    ],

    // - Commandes : [user_email, artisan_email, statut, adresse, cp, ville, msg, qte] -
    'commandes' => [
        ['marie@artify.fr', 'sophie@artify.fr', 'livree',         '12 rue de Rivoli',  '75001', 'Paris',    null, 1],
        ['paul@artify.fr',  'lucas@artify.fr',  'expediee',       '5 avenue Foch',     '69006', 'Lyon',     'Bien emballer svp', 2],
        ['julie@artify.fr', 'amelie@artify.fr', 'en_fabrication', '23 cours Pasteur',  '33000', 'Bordeaux', 'Cadeau d\'anniversaire', 1],
        ['kevin@artify.fr', 'marc@artify.fr',   'confirmee',      '8 rue du Calvaire', '44000', 'Nantes',   null, 1],
        ['marie@artify.fr', 'lucas@artify.fr',  'en_attente',     '12 rue de Rivoli',  '75001', 'Paris',    null, 3],
    ],

    // - Avis : [user_email, artisan_email, note, commentaire] -
    'avis' => [
        ['marie@artify.fr', 'sophie@artify.fr', 5, 'Bague magnifique, le travail est irréprochable. Sophie a été d\'excellents conseils. Je recommande !'],
        ['paul@artify.fr',  'lucas@artify.fr',  5, 'Set de tasses parfait, exactement comme sur les photos. Emballage soigné. Merci Lucas.'],
        ['julie@artify.fr', 'amelie@artify.fr', 4, 'Très belle écharpe, la qualité est au rendez-vous. Petit bémol sur le délai un peu long, sinon parfait.'],
        ['kevin@artify.fr', 'marc@artify.fr',   5, 'Tabouret superbe, finitions impeccables. Marc m\'a accompagné dans le choix de la teinte.'],
        ['paul@artify.fr',  'sophie@artify.fr', 4, 'Boucles d\'oreilles très fines, parfaites pour un cadeau. Livraison rapide.'],
        ['marie@artify.fr', 'lucas@artify.fr',  5, 'Bol en grès somptueux, le toucher de l\'émail est unique. Conforme à 100%.'],
        ['julie@artify.fr', 'sophie@artify.fr', 3, 'Bijou très joli mais un peu plus petit que ce que j\'imaginais. Sophie a proposé un échange, service impeccable.'],
    ],

    // - Contact : [nom, email, sujet, message, traite] -
    'contacts' => [
        ['Léa Bernard',     'lea.bernard@example.com',  'Demande de devis',          'Bonjour, serait-il possible d\'avoir un devis pour 20 bols personnalisés pour un mariage en juillet ? Merci.', 1],
        ['Hugo Carpentier', 'hugo.c@example.com',       'Problème de connexion',     'Bonjour, je n\'arrive pas à me connecter à mon compte depuis ce matin. Pouvez-vous m\'aider ?', 1],
        ['Inès Vasseur',    'ines.v@example.com',       'Devenir artisane',          'Bonjour, je suis céramiste à Marseille et je souhaiterais ouvrir une boutique sur Artify. Quels sont les critères ?', 0],
        ['Olivier Klein',   'oklein@example.com',       'Question sur la livraison', 'Bonjour, livrez-vous en Belgique ? Et quels sont les délais pour Bruxelles ? Merci d\'avance.', 1],
        ['Camille Roux',    'camille.roux@example.com', 'Article reçu cassé',        'Bonjour, mon vase est arrivé fissuré ce matin. Comment procéder pour un remplacement ou un remboursement ?', 0],
    ],

    // - Textes longs CGU / mention légale -
    'cgu' => <<<MD
# Conditions générales d'utilisation

**Version 1.0** - date d'effet : aujourd'hui.

## 1. Objet
Les présentes conditions générales d'utilisation (« CGU ») régissent l'accès et l'usage du site Artify, plateforme de mise en relation entre artisans d'art et acheteurs. En accédant au site, l'utilisateur reconnaît avoir pris connaissance des CGU et les accepter sans réserve.

## 2. Inscription
La création d'un compte est gratuite. Deux profils sont possibles : visiteur (achat, favoris, événements) et artisan (vente, boutique, événements). L'utilisateur s'engage à fournir des informations exactes et à les maintenir à jour. Toute fausse déclaration peut entraîner la suspension du compte.

## 3. Utilisation du service
Le site est mis à disposition pour un usage personnel et non commercial, sauf pour les artisans dans le cadre de leur boutique. Toute utilisation détournée, automatisée ou abusive (scraping, dénis de service) est interdite et peut entraîner la fermeture du compte sans préavis.

## 4. Vente et commande
L'artisan reste l'unique vendeur des pièces présentées sur sa boutique. Artify joue un rôle d'intermédiaire technique. Les commandes valent contrat ferme entre l'acheteur et l'artisan, sauf rétractation légale de 14 jours pour les pièces non personnalisées (Code de la consommation, art. L221-18).

## 5. Propriété intellectuelle
Tous les contenus du site (textes, photos, logos, marques) sont protégés par le droit d'auteur. Les artisans conservent leurs droits sur leurs créations et photos. Toute reproduction ou diffusion sans autorisation est interdite.

## 6. Responsabilité
Artify s'efforce d'assurer la disponibilité du site mais ne saurait être tenu responsable des interruptions techniques, des contenus produits par les artisans, ou des litiges directs entre acheteurs et vendeurs. Une médiation interne peut être proposée à titre de service.

## 7. Données personnelles
Le traitement des données est décrit dans les mentions légales. L'utilisateur dispose d'un droit d'accès, de rectification, d'effacement et de portabilité (RGPD). Pour exercer ces droits, contactez le DPO via le formulaire de contact.

## 8. Modifications
Artify se réserve le droit de modifier les CGU à tout moment. Les utilisateurs sont informés des modifications substantielles par email. La poursuite de l'usage du site vaut acceptation des nouvelles conditions.

## 9. Loi applicable
Les présentes CGU sont régies par le droit français. Tout litige sera soumis aux tribunaux compétents de Paris, sauf disposition légale impérative contraire.
MD,

    'mentions' => <<<MD
# Mentions légales

## Éditeur du site
Artify SAS - Société par actions simplifiée au capital de 10 000 €.
Siège social : 12 rue de l'Atelier, 75011 Paris.
RCS Paris 920 123 456. TVA intracommunautaire : FR 12 920123456.
Directrice de la publication : Sophie Martin.
Email : contact@artify.fr - Téléphone : +33 1 23 45 67 89.

## Hébergeur
OVHcloud SAS - 2 rue Kellermann, 59100 Roubaix, France.
Téléphone : 1007. Site : ovhcloud.com.

## Propriété intellectuelle
L'ensemble du site (charte graphique, code, contenus rédactionnels) est protégé par le droit d'auteur. Les marques et logos cités sont la propriété de leurs détenteurs respectifs. Toute reproduction non autorisée est passible de poursuites.

## Données personnelles
Le traitement des données personnelles est conforme au Règlement Général sur la Protection des Données (RGPD) et à la loi Informatique et Libertés. Le responsable du traitement est Artify SAS. Les données sont conservées le temps strictement nécessaire à la fourniture du service.

Vous disposez d'un droit d'accès, de rectification, d'effacement, de portabilité et d'opposition. Pour exercer ces droits, contactez notre DPO :
- Par email : dpo@artify.fr
- Par courrier : DPO Artify, 12 rue de l'Atelier, 75011 Paris.

Vous pouvez également introduire une réclamation auprès de la CNIL (cnil.fr).

## Cookies
Le site dépose des cookies fonctionnels indispensables au panier et à la session de connexion. Des cookies de mesure d'audience anonymisée peuvent être utilisés. Aucun cookie publicitaire tiers n'est déposé sans votre consentement explicite.

## Liens externes
Le site peut contenir des liens vers des sites tiers (sites des artisans, réseaux sociaux). Artify n'exerce aucun contrôle sur ces sites et ne saurait être tenu responsable de leur contenu.

## Médiation
En cas de litige, l'utilisateur peut faire appel au médiateur de la consommation accessible via le site mediation-conso.fr avant toute action judiciaire.
MD,
];
