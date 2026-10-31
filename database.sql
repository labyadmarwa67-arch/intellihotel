-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : sam. 31 oct. 2026 à 15:24
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `intellihotel`
--

-- --------------------------------------------------------

--
-- Structure de la table `chambres`
--

CREATE TABLE `chambres` (
  `id` int(11) NOT NULL,
  `numero` int(11) DEFAULT NULL,
  `type` enum('Single','Double','Suite') DEFAULT NULL,
  `vue` enum('Mer','Jardin','Piscine','Ville') DEFAULT NULL,
  `prix_base` decimal(10,2) DEFAULT NULL,
  `etat` enum('vide','reservee','occupee') DEFAULT 'vide'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `chambres`
--

INSERT INTO `chambres` (`id`, `numero`, `type`, `vue`, `prix_base`, `etat`) VALUES
(1, 101, 'Single', 'Jardin', 350.00, 'reservee'),
(2, 102, 'Single', 'Ville', 400.00, 'reservee'),
(3, 103, 'Double', 'Piscine', 550.00, 'reservee'),
(4, 104, 'Double', 'Mer', 650.00, 'reservee'),
(5, 105, 'Suite', 'Mer', 1200.00, 'occupee'),
(6, 106, 'Suite', 'Mer', 1200.00, 'vide'),
(7, 107, 'Double', 'Jardin', 500.00, 'vide'),
(8, 108, 'Single', 'Mer', 450.00, 'reservee'),
(9, 201, 'Double', 'Piscine', 560.00, 'occupee'),
(10, 202, 'Double', 'Mer', 670.00, 'reservee'),
(11, 203, 'Suite', 'Ville', 1100.00, 'occupee'),
(12, 204, 'Single', 'Jardin', 360.00, 'occupee'),
(13, 205, 'Double', 'Piscine', 540.00, 'reservee'),
(14, 206, 'Suite', 'Mer', 1300.00, 'reservee'),
(15, 207, 'Double', 'Jardin', 510.00, 'reservee'),
(16, 208, 'Single', 'Ville', 410.00, 'vide'),
(17, 301, 'Double', 'Piscine', 580.00, 'reservee'),
(18, 302, 'Suite', 'Mer', 1250.00, 'occupee'),
(19, 303, 'Single', 'Jardin', 370.00, 'reservee'),
(20, 304, 'Double', 'Ville', 520.00, 'reservee'),
(21, 188, 'Double', 'Piscine', 1500.00, 'reservee'),
(22, 199, 'Single', 'Jardin', 900.00, 'reservee'),
(23, 988, 'Double', 'Mer', 1040.00, 'reservee'),
(24, 258, 'Double', 'Mer', 832.00, 'reservee'),
(25, 450, 'Double', 'Piscine', 1104.00, 'vide'),
(26, 1222, 'Double', 'Mer', 832.00, 'reservee');

-- --------------------------------------------------------

--
-- Structure de la table `clients`
--

CREATE TABLE `clients` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `cin` varchar(20) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `clients`
--

INSERT INTO `clients` (`id`, `nom`, `cin`, `telephone`, `email`) VALUES
(1, 'Ahmed Benali', 'AB123', '0611111111', 'ahmed@gmail.com'),
(2, 'Sara Lahlou', 'CD456', '0622222222', 'sara@gmail.com'),
(3, 'Youssef Amrani', 'EF789', '0633333333', 'youssef@gmail.com'),
(4, 'Imane Idrissi', 'GH101', '0644444444', 'imane@gmail.com'),
(5, 'Khalid Naciri', 'IJ202', '0655555555', 'khalid@gmail.com'),
(6, 'Salma Chraibi', 'KL303', '0666666666', 'salma@gmail.com'),
(7, 'Omar Alaoui', 'MN404', '0677777777', 'omar@gmail.com'),
(8, 'Nadia Tazi', 'OP505', '0688888888', 'nadia@gmail.com'),
(9, 'Ayoub El Fassi', 'QR606', '0699999999', 'ayoub@gmail.com'),
(10, 'Meryem Saidi', 'ST707', '0600000000', 'meryem@gmail.com'),
(11, 'Hamza El Idrissi', 'UV808', '0612121212', 'hamza@gmail.com'),
(12, 'Soukaina Berrada', 'WX909', '0623232323', 'soukaina@gmail.com'),
(13, 'Reda Chami', 'YZ010', '0634343434', 'reda@gmail.com'),
(14, 'Hajar El Alaoui', 'AA111', '0645454545', 'hajar@gmail.com'),
(15, 'Anas Tazi', 'BB222', '0656565656', 'anas@gmail.com'),
(16, 'Lina Amrani', 'CC333', '0667676767', 'lina@gmail.com'),
(17, 'Mehdi Fassi', 'DD444', '0678787878', 'mehdi@gmail.com'),
(18, 'Chaimae Naciri', 'EE555', '0689898989', 'chaimae@gmail.com'),
(19, 'Zakaria Idrissi', 'FF666', '0690909090', 'zakaria@gmail.com'),
(20, 'Asmae Chraibi', 'GG777', '0601010101', 'asmae@gmail.com'),
(23, 'Sofia Bennani', 'SB1001', '0670000023', 'sofia@gmail.com'),
(24, 'Ahmed Benali', 'AB123456', '0612345678', 'ahmed@email.com'),
(25, 'Fatima Zahra', 'FZ789012', '0623456789', 'fatima@email.com'),
(26, 'Youssef Alami', 'YA345678', '0634567890', 'youssef@email.com'),
(27, 'Khadija Mouni', 'KM901234', '0645678901', 'khadija@email.com'),
(28, 'Hicham Zahidi', 'HZ1002', '0670000028', 'hicham@gmail.com'),
(29, 'Karim Lamrani', 'KL1003', '0670000029', 'karim@gmail.com'),
(30, 'Rachid Benkirane', 'RB1004', '0670000030', 'rachid@gmail.com'),
(31, 'Lamia Hariri', 'LH1005', '0670000031', 'lamia@gmail.com'),
(32, 'Salima Ouazzani', 'SO1006', '0670000032', 'salima@gmail.com'),
(33, 'Fatiha Rahmouni', 'FR1007', '0670000033', 'fatiha@gmail.com'),
(34, 'Samir Mahdi', 'SM1008', '0670000034', 'samir@gmail.com'),
(35, 'Meriem Jalal', 'MJ1009', '0670000035', 'meriem@gmail.com'),
(36, 'Mounir Nasri', 'MN1010', '0670000036', 'mounir@gmail.com'),
(37, 'Sanae Mouddi', 'SM1011', '0670000037', 'sanae@gmail.com'),
(38, 'Amal Labbaz', 'AL1012', '0670000038', 'amal@gmail.com');

-- --------------------------------------------------------

--
-- Structure de la table `maintenance`
--

CREATE TABLE `maintenance` (
  `id` int(11) NOT NULL,
  `chambre_id` int(11) DEFAULT NULL,
  `technicien_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `statut` enum('en_cours','terminee','prolongee') DEFAULT 'en_cours',
  `date_debut` date DEFAULT NULL,
  `date_fin` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `maintenance`
--

INSERT INTO `maintenance` (`id`, `chambre_id`, `technicien_id`, `description`, `statut`, `date_debut`, `date_fin`) VALUES
(1, 1, NULL, 'Climatisation en panne', 'terminee', '2026-03-01', '2026-03-02'),
(2, 2, NULL, 'Problème de plomberie', 'terminee', '2026-03-05', '2026-03-07'),
(3, 3, NULL, 'Télévision défectueuse', 'terminee', '2026-03-10', '2026-03-11'),
(4, 4, NULL, 'Connexion WiFi faible', 'terminee', '2026-03-15', '2026-03-16'),
(5, 5, NULL, 'Ampoule grillée', 'terminee', '2026-04-01', '2026-04-01'),
(6, 6, NULL, 'Fuite robinet salle de bain', 'terminee', '2026-04-05', '2026-04-06'),
(7, 7, NULL, 'Serrure porte défectueuse', 'terminee', '2026-04-10', '2026-04-11'),
(8, 8, NULL, 'Rideau cassé', 'terminee', '2026-04-15', '2026-04-15'),
(9, 1, NULL, 'Réparation matelas', 'terminee', '2026-04-20', '2026-04-21'),
(10, 2, NULL, 'Peinture mur abîmé', 'terminee', '2026-04-25', '2026-04-26'),
(11, 3, NULL, 'Réparation douche', 'en_cours', '2026-05-01', NULL),
(12, 4, NULL, 'Climatisation bruit anormal', 'en_cours', '2026-05-03', NULL),
(13, 5, NULL, 'Remplacement ampoules', 'terminee', '2026-05-02', '2026-05-09'),
(14, 6, NULL, 'Fuite plafond', 'terminee', '2026-04-28', '2026-05-09'),
(15, 7, NULL, 'Prise électrique défectueuse', 'terminee', '2026-05-05', '2026-05-08'),
(16, 4, NULL, ' Fuite d\'eau', 'en_cours', '2026-05-17', NULL),
(17, 9, 9, 'électricité', 'en_cours', '2026-05-20', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `paiements`
--

CREATE TABLE `paiements` (
  `id` int(11) NOT NULL,
  `reservation_id` int(11) DEFAULT NULL,
  `montant` decimal(10,2) DEFAULT NULL,
  `methode` enum('Especes','Carte bancaire','Virement') DEFAULT NULL,
  `statut` enum('en_attente','paye','retard') DEFAULT 'en_attente',
  `date_paiement` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `paiements`
--

INSERT INTO `paiements` (`id`, `reservation_id`, `montant`, `methode`, `statut`, `date_paiement`) VALUES
(1, 1, 1950.00, 'Especes', 'paye', '2026-01-08'),
(2, 2, 2750.00, 'Carte bancaire', 'paye', '2026-01-20'),
(3, 3, 7200.00, 'Virement', 'paye', '2026-02-05'),
(4, 4, 1600.00, 'Especes', 'paye', '2026-02-14'),
(5, 5, 3250.00, 'Carte bancaire', 'paye', '2026-02-25'),
(6, 6, 2750.00, 'Especes', 'paye', '2026-03-06'),
(7, 7, 9000.00, 'Virement', 'paye', '2026-03-15'),
(8, 8, 1600.00, 'Carte bancaire', 'paye', '2026-03-24'),
(9, 9, 3900.00, 'Especes', 'paye', '2026-04-05'),
(10, 10, 4125.00, 'Carte bancaire', 'paye', '2026-04-15'),
(11, 11, 9000.00, 'Virement', 'paye', '2026-04-25'),
(12, 12, 1500.00, 'Virement', 'paye', '2026-05-20'),
(13, 13, 3900.00, 'Especes', 'paye', '2026-05-20'),
(14, 14, 2750.00, 'Especes', 'paye', '2026-05-12'),
(15, 15, 9000.00, 'Especes', 'paye', '2026-05-08'),
(16, 1, 1400.00, 'Carte bancaire', 'paye', '2026-03-05'),
(17, 2, 2000.00, 'Especes', 'paye', '2026-03-15'),
(18, 3, 2200.00, 'Especes', 'paye', '2026-05-13'),
(19, 4, 3250.00, 'Especes', 'paye', '2026-05-12'),
(20, 6, 1400.00, 'Especes', 'paye', '2026-05-05'),
(21, 76, 12937.50, 'Virement', 'paye', '2026-05-12'),
(22, 78, 2340.00, 'Carte bancaire', 'paye', '2026-05-20'),
(23, 79, 7312.50, 'Especes', 'paye', '2026-05-12'),
(24, 1, 2500.00, 'Carte bancaire', 'paye', '2025-05-05'),
(25, 2, 3200.00, 'Especes', 'paye', '2025-05-15'),
(26, 5, 3900.00, 'Virement', 'paye', '2025-06-15'),
(27, 6, 3100.00, 'Carte bancaire', 'paye', '2025-05-07'),
(28, 7, 4200.00, 'Especes', 'paye', '2025-05-17'),
(29, 10, 2900.00, 'Carte bancaire', 'paye', '2025-06-17'),
(30, 11, 3800.00, 'Virement', 'paye', '2025-05-10'),
(31, 12, 5100.00, 'Especes', 'paye', '2025-05-20'),
(32, 15, 5600.00, 'Carte bancaire', 'paye', '2025-06-20'),
(33, 1, 2500.00, 'Carte bancaire', 'paye', '2025-05-05'),
(34, 2, 3200.00, 'Especes', 'paye', '2025-05-15'),
(35, 5, 3900.00, 'Virement', 'paye', '2025-06-15'),
(36, 6, 3100.00, 'Carte bancaire', 'paye', '2025-05-07'),
(37, 7, 4200.00, 'Especes', 'paye', '2025-05-17'),
(38, 10, 2900.00, 'Carte bancaire', 'paye', '2025-06-17'),
(39, 11, 3800.00, 'Virement', 'paye', '2025-05-10'),
(40, 12, 5100.00, 'Especes', 'paye', '2025-05-20'),
(41, 15, 5600.00, 'Carte bancaire', 'paye', '2025-06-20'),
(42, 179, 14850.00, 'Carte bancaire', 'paye', '2026-05-13'),
(43, 180, 10140.00, 'Carte bancaire', 'paye', '2026-05-13'),
(44, 181, 2250.00, 'Especes', 'paye', '2026-05-13'),
(45, 183, 6489.60, 'Carte bancaire', 'paye', '2026-05-20'),
(46, 184, 16380.00, 'Especes', 'paye', '2026-05-30'),
(47, 185, 15235.20, 'Carte bancaire', 'paye', '2026-07-22'),
(48, 186, 17139.60, 'Carte bancaire', 'paye', '2026-08-22'),
(49, 187, 9734.40, 'Especes', 'paye', '2026-09-20');

-- --------------------------------------------------------

--
-- Structure de la table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `chambre_id` int(11) DEFAULT NULL,
  `receptionniste_id` int(11) DEFAULT NULL,
  `date_debut` date DEFAULT NULL,
  `date_fin` date DEFAULT NULL,
  `statut` enum('en_attente','confirmee','checkin','checkout','annulee') DEFAULT 'confirmee',
  `prix_total` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `reservations`
--

INSERT INTO `reservations` (`id`, `client_id`, `chambre_id`, `receptionniste_id`, `date_debut`, `date_fin`, `statut`, `prix_total`) VALUES
(1, 1, 4, NULL, '2026-01-05', '2026-01-08', 'checkout', 1950.00),
(2, 2, 3, NULL, '2026-01-15', '2026-01-20', 'checkout', 2750.00),
(3, 3, 5, NULL, '2026-02-01', '2026-02-05', 'checkout', 7200.00),
(4, 4, 2, NULL, '2026-02-10', '2026-02-14', 'checkout', 1600.00),
(5, 5, 4, NULL, '2026-02-20', '2026-02-25', 'checkout', 3250.00),
(6, 1, 3, NULL, '2026-03-01', '2026-03-06', 'checkout', 2750.00),
(7, 2, 5, NULL, '2026-03-10', '2026-03-15', 'checkout', 9000.00),
(8, 3, 2, NULL, '2026-03-20', '2026-03-24', 'checkout', 1600.00),
(9, 4, 4, NULL, '2026-04-01', '2026-04-05', 'checkout', 3900.00),
(10, 5, 3, NULL, '2026-04-10', '2026-04-15', 'checkout', 4125.00),
(11, 1, 5, NULL, '2026-04-20', '2026-04-25', 'checkout', 9000.00),
(12, 2, 7, NULL, '2026-05-01', '2026-05-04', 'checkout', 1500.00),
(13, 3, 4, NULL, '2026-05-06', '2026-05-10', 'confirmee', 3900.00),
(14, 4, 3, NULL, '2026-05-12', '2026-05-16', 'confirmee', 2750.00),
(15, 5, 5, NULL, '2026-05-20', '2026-05-25', 'checkin', 9000.00),
(64, 22, 17, NULL, '2026-05-10', '2026-05-16', 'confirmee', 3480.00),
(65, 22, 2, NULL, '2026-05-10', '2026-05-16', 'confirmee', 2400.00),
(66, 22, 20, NULL, '2026-05-10', '2026-05-16', 'confirmee', 3120.00),
(67, 22, 2, NULL, '2026-05-10', '2026-05-16', 'confirmee', 2400.00),
(68, 23, 11, NULL, '2026-05-15', '2026-05-19', 'confirmee', 4400.00),
(69, 1, 1, NULL, '2026-03-01', '2026-03-05', 'checkout', 1400.00),
(70, 2, 2, NULL, '2026-03-10', '2026-03-15', 'checkout', 2000.00),
(71, 3, 3, NULL, '2026-04-01', '2026-04-05', 'checkin', 2200.00),
(72, 4, 4, NULL, '2026-04-10', '2026-04-15', 'confirmee', 3250.00),
(73, 1, 5, NULL, '2026-04-20', '2026-04-25', 'annulee', 6000.00),
(74, 2, 1, NULL, '2026-05-01', '2026-05-05', 'checkout', 1400.00),
(75, 28, 11, NULL, '2026-05-10', '2026-05-16', 'checkin', 6600.00),
(76, 31, 21, NULL, '2026-05-12', '2026-05-17', 'confirmee', 12937.50),
(77, 32, 14, NULL, '2026-05-15', '2026-05-31', 'confirmee', 20800.00),
(78, 16, 6, NULL, '2026-05-31', '2026-06-01', 'checkout', 2340.00),
(79, 10, 18, NULL, '2026-05-28', '2026-05-31', 'confirmee', 7312.50),
(80, 9, 12, NULL, '2026-03-23', '2026-05-20', 'checkin', 5358.00),
(81, 10, 18, NULL, '2026-04-20', '2026-05-16', 'checkout', 4375.00),
(82, 1, 2, NULL, '2026-04-16', '2026-05-31', 'checkout', 6639.00),
(83, 7, 6, NULL, '2026-03-19', '2026-05-25', 'checkin', 3379.00),
(84, 31, 11, NULL, '2026-03-15', '2026-05-13', 'checkin', 1674.00),
(85, 23, 18, NULL, '2026-05-08', '2026-05-14', 'checkin', 7624.00),
(86, 15, 8, NULL, '2026-05-03', '2026-05-27', 'confirmee', 6671.00),
(87, 28, 12, NULL, '2026-04-11', '2026-06-04', 'checkin', 1681.00),
(88, 26, 18, NULL, '2026-05-12', '2026-06-06', 'annulee', 1343.00),
(89, 17, 21, NULL, '2026-03-29', '2026-05-25', 'annulee', 6884.00),
(90, 5, 5, NULL, '2026-04-10', '2026-05-14', 'confirmee', 5003.00),
(91, 20, 7, NULL, '2026-03-30', '2026-05-18', 'checkin', 6412.00),
(92, 17, 14, NULL, '2026-05-05', '2026-06-03', 'checkout', 2707.00),
(93, 20, 10, NULL, '2026-05-04', '2026-06-11', 'checkin', 4306.00),
(94, 18, 14, NULL, '2026-04-10', '2026-06-10', 'annulee', 1931.00),
(95, 1, 16, NULL, '2026-03-26', '2026-05-30', 'checkin', 3991.00),
(96, 30, 8, NULL, '2026-03-21', '2026-06-11', 'checkin', 2939.00),
(97, 32, 12, NULL, '2026-05-02', '2026-05-20', 'annulee', 5999.00),
(98, 4, 13, NULL, '2026-04-01', '2026-05-16', 'checkout', 4672.00),
(99, 15, 4, NULL, '2026-03-17', '2026-05-13', 'checkout', 2470.00),
(100, 20, 8, NULL, '2026-04-07', '2026-05-19', 'annulee', 2990.00),
(101, 12, 9, NULL, '2026-03-29', '2026-05-14', 'annulee', 1049.00),
(102, 1, 11, NULL, '2026-03-30', '2026-05-25', 'annulee', 6757.00),
(103, 10, 6, NULL, '2026-05-04', '2026-05-16', 'checkin', 2180.00),
(104, 30, 21, NULL, '2026-04-11', '2026-05-30', 'checkin', 2567.00),
(111, 14, 1, 15, '2026-04-27', '2026-05-19', 'confirmee', 2618.00),
(112, 14, 1, 13, '2026-03-24', '2026-06-03', 'annulee', 1046.00),
(113, 14, 1, 14, '2026-04-13', '2026-06-06', 'checkin', 4879.00),
(114, 14, 2, 15, '2026-03-16', '2026-06-08', 'checkout', 4827.00),
(115, 14, 2, 13, '2026-04-07', '2026-06-08', 'checkin', 5412.00),
(116, 14, 2, 14, '2026-04-13', '2026-05-15', 'checkout', 7171.00),
(117, 14, 3, 15, '2026-04-30', '2026-05-31', 'annulee', 3449.00),
(118, 14, 3, 13, '2026-04-06', '2026-05-21', 'checkout', 5190.00),
(119, 14, 3, 14, '2026-03-26', '2026-06-02', 'annulee', 7913.00),
(120, 14, 4, 15, '2026-04-15', '2026-06-04', 'checkout', 2855.00),
(121, 14, 4, 13, '2026-04-02', '2026-05-18', 'annulee', 7145.00),
(122, 14, 4, 14, '2026-04-20', '2026-05-29', 'checkin', 5371.00),
(123, 14, 5, 15, '2026-04-26', '2026-05-18', 'confirmee', 1377.00),
(124, 14, 5, 13, '2026-04-23', '2026-05-30', 'annulee', 7359.00),
(125, 14, 5, 14, '2026-04-30', '2026-06-05', 'confirmee', 2464.00),
(126, 14, 6, 15, '2026-03-21', '2026-05-23', 'confirmee', 1956.00),
(127, 14, 6, 13, '2026-04-12', '2026-06-08', 'annulee', 7268.00),
(128, 14, 6, 14, '2026-04-30', '2026-06-04', 'checkout', 1463.00),
(129, 14, 7, 15, '2026-04-25', '2026-05-19', 'checkout', 2030.00),
(130, 14, 7, 13, '2026-05-09', '2026-05-14', 'checkin', 1730.00),
(131, 14, 7, 14, '2026-04-25', '2026-05-29', 'confirmee', 6857.00),
(132, 14, 8, 15, '2026-05-04', '2026-05-19', 'checkout', 4900.00),
(133, 14, 8, 13, '2026-05-06', '2026-05-17', 'checkin', 3391.00),
(134, 14, 8, 14, '2026-04-21', '2026-05-14', 'checkout', 1326.00),
(135, 14, 21, 15, '2026-04-13', '2026-05-18', 'checkout', 3205.00),
(136, 14, 21, 13, '2026-04-21', '2026-06-02', 'checkin', 2958.00),
(137, 14, 21, 14, '2026-03-18', '2026-06-01', 'checkout', 3838.00),
(138, 14, 9, 15, '2026-05-01', '2026-06-06', 'confirmee', 3870.00),
(139, 14, 9, 13, '2026-03-22', '2026-05-27', 'checkin', 6668.00),
(140, 14, 9, 14, '2026-04-09', '2026-05-14', 'confirmee', 4722.00),
(157, 1, 1, 13, '2025-05-01', '2025-05-05', 'checkout', 2500.00),
(158, 2, 2, 13, '2025-05-10', '2025-05-15', 'checkout', 3200.00),
(159, 3, 3, 13, '2025-05-20', '2025-05-25', 'checkin', 4500.00),
(160, 4, 4, 13, '2025-06-01', '2025-06-06', 'confirmee', 2800.00),
(161, 5, 5, 13, '2025-06-10', '2025-06-15', 'checkout', 3900.00),
(162, 6, 6, 14, '2025-05-02', '2025-05-07', 'checkout', 3100.00),
(163, 7, 7, 14, '2025-05-12', '2025-05-17', 'checkout', 4200.00),
(164, 8, 8, 14, '2025-05-22', '2025-05-27', 'checkin', 3600.00),
(165, 9, 9, 14, '2025-06-03', '2025-06-08', 'confirmee', 4800.00),
(166, 10, 10, 14, '2025-06-12', '2025-06-17', 'checkout', 2900.00),
(167, 11, 11, 14, '2025-06-12', '2025-06-19', 'checkout', 3599.00),
(168, 12, 12, 15, '2025-05-05', '2025-05-10', 'checkout', 3800.00),
(169, 13, 13, 15, '2025-05-15', '2025-05-20', 'checkout', 5100.00),
(170, 14, 14, 15, '2025-05-25', '2025-05-30', 'checkin', 4400.00),
(171, 15, 15, 15, '2025-06-05', '2025-06-10', 'annulee', 3200.00),
(172, 16, 16, 15, '2025-06-15', '2025-06-20', 'checkout', 5600.00),
(173, 1, 1, 13, '2026-05-13', '2026-05-18', 'confirmee', 2500.00),
(174, 2, 2, 13, '2026-05-13', '2026-05-20', 'confirmee', 3200.00),
(175, 3, 3, 14, '2026-05-13', '2026-05-16', 'confirmee', 2800.00),
(176, 4, 4, 14, '2026-05-13', '2026-05-19', 'confirmee', 3900.00),
(177, 5, 5, 15, '2026-05-13', '2026-05-17', 'confirmee', 4500.00),
(178, 6, 6, 15, '2026-05-13', '2026-05-21', 'confirmee', 3100.00),
(179, 33, 22, 3, '2026-05-13', '2026-05-24', 'confirmee', 14850.00),
(180, 34, 23, 13, '2026-05-23', '2026-05-28', 'confirmee', 10140.00),
(181, 6, 7, 13, '2026-06-08', '2026-06-11', 'checkout', 2250.00),
(182, 35, 1, NULL, '2026-05-16', '2026-05-31', 'confirmee', 5250.00),
(183, 36, 24, 15, '2026-05-14', '2026-05-18', 'checkout', 6489.60),
(184, 4, 5, 14, '2026-06-13', '2026-06-20', 'checkout', 16380.00),
(185, 37, 25, 13, '2026-07-22', '2026-07-30', 'checkout', 15235.20),
(186, 38, 25, 13, '2026-08-22', '2026-08-31', 'checkout', 17139.60),
(187, 10, 26, 15, '2026-09-20', '2026-09-26', 'confirmee', 9734.40);

-- --------------------------------------------------------

--
-- Structure de la table `reservation_receptionniste`
--

CREATE TABLE `reservation_receptionniste` (
  `reservation_id` int(11) NOT NULL,
  `receptionniste_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `reservation_receptionniste2`
--

CREATE TABLE `reservation_receptionniste2` (
  `reservation_id` int(11) NOT NULL,
  `receptionniste_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `saisons`
--

CREATE TABLE `saisons` (
  `id` int(11) NOT NULL,
  `nom` varchar(50) DEFAULT NULL,
  `mois_debut` int(11) DEFAULT NULL,
  `mois_fin` int(11) DEFAULT NULL,
  `coefficient` decimal(3,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `saisons`
--

INSERT INTO `saisons` (`id`, `nom`, `mois_debut`, `mois_fin`, `coefficient`) VALUES
(1, 'Haute Saison', 4, 10, 1.50),
(2, 'Basse Saison', 11, 3, 0.80);

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `mot_de_passe` varchar(255) DEFAULT NULL,
  `role` enum('admin','receptionniste','technicien') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `nom`, `email`, `mot_de_passe`, `role`) VALUES
(3, 'Admin Principal', 'admin@intellihotel.ma', '0192023a7bbd73250516f069df18b500', 'admin'),
(7, 'Youssef Техник', 'youssef.tech@intellihotel.ma', '7b591a2a55585e166465a838c28a2c5f', 'technicien'),
(8, 'Hassan Berrada', 'hassan.tech@intellihotel.ma', '617385f0235ebf49304e3b82eb703a8e', 'technicien'),
(9, 'Rachid Fassi', 'rachid.tech@intellihotel.ma', '83d36ab91edd94fb4ee05e2e308a6d02', 'technicien'),
(13, 'Mohamed Tazi', 'mohamed@intellihotel.com', '482c811da5d5b4bc6d497ffa98491e38', 'receptionniste'),
(14, 'Sara Amrani', 'sara@intellihotel.com', '482c811da5d5b4bc6d497ffa98491e38', 'receptionniste'),
(15, 'Khadija Saidi', 'khadija@intellihotel.com', '482c811da5d5b4bc6d497ffa98491e38', 'receptionniste');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `chambres`
--
ALTER TABLE `chambres`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero` (`numero`);

--
-- Index pour la table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cin` (`cin`);

--
-- Index pour la table `maintenance`
--
ALTER TABLE `maintenance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chambre_id` (`chambre_id`),
  ADD KEY `technicien_id` (`technicien_id`);

--
-- Index pour la table `paiements`
--
ALTER TABLE `paiements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reservation_id` (`reservation_id`);

--
-- Index pour la table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `chambre_id` (`chambre_id`),
  ADD KEY `receptionniste_id` (`receptionniste_id`);

--
-- Index pour la table `reservation_receptionniste`
--
ALTER TABLE `reservation_receptionniste`
  ADD PRIMARY KEY (`reservation_id`,`receptionniste_id`),
  ADD KEY `receptionniste_id` (`receptionniste_id`);

--
-- Index pour la table `reservation_receptionniste2`
--
ALTER TABLE `reservation_receptionniste2`
  ADD PRIMARY KEY (`reservation_id`,`receptionniste_id`),
  ADD KEY `receptionniste_id` (`receptionniste_id`);

--
-- Index pour la table `saisons`
--
ALTER TABLE `saisons`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `unique_nom_role` (`nom`,`role`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `chambres`
--
ALTER TABLE `chambres`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT pour la table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT pour la table `maintenance`
--
ALTER TABLE `maintenance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT pour la table `paiements`
--
ALTER TABLE `paiements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT pour la table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=188;

--
-- AUTO_INCREMENT pour la table `saisons`
--
ALTER TABLE `saisons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `maintenance`
--
ALTER TABLE `maintenance`
  ADD CONSTRAINT `maintenance_ibfk_1` FOREIGN KEY (`chambre_id`) REFERENCES `chambres` (`id`),
  ADD CONSTRAINT `maintenance_ibfk_2` FOREIGN KEY (`technicien_id`) REFERENCES `utilisateurs` (`id`);

--
-- Contraintes pour la table `paiements`
--
ALTER TABLE `paiements`
  ADD CONSTRAINT `paiements_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`);

--
-- Contraintes pour la table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`chambre_id`) REFERENCES `chambres` (`id`),
  ADD CONSTRAINT `reservations_ibfk_3` FOREIGN KEY (`receptionniste_id`) REFERENCES `utilisateurs` (`id`);

--
-- Contraintes pour la table `reservation_receptionniste`
--
ALTER TABLE `reservation_receptionniste`
  ADD CONSTRAINT `reservation_receptionniste_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`),
  ADD CONSTRAINT `reservation_receptionniste_ibfk_2` FOREIGN KEY (`receptionniste_id`) REFERENCES `utilisateurs` (`id`),
  ADD CONSTRAINT `reservation_receptionniste_ibfk_3` FOREIGN KEY (`receptionniste_id`) REFERENCES `utilisateurs` (`id`);

--
-- Contraintes pour la table `reservation_receptionniste2`
--
ALTER TABLE `reservation_receptionniste2`
  ADD CONSTRAINT `reservation_receptionniste2_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`),
  ADD CONSTRAINT `reservation_receptionniste2_ibfk_2` FOREIGN KEY (`receptionniste_id`) REFERENCES `utilisateurs` (`id`),
  ADD CONSTRAINT `reservation_receptionniste2_ibfk_3` FOREIGN KEY (`receptionniste_id`) REFERENCES `utilisateurs` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
