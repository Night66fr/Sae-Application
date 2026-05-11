-- ============================================================
--  LevelUp – donnee.sql
--  Importer dans phpMyAdmin sur la base db_PLACE_NEVEUX
-- ============================================================

DROP TABLE IF EXISTS users;

CREATE TABLE users (
  id         INT(11) UNSIGNED AUTO_INCREMENT NOT NULL,
  user       VARCHAR(50)  NOT NULL UNIQUE,
  pass       VARCHAR(255) NOT NULL,
  role       VARCHAR(20)  NOT NULL DEFAULT 'etudiant',
  nom        VARCHAR(80)  DEFAULT NULL,
  prenom     VARCHAR(80)  DEFAULT NULL,
  email      VARCHAR(120) DEFAULT NULL,
  niveau     INT(3) UNSIGNED NOT NULL DEFAULT 1,
  xp         INT(11) UNSIGNED NOT NULL DEFAULT 0,
  streak     INT(5) UNSIGNED NOT NULL DEFAULT 0,
  last_login DATETIME DEFAULT NULL,
  actif      TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
--  Comptes de test  — mot de passe : secret
--  Hash généré avec : password_hash('secret', PASSWORD_BCRYPT)
--  IMPORTANT : remplacez ce hash par celui généré sur votre
--  serveur via genhash.php (voir README)
-- ============================================================
INSERT INTO users (user, pass, role, nom, prenom, email, xp, niveau) VALUES
('alice', '$2y$10$REMPLACEZ_PAR_VOTRE_HASH_ICI________________', 'etudiant',   'Dupont',  'Alice',      'alice@etud.iut-beziers.fr', 120, 2),
('bob',   '$2y$10$REMPLACEZ_PAR_VOTRE_HASH_ICI________________', 'etudiant',   'Martin',  'Bob',        'bob@etud.iut-beziers.fr',   45,  1),
('prof',  '$2y$10$REMPLACEZ_PAR_VOTRE_HASH_ICI________________', 'enseignant', 'Borelly', 'Christophe', 'prof@iut-beziers.fr',        0,   1),
('admin', '$2y$10$REMPLACEZ_PAR_VOTRE_HASH_ICI________________', 'admin',      'Admin',   'LevelUp',    'admin@iut-beziers.fr',       0,   1);

-- ============================================================
--  ÉTAPE 1 : déposer genhash.php sur le serveur
--  ÉTAPE 2 : ouvrir dans le navigateur → copier le hash
--  ÉTAPE 3 : UPDATE users SET pass='hash_copié' WHERE 1;
--  ÉTAPE 4 : supprimer genhash.php du serveur
-- ============================================================
