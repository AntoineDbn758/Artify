-- Dump SQL initial de la base 'artify'. Importe automatiquement au premier
-- demarrage du conteneur MariaDB grace au mecanisme docker-entrypoint-initdb.d.
-- Contient les 18 tables (utilisateur, artisan, produit, categorie, evenement,
-- ...) et quelques lignes de demarrage (categories de base, FAQ initiale).

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : dim. 10 mai 2026 à 16:45
-- Version du serveur : 10.4.28-MariaDB
-- Version de PHP : 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `artify`
--

-- -

--
-- Structure de la table `artisan`
--

CREATE TABLE `artisan` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `nom_boutique` varchar(150) NOT NULL,
  `specialite` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `site_web` varchar(300) DEFAULT NULL,
  `instagram` varchar(100) DEFAULT NULL,
  `note_moyenne` decimal(3,2) DEFAULT 0.00,
  `nb_avis` int(11) NOT NULL DEFAULT 0,
  `verifie` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `artisan`
--

INSERT INTO `artisan` (`id`, `utilisateur_id`, `nom_boutique`, `specialite`, `description`, `site_web`, `instagram`, `note_moyenne`, `nb_avis`, `verifie`, `created_at`) VALUES
(1, 2, 'Atelier Sophie M.', 'Bijouterie', 'Créations en or et argent faites à la main depuis 2015.', NULL, NULL, 0.00, 0, 0, '2026-04-15 11:02:01'),
(2, 3, 'Lucas Céramiques', 'Céramique', 'Pièces uniques en grès et porcelaine.', NULL, NULL, 0.00, 0, 0, '2026-04-15 11:02:01'),
(3, 4, 'Fils & Trame', 'Textile', 'Tissage artisanal et broderies contemporaines.', NULL, NULL, 0.00, 0, 0, '2026-04-15 11:02:01');

-- -

--
-- Structure de la table `avis`
--

CREATE TABLE `avis` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `artisan_id` int(11) NOT NULL,
  `commande_id` int(11) DEFAULT NULL,
  `note` tinyint(4) NOT NULL CHECK (`note` between 1 and 5),
  `commentaire` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -

--
-- Structure de la table `categorie`
--

CREATE TABLE `categorie` (
  `id` int(11) NOT NULL,
  `nom` varchar(80) NOT NULL,
  `emoji` varchar(10) DEFAULT NULL,
  `slug` varchar(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `categorie`
--

INSERT INTO `categorie` (`id`, `nom`, `emoji`, `slug`) VALUES
(1, 'Bijouterie', '', 'bijouterie'),
(2, 'Céramique', '', 'ceramique'),
(3, 'Textile', '', 'textile'),
(4, 'Ébénisterie', '', 'ebenisterie'),
(5, 'Cuir', '', 'cuir'),
(6, 'Verrerie', '', 'verrerie'),
(7, 'Peinture', '', 'peinture'),
(8, 'Illustration', '️', 'illustration');

-- -

--
-- Structure de la table `cgu`
--

CREATE TABLE `cgu` (
  `id` int(11) NOT NULL,
  `contenu` longtext NOT NULL,
  `version` varchar(20) NOT NULL DEFAULT '1.0',
  `date_effet` date NOT NULL,
  `est_actif` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -

--
-- Structure de la table `commande`
--

CREATE TABLE `commande` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `artisan_id` int(11) NOT NULL,
  `montant_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `statut` enum('en_attente','confirmee','en_fabrication','expediee','livree','annulee') NOT NULL DEFAULT 'en_attente',
  `adresse_livraison` text DEFAULT NULL,
  `code_postal` varchar(10) DEFAULT NULL,
  `ville_livraison` varchar(100) DEFAULT NULL,
  `message_personnalisation` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -

--
-- Structure de la table `contact`
--

CREATE TABLE `contact` (
  `id` int(11) NOT NULL,
  `nom` varchar(120) NOT NULL,
  `email` varchar(180) NOT NULL,
  `sujet` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `traite` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -

--
-- Structure de la table `evenement`
--

CREATE TABLE `evenement` (
  `id` int(11) NOT NULL,
  `artisan_id` int(11) NOT NULL,
  `titre` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `lieu` varchar(200) DEFAULT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `date_debut` datetime NOT NULL,
  `date_fin` datetime DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `capacite_max` int(11) DEFAULT NULL,
  `prix_entree` decimal(8,2) NOT NULL DEFAULT 0.00,
  `est_publie` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -

--
-- Structure de la table `faq`
--

CREATE TABLE `faq` (
  `id` int(11) NOT NULL,
  `question` varchar(400) NOT NULL,
  `reponse` text NOT NULL,
  `ordre` int(11) NOT NULL DEFAULT 0,
  `est_actif` tinyint(1) NOT NULL DEFAULT 1,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `faq`
--

INSERT INTO `faq` (`id`, `question`, `reponse`, `ordre`, `est_actif`, `updated_at`) VALUES
(1, 'Comment créer un compte artisan ?', 'Cliquez sur « Créer un compte », choisissez le profil Artisan et remplissez le formulaire.', 1, 1, '2026-04-15 11:02:01'),
(2, 'Les paiements sont-ils sécurisés ?', 'Oui, tous les paiements transitent par une plateforme certifiée PCI-DSS.', 2, 1, '2026-04-15 11:02:01'),
(3, 'Puis-je commander une pièce personnalisée ?', 'Oui, sur la fiche produit, activez l\'option « Personnalisation » et précisez vos souhaits.', 3, 1, '2026-04-15 11:02:01');

-- -

--
-- Structure de la table `favori`
--

CREATE TABLE `favori` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `produit_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -

--
-- Structure de la table `galerie`
--

CREATE TABLE `galerie` (
  `id` int(11) NOT NULL,
  `artisan_id` int(11) NOT NULL,
  `produit_id` int(11) DEFAULT NULL,
  `image_url` varchar(500) NOT NULL,
  `titre` varchar(200) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `est_publie` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -

--
-- Structure de la table `image_produit`
--

CREATE TABLE `image_produit` (
  `id` int(11) NOT NULL,
  `produit_id` int(11) NOT NULL,
  `url` varchar(500) NOT NULL,
  `ordre` int(11) NOT NULL DEFAULT 0,
  `est_principale` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -

--
-- Structure de la table `inscription_evenement`
--

CREATE TABLE `inscription_evenement` (
  `id` int(11) NOT NULL,
  `evenement_id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `date_inscription` datetime NOT NULL DEFAULT current_timestamp(),
  `statut` enum('confirmee','liste_attente','annulee') NOT NULL DEFAULT 'confirmee'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -

--
-- Structure de la table `ligne_commande`
--

CREATE TABLE `ligne_commande` (
  `id` int(11) NOT NULL,
  `commande_id` int(11) NOT NULL,
  `produit_id` int(11) NOT NULL,
  `quantite` int(11) NOT NULL DEFAULT 1,
  `prix_unitaire` decimal(10,2) NOT NULL,
  `details_personnalisation` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -

--
-- Structure de la table `mention_legale`
--

CREATE TABLE `mention_legale` (
  `id` int(11) NOT NULL,
  `contenu` longtext NOT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -

--
-- Structure de la table `messagerie`
--

CREATE TABLE `messagerie` (
  `id` int(11) NOT NULL,
  `expediteur_id` int(11) NOT NULL,
  `destinataire_id` int(11) NOT NULL,
  `contenu` text NOT NULL,
  `lu` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -

--
-- Structure de la table `produit`
--

CREATE TABLE `produit` (
  `id` int(11) NOT NULL,
  `artisan_id` int(11) NOT NULL,
  `categorie_id` int(11) NOT NULL,
  `nom` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `prix` decimal(10,2) NOT NULL,
  `materiaux` text DEFAULT NULL,
  `dimensions` varchar(100) DEFAULT NULL,
  `delai_fabrication_jours` int(11) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `est_personnalisable` tinyint(1) NOT NULL DEFAULT 0,
  `est_publie` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `produit`
--

INSERT INTO `produit` (`id`, `artisan_id`, `categorie_id`, `nom`, `description`, `prix`, `materiaux`, `dimensions`, `delai_fabrication_jours`, `stock`, `est_personnalisable`, `est_publie`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Bague dorée à l\'or fin', NULL, 128.00, 'Or 18 carats, saphir', NULL, NULL, 3, 0, 1, '2026-04-15 11:02:01', '2026-04-15 11:02:01'),
(2, 2, 2, 'Bol en grès émaillé', NULL, 65.00, 'Grès chamotté, émail mat', NULL, NULL, 8, 0, 1, '2026-04-15 11:02:01', '2026-04-15 11:02:01'),
(3, 3, 3, 'Écharpe tissée main', NULL, 89.00, 'Laine mérinos, soie', NULL, NULL, 5, 0, 1, '2026-04-15 11:02:01', '2026-04-15 11:02:01');

-- -

--
-- Structure de la table `recherche_log`
--

CREATE TABLE `recherche_log` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) DEFAULT NULL,
  `terme` varchar(200) DEFAULT NULL,
  `categorie` varchar(80) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `id` int(11) NOT NULL,
  `nom` varchar(80) NOT NULL,
  `prenom` varchar(80) NOT NULL,
  `email` varchar(180) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `role` enum('visiteur','artisan','admin') NOT NULL DEFAULT 'visiteur',
  `avatar_url` varchar(500) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `est_actif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id`, `nom`, `prenom`, `email`, `mot_de_passe`, `role`, `avatar_url`, `bio`, `ville`, `telephone`, `est_actif`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'Système', 'admin@artify.fr', '$2y$12$examplehashfordevonly', 'admin', NULL, NULL, NULL, NULL, 1, '2026-04-15 11:02:01', '2026-04-15 11:02:01'),
(2, 'Martin', 'Sophie', 'sophie@artify.fr', '$2y$12$examplehash2', 'artisan', NULL, NULL, 'Paris', NULL, 1, '2026-04-15 11:02:01', '2026-04-15 11:02:01'),
(3, 'Renard', 'Lucas', 'lucas@artify.fr', '$2y$12$examplehash3', 'artisan', NULL, NULL, 'Lyon', NULL, 1, '2026-04-15 11:02:01', '2026-04-15 11:02:01'),
(4, 'Tisserand', 'Amélie', 'amelie@artify.fr', '$2y$12$examplehash4', 'artisan', NULL, NULL, 'Bordeaux', NULL, 1, '2026-04-15 11:02:01', '2026-04-15 11:02:01'),
(5, 'Dupont', 'Marie', 'marie@example.com', '$2y$12$examplehash5', 'visiteur', NULL, NULL, NULL, NULL, 1, '2026-04-15 11:02:01', '2026-04-15 11:02:01'),
(8, 'Test', 'Sara', 'test@test.com', '$2y$10$6sVpeBGvHT9uJ1q4C.jxIe3CSGqD9QJfXMBeURR0AfkM61IKektYG', 'visiteur', NULL, NULL, NULL, NULL, 1, '2026-05-10 16:29:34', '2026-05-10 16:29:34');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `artisan`
--
ALTER TABLE `artisan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `utilisateur_id` (`utilisateur_id`);

--
-- Index pour la table `avis`
--
ALTER TABLE `avis`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_avis_commande` (`commande_id`),
  ADD KEY `fk_avis_utilisateur` (`utilisateur_id`),
  ADD KEY `idx_avis_artisan` (`artisan_id`);

--
-- Index pour la table `categorie`
--
ALTER TABLE `categorie`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Index pour la table `cgu`
--
ALTER TABLE `cgu`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `commande`
--
ALTER TABLE `commande`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_commande_user` (`utilisateur_id`),
  ADD KEY `idx_commande_artisan` (`artisan_id`);

--
-- Index pour la table `contact`
--
ALTER TABLE `contact`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `evenement`
--
ALTER TABLE `evenement`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_evenement_artisan` (`artisan_id`),
  ADD KEY `idx_evenement_date` (`date_debut`);

--
-- Index pour la table `faq`
--
ALTER TABLE `faq`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `favori`
--
ALTER TABLE `favori`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_favori` (`utilisateur_id`,`produit_id`),
  ADD KEY `fk_favori_produit` (`produit_id`);

--
-- Index pour la table `galerie`
--
ALTER TABLE `galerie`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_galerie_produit` (`produit_id`),
  ADD KEY `idx_galerie_artisan` (`artisan_id`);

--
-- Index pour la table `image_produit`
--
ALTER TABLE `image_produit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_image_produit` (`produit_id`);

--
-- Index pour la table `inscription_evenement`
--
ALTER TABLE `inscription_evenement`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_inscription` (`evenement_id`,`utilisateur_id`),
  ADD KEY `fk_ie_utilisateur` (`utilisateur_id`);

--
-- Index pour la table `ligne_commande`
--
ALTER TABLE `ligne_commande`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_lc_commande` (`commande_id`),
  ADD KEY `fk_lc_produit` (`produit_id`);

--
-- Index pour la table `mention_legale`
--
ALTER TABLE `mention_legale`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `messagerie`
--
ALTER TABLE `messagerie`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_msg_expediteur` (`expediteur_id`),
  ADD KEY `idx_msg_destinataire` (`destinataire_id`);

--
-- Index pour la table `produit`
--
ALTER TABLE `produit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_produit_artisan` (`artisan_id`),
  ADD KEY `idx_produit_categorie` (`categorie_id`),
  ADD KEY `idx_produit_publie` (`est_publie`);

--
-- Index pour la table `recherche_log`
--
ALTER TABLE `recherche_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_recherche_utilisateur` (`utilisateur_id`);

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `artisan`
--
ALTER TABLE `artisan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `avis`
--
ALTER TABLE `avis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `categorie`
--
ALTER TABLE `categorie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `cgu`
--
ALTER TABLE `cgu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `commande`
--
ALTER TABLE `commande`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `contact`
--
ALTER TABLE `contact`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `evenement`
--
ALTER TABLE `evenement`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `faq`
--
ALTER TABLE `faq`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `favori`
--
ALTER TABLE `favori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `galerie`
--
ALTER TABLE `galerie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `image_produit`
--
ALTER TABLE `image_produit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `inscription_evenement`
--
ALTER TABLE `inscription_evenement`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `ligne_commande`
--
ALTER TABLE `ligne_commande`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `mention_legale`
--
ALTER TABLE `mention_legale`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `messagerie`
--
ALTER TABLE `messagerie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `produit`
--
ALTER TABLE `produit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `recherche_log`
--
ALTER TABLE `recherche_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `artisan`
--
ALTER TABLE `artisan`
  ADD CONSTRAINT `fk_artisan_utilisateur` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `avis`
--
ALTER TABLE `avis`
  ADD CONSTRAINT `fk_avis_artisan` FOREIGN KEY (`artisan_id`) REFERENCES `artisan` (`id`),
  ADD CONSTRAINT `fk_avis_commande` FOREIGN KEY (`commande_id`) REFERENCES `commande` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_avis_utilisateur` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`id`);

--
-- Contraintes pour la table `commande`
--
ALTER TABLE `commande`
  ADD CONSTRAINT `fk_commande_artisan` FOREIGN KEY (`artisan_id`) REFERENCES `artisan` (`id`),
  ADD CONSTRAINT `fk_commande_utilisateur` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`id`);

--
-- Contraintes pour la table `evenement`
--
ALTER TABLE `evenement`
  ADD CONSTRAINT `fk_evenement_artisan` FOREIGN KEY (`artisan_id`) REFERENCES `artisan` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `favori`
--
ALTER TABLE `favori`
  ADD CONSTRAINT `fk_favori_produit` FOREIGN KEY (`produit_id`) REFERENCES `produit` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_favori_utilisateur` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `galerie`
--
ALTER TABLE `galerie`
  ADD CONSTRAINT `fk_galerie_artisan` FOREIGN KEY (`artisan_id`) REFERENCES `artisan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_galerie_produit` FOREIGN KEY (`produit_id`) REFERENCES `produit` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `image_produit`
--
ALTER TABLE `image_produit`
  ADD CONSTRAINT `fk_image_produit` FOREIGN KEY (`produit_id`) REFERENCES `produit` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `inscription_evenement`
--
ALTER TABLE `inscription_evenement`
  ADD CONSTRAINT `fk_ie_evenement` FOREIGN KEY (`evenement_id`) REFERENCES `evenement` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ie_utilisateur` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `ligne_commande`
--
ALTER TABLE `ligne_commande`
  ADD CONSTRAINT `fk_lc_commande` FOREIGN KEY (`commande_id`) REFERENCES `commande` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_lc_produit` FOREIGN KEY (`produit_id`) REFERENCES `produit` (`id`);

--
-- Contraintes pour la table `messagerie`
--
ALTER TABLE `messagerie`
  ADD CONSTRAINT `fk_msg_destinataire` FOREIGN KEY (`destinataire_id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_msg_expediteur` FOREIGN KEY (`expediteur_id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `produit`
--
ALTER TABLE `produit`
  ADD CONSTRAINT `fk_produit_artisan` FOREIGN KEY (`artisan_id`) REFERENCES `artisan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_produit_categorie` FOREIGN KEY (`categorie_id`) REFERENCES `categorie` (`id`);

--
-- Contraintes pour la table `recherche_log`
--
ALTER TABLE `recherche_log`
  ADD CONSTRAINT `fk_recherche_utilisateur` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
