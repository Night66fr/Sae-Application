-- Suppression de l'ancienne table si elle existe pour éviter les conflits
DROP TABLE IF EXISTS `users`;

-- Création de la table avec la structure attendue par le PHP
CREATE TABLE `users` (
  `id` INT(11) UNSIGNED AUTO_INCREMENT NOT NULL,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL, 
  `role` ENUM('etudiant', 'enseignant') DEFAULT 'etudiant',
  `xp` INT(11) DEFAULT 0,
  `niveau` INT(11) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- Insertion de comptes avec mots de passe HACHÉS (Mot de passe : password123)
INSERT INTO `users` (`username`, `password`, `role`) VALUES 
('enseignant_rt', '$2y$10$8K9Vf79B9.YcyfdZpAUP9O4.SNoY7CBlGubZSuZ.F1C.v6yV4T6Be', 'enseignant'),
('etudiant_test', '$2y$10$8K9Vf79B9.YcyfdZpAUP9O4.SNoY7CBlGubZSuZ.F1C.v6yV4T6Be', 'etudiant');