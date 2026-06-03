-- Migration : ajout forum + mot de passe oublié
-- À exécuter une fois sur la base existante.

CREATE TABLE IF NOT EXISTS `forum_sujet` (
  `id`              INT AUTO_INCREMENT PRIMARY KEY,
  `titre`           VARCHAR(200) NOT NULL,
  `categorie`       VARCHAR(50)  NOT NULL DEFAULT 'general',
  `utilisateur_id`  INT NOT NULL,
  `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `est_epingle`     TINYINT(1) NOT NULL DEFAULT 0,
  `est_ferme`       TINYINT(1) NOT NULL DEFAULT 0,
  INDEX (utilisateur_id),
  INDEX (categorie),
  CONSTRAINT fk_sujet_user FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `forum_message` (
  `id`              INT AUTO_INCREMENT PRIMARY KEY,
  `sujet_id`        INT NOT NULL,
  `utilisateur_id`  INT NOT NULL,
  `contenu`         TEXT NOT NULL,
  `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (sujet_id),
  INDEX (utilisateur_id),
  CONSTRAINT fk_msg_sujet FOREIGN KEY (sujet_id) REFERENCES forum_sujet(id) ON DELETE CASCADE,
  CONSTRAINT fk_msg_user  FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `password_reset` (
  `id`              INT AUTO_INCREMENT PRIMARY KEY,
  `utilisateur_id`  INT NOT NULL,
  `token_hash`      CHAR(64) NOT NULL,
  `expire_at`       DATETIME NOT NULL,
  `used_at`         DATETIME NULL,
  `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY (token_hash),
  INDEX (utilisateur_id),
  CONSTRAINT fk_reset_user FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
