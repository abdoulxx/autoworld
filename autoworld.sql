-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mer. 07 mai 2025 à 00:01
-- Version du serveur : 8.3.0
-- Version de PHP : 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `autoworld`
--

-- --------------------------------------------------------

--
-- Structure de la table `admin`
--

DROP TABLE IF EXISTS `admin`;
CREATE TABLE IF NOT EXISTS `admin` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `admin`
--

INSERT INTO `admin` (`id`, `nom`, `email`, `mot_de_passe`, `created_at`) VALUES
(3, 'admin', 'admin@gmail.com', '$2y$10$MHKLsVQPqUF70K9qZPot4eX89.3xzfJK1AP0SI8xbtf217HerDiOK', '2024-06-18 01:26:25'),
(4, 'test', 'test@gmail.com', '$2y$10$XSJcJ/D12VxQZaCMUB2Q2OvPxQNfuQ1pfxUXEzs981H6nXxS/qDka', '2025-05-01 22:36:41');

-- --------------------------------------------------------

--
-- Structure de la table `demandes_essai`
--

DROP TABLE IF EXISTS `demandes_essai`;
CREATE TABLE IF NOT EXISTS `demandes_essai` (
  `id` int NOT NULL AUTO_INCREMENT,
  `vehicule_id` int DEFAULT NULL,
  `date_essai` date DEFAULT NULL,
  `heure_essai` time DEFAULT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `message` text,
  `date_demande` datetime DEFAULT NULL,
  `statut` enum('en_attente','confirmé','annulé') DEFAULT 'en_attente',
  PRIMARY KEY (`id`),
  KEY `vehicule_id` (`vehicule_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `demandes_essai`
--

INSERT INTO `demandes_essai` (`id`, `vehicule_id`, `date_essai`, `heure_essai`, `nom`, `email`, `telephone`, `message`, `date_demande`, `statut`) VALUES
(1, 7, '2025-05-09', '11:00:00', 'sidy samb', 'sambsidy287@gmail.com', '0151516084', 'uregent', '2025-05-02 14:10:15', 'confirmé'),
(2, 6, '2025-05-20', '12:00:00', 'sidy samb', 'sambsidy287@gmail.com', '0151516084', 'urgent 0', '2025-05-06 23:45:22', 'confirmé');

-- --------------------------------------------------------

--
-- Structure de la table `images`
--

DROP TABLE IF EXISTS `images`;
CREATE TABLE IF NOT EXISTS `images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `voiture_id` int DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `is_cover` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `voiture_id` (`voiture_id`)
) ENGINE=MyISAM AUTO_INCREMENT=86 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `images`
--

INSERT INTO `images` (`id`, `voiture_id`, `image_url`, `is_cover`) VALUES
(1, 9, 'uploads/87c93aa33275b4c8c73637ad3fbee836.jpeg', 0),
(2, 9, 'uploads/195ccf1c6f16b4353f259502271aad39.jpg', 0),
(3, 9, 'uploads/659b67050d08b305ee74aa825d1bd764.jpg', 0),
(4, 9, 'uploads/1240x775-px-life-programmer-sports-wrestling-hd-art-wallpaper-preview.jpg', 0),
(5, 9, 'uploads/2020Ships_Pirate_black_flag_with_white_skull_149641_.jpg', 0),
(6, 10, 'uploads/rr1.jpg', 0),
(7, 10, 'uploads/rr2.jpg', 0),
(8, 10, 'uploads/rr3.jpg', 0),
(9, 10, 'uploads/rr4.jpg', 0),
(10, 10, 'uploads/rr5.jpg', 0),
(84, 37, 'uploads/vehicules/6813f51b94ac3_jeep.png', 1),
(83, 36, 'uploads/vehicules/6813f4d92ad20_elantra.png', 1),
(16, 12, 'uploads/audi.png', 0),
(17, 12, 'uploads/cc.jpg', 0),
(18, 12, 'uploads/misti.png', 0),
(19, 12, 'uploads/rr2.jpg', 0),
(20, 12, 'uploads/rr5.jpg', 0),
(21, 13, 'uploads/audi.png', 0),
(22, 13, 'uploads/jeep.png', 0),
(23, 13, 'uploads/misti.png', 0),
(24, 13, 'uploads/rr2.jpg', 0),
(25, 13, 'uploads/rr5.jpg', 0),
(26, 15, 'uploads/IMG-20240627-WA0026.jpg', 0),
(27, 15, 'uploads/IMG-20240627-WA0027.jpg', 0),
(28, 15, 'uploads/IMG-20240627-WA0028.jpg', 0),
(29, 15, 'uploads/IMG-20240627-WA0029.jpg', 0),
(30, 16, 'uploads/misti (1).jpg', 0),
(31, 16, 'uploads/misti (2).jpg', 0),
(32, 16, 'uploads/misti (3).jpg', 0),
(33, 16, 'uploads/misti (4).jpg', 0),
(34, 16, 'uploads/misti (5).jpg', 0),
(35, 17, 'uploads/misti (1).jpg', 0),
(36, 17, 'uploads/misti (2).jpg', 0),
(37, 17, 'uploads/misti (3).jpg', 0),
(38, 17, 'uploads/misti (4).jpg', 0),
(39, 17, 'uploads/misti (5).jpg', 0),
(40, 18, 'uploads/misti5.jpg', 0),
(41, 18, 'uploads/misti4.jpg', 0),
(42, 18, 'uploads/misti3.jpg', 0),
(43, 18, 'uploads/misti2.jpg', 0),
(44, 18, 'uploads/misti1.jpg', 0),
(45, 19, 'uploads/misti1.jpg', 1),
(46, 19, 'uploads/misti2.jpg', 0),
(47, 19, 'uploads/misti3.jpg', 0),
(48, 19, 'uploads/misti4.jpg', 0),
(49, 19, 'uploads/misti5.jpg', 0),
(50, 20, 'uploads/tucson (1).jpg', 1),
(51, 20, 'uploads/tucson (2).jpg', 0),
(52, 20, 'uploads/tucson (3).jpg', 0),
(53, 20, 'uploads/tucson (4).jpg', 0),
(54, 20, 'uploads/tucson (5).jpg', 0),
(55, 21, 'uploads/bmw (1).jpg', 1),
(56, 21, 'uploads/bmw (2).jpg', 0),
(57, 21, 'uploads/bmw (3).jpg', 0),
(58, 21, 'uploads/bmw (4).jpg', 0),
(59, 21, 'uploads/bmw (5).jpg', 0),
(60, 22, 'uploads/bmw (1).jpg', 1),
(61, 22, 'uploads/bmw (2).jpg', 0),
(62, 22, 'uploads/bmw (3).jpg', 0),
(63, 22, 'uploads/bmw (4).jpg', 0),
(64, 22, 'uploads/bmw (5).jpg', 0),
(65, 23, 'uploads/bmw (2).jpg', 1),
(66, 23, 'uploads/bmw (3).jpg', 0),
(67, 23, 'uploads/bmw (4).jpg', 0),
(68, 23, 'uploads/bmw (5).jpg', 0),
(69, 24, 'uploads/honda (1).jpg', 1),
(70, 24, 'uploads/honda (2).jpg', 0),
(71, 24, 'uploads/honda (3).jpg', 0),
(72, 24, 'uploads/honda (4).jpg', 0),
(73, 24, 'uploads/honda (5).jpg', 0),
(74, 25, 'uploads/dd.jpg', 1),
(75, 25, 'uploads/ddd.jpg', 0),
(76, 25, 'uploads/dddd.jpg', 0),
(77, 26, 'uploads/bmw (2).jpg', 1),
(78, 26, 'uploads/bmw (3).jpg', 0),
(79, 26, 'uploads/bmw (4).jpg', 0),
(80, 26, 'uploads/bmw (5).jpg', 0),
(81, 35, 'uploads/vehicules/6813e2cedfc57_audi.png', 1),
(82, 35, 'uploads/vehicules/6813e2cee43bb_elantra.png', 0);

-- --------------------------------------------------------

--
-- Structure de la table `louer`
--

DROP TABLE IF EXISTS `louer`;
CREATE TABLE IF NOT EXISTS `louer` (
  `id` int NOT NULL AUTO_INCREMENT,
  `marque` varchar(100) NOT NULL,
  `modele` varchar(100) NOT NULL,
  `prix_jour` decimal(10,2) NOT NULL,
  `disponibilite` tinyint(1) DEFAULT '1',
  `annee` int NOT NULL,
  `categorie` varchar(50) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `disponible` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=39 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `louer`
--

INSERT INTO `louer` (`id`, `marque`, `modele`, `prix_jour`, `disponibilite`, `annee`, `categorie`, `image_url`, `disponible`, `created_at`) VALUES
(37, 'jeep', 'wagon', 45000.00, 1, 2025, '4x4', NULL, 1, '2025-05-01 22:26:35'),
(36, 'elantra', 'camry', 30000.00, 1, 2021, 'Berline', NULL, 1, '2025-05-01 22:25:29'),
(35, 'mercedes', 'c400s+', 60000.00, 1, 2025, 'SUV', NULL, 1, '2025-05-01 21:08:30');

-- --------------------------------------------------------

--
-- Structure de la table `reservations`
--

DROP TABLE IF EXISTS `reservations`;
CREATE TABLE IF NOT EXISTS `reservations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `vehicule_id` int NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `prix_total` decimal(10,2) NOT NULL,
  `mode_paiement` enum('cash','online') NOT NULL,
  `statut` enum('en_attente','payé','annulé') NOT NULL DEFAULT 'en_attente',
  `transaction_id` varchar(255) DEFAULT NULL,
  `date_creation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `vehicule_id` (`vehicule_id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `reservations`
--

INSERT INTO `reservations` (`id`, `user_id`, `vehicule_id`, `date_debut`, `date_fin`, `prix_total`, `mode_paiement`, `statut`, `transaction_id`, `date_creation`) VALUES
(13, 17, 35, '2025-05-02', '2025-05-08', 420000.00, 'online', 'payé', '_g8jMzFxC', '2025-05-01 22:47:56'),
(11, 17, 36, '2025-05-16', '2025-05-29', 420000.00, 'online', 'payé', '-cL2AQjTX', '2025-05-01 22:28:28'),
(9, 17, 35, '2025-05-08', '2025-05-21', 840000.00, 'online', 'annulé', 'KavVgr5UC', '2025-05-01 22:13:38'),
(10, 17, 37, '2025-05-09', '2025-05-15', 315000.00, 'cash', 'en_attente', NULL, '2025-05-01 22:28:16'),
(14, 17, 36, '2025-05-07', '2025-05-13', 210000.00, 'cash', 'payé', NULL, '2025-05-06 23:41:18'),
(15, 17, 35, '2025-05-14', '2025-05-20', 420000.00, 'online', 'payé', 'se-c9aG8N', '2025-05-06 23:41:55');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `numero` varchar(20) DEFAULT NULL,
  `adresse` text,
  `mot_de_passe` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `nom`, `email`, `numero`, `adresse`, `mot_de_passe`, `created_at`) VALUES
(17, 'sidy samb', 'sambsidy287@gmail.com', '0151516084', 'marcory', '$2y$10$5tX5dpeyFirhZwGWvDTEB.XRp/VjsVHyhbxHQhDcXZlsrx03BHONa', '2025-05-01 22:12:49');

-- --------------------------------------------------------

--
-- Structure de la table `vendre`
--

DROP TABLE IF EXISTS `vendre`;
CREATE TABLE IF NOT EXISTS `vendre` (
  `id` int NOT NULL AUTO_INCREMENT,
  `marque` varchar(100) NOT NULL,
  `modele` varchar(100) NOT NULL,
  `annee` int NOT NULL,
  `kilometrage` int NOT NULL,
  `prix` decimal(10,2) NOT NULL,
  `prix_negociable` tinyint(1) DEFAULT '0',
  `carburant` varchar(50) NOT NULL,
  `transmission` varchar(50) NOT NULL,
  `description` text,
  `image_ext` varchar(10) DEFAULT 'jpg',
  `places` int NOT NULL,
  `couleur` varchar(50) NOT NULL,
  `disponible` tinyint(1) DEFAULT '1',
  `date_ajout` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `vendre`
--

INSERT INTO `vendre` (`id`, `marque`, `modele`, `annee`, `kilometrage`, `prix`, `prix_negociable`, `carburant`, `transmission`, `description`, `image_ext`, `places`, `couleur`, `disponible`, `date_ajout`) VALUES
(6, 'mercedes', 'c300', 2025, 2, 55000000.00, 0, 'Diesel', 'Automatique', 'belle b', 'jpg', 5, 'blanche', 1, '2025-05-01 23:57:31'),
(7, 'mercedes', 'c300', 2002, 0, 2600000.00, 0, 'Essence', 'Manuelle', 'belle', 'png', 5, 'verte', 1, '2025-05-02 00:18:00');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
