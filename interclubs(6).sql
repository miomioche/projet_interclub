-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : sam. 06 sep. 2025 à 15:56
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
-- Base de données : `interclubs`
--

-- --------------------------------------------------------

--
-- Structure de la table `adversaires`
--

CREATE TABLE `adversaires` (
  `id` int(11) NOT NULL,
  `nom` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `adversaires`
--

INSERT INTO `adversaires` (`id`, `nom`) VALUES
(1, 'Ent. Sport. Badminton Courrières 4'),
(2, 'Speed Bad Club 3');

-- --------------------------------------------------------

--
-- Structure de la table `classements`
--

CREATE TABLE `classements` (
  `id` int(11) NOT NULL,
  `joueur_id` int(11) DEFAULT NULL,
  `date_classement` date DEFAULT NULL,
  `simple` int(11) DEFAULT NULL,
  `double` int(11) DEFAULT NULL,
  `mixte` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `classement_equipes`
--

CREATE TABLE `classement_equipes` (
  `id` int(11) NOT NULL,
  `saison` varchar(9) NOT NULL,
  `division` varchar(10) NOT NULL,
  `poule` varchar(50) NOT NULL,
  `rang` int(11) NOT NULL,
  `equipe` varchar(120) NOT NULL,
  `jouees` int(11) NOT NULL,
  `gagnees` int(11) NOT NULL,
  `nulles` int(11) NOT NULL,
  `perdues` int(11) NOT NULL,
  `forfaits` int(11) NOT NULL,
  `bonus` int(11) NOT NULL,
  `penalites` int(11) NOT NULL,
  `points` int(11) NOT NULL,
  `matchs_diff` int(11) NOT NULL,
  `sets_diff` int(11) NOT NULL,
  `pts_diff` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `classement_equipes`
--

INSERT INTO `classement_equipes` (`id`, `saison`, `division`, `poule`, `rang`, `equipe`, `jouees`, `gagnees`, `nulles`, `perdues`, `forfaits`, `bonus`, `penalites`, `points`, `matchs_diff`, `sets_diff`, `pts_diff`) VALUES
(1, '2024-2025', 'D6', 'Poule 7', 1, 'Speed Bad Club 3 (62-SBC-7)', 14, 11, 1, 2, 0, 0, 0, 37, 36, 73, 745),
(2, '2024-2025', 'D6', 'Poule 7', 2, 'La Souchezoise 3 (62-SZ-7)', 14, 11, 1, 2, 0, 0, 0, 37, 32, 61, 636),
(3, '2024-2025', 'D6', 'Poule 7', 3, 'Badminton Club Arras 6 (62-BCA-7)', 14, 11, 0, 3, 0, 0, 0, 36, 32, 52, 567),
(4, '2024-2025', 'D6', 'Poule 7', 4, 'Volant Opale Club 3 (62-VOC-7)', 14, 9, 2, 3, 0, 0, 0, 34, 26, 44, 593),
(5, '2024-2025', 'D6', 'Poule 7', 5, 'Vitry Badminton Club 2 (62-VABC-7)', 14, 8, 3, 3, 0, 0, 0, 33, 14, 28, 145),
(6, '2024-2025', 'D6', 'Poule 7', 6, 'Ent. Sport. Badminton Courrières 4 (62-ESBC-7)', 14, 8, 2, 4, 0, 0, 0, 32, 20, 34, 417),
(7, '2024-2025', 'D6', 'Poule 7', 7, 'Longuenesse Badminton Club 4 (62-LBC-7)', 14, 6, 4, 4, 0, 0, 0, 30, 2, 2, -6),
(8, '2024-2025', 'D6', 'Poule 7', 8, 'Calais Badminton Club 4 (62-CBC-7)', 14, 6, 1, 7, 0, 0, 0, 27, -14, -21, -234),
(9, '2024-2025', 'D6', 'Poule 7', 9, 'Leforest Badminton Club 5 (62-LBCL-7)', 14, 5, 2, 7, 0, 0, 0, 26, -6, -13, -133),
(10, '2024-2025', 'D6', 'Poule 7', 10, 'C B Montreuil 3 (62-CBM-7)', 14, 4, 3, 7, 0, 0, 0, 25, -12, -18, -186),
(11, '2024-2025', 'D6', 'Poule 7', 11, 'A.b.st Etienne Au Mont 6 (62-ABS-7)', 14, 4, 2, 8, 0, 0, 0, 24, -20, -34, -590),
(12, '2024-2025', 'D6', 'Poule 7', 12, 'LE VOLANT AIROIS 3 (62-LVA-7)', 14, 3, 2, 9, 0, 0, 0, 22, -18, -35, -271),
(13, '2024-2025', 'D6', 'Poule 7', 13, 'Association Sports et Détente de Beaurains 2 (62-ASDB-7)', 14, 1, 4, 9, 0, 0, 0, 20, -28, -54, -529),
(14, '2024-2025', 'D6', 'Poule 7', 14, 'Audruicq Badminton Club 2 (62-ABC-7)', 14, 2, 1, 11, 0, 0, 0, 19, -26, -42, -430),
(15, '2024-2025', 'D6', 'Poule 7', 15, 'Calais Badminton Club 5 (62-CBC-7)', 14, 2, 0, 12, 0, 0, 0, 18, -38, -77, -724);

-- --------------------------------------------------------

--
-- Structure de la table `clubs`
--

CREATE TABLE `clubs` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `short_name` varchar(20) NOT NULL,
  `city` varchar(80) NOT NULL,
  `logo_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `clubs`
--

INSERT INTO `clubs` (`id`, `name`, `short_name`, `city`, `logo_url`) VALUES
(1, 'Badminton Club Arras', 'BCA', 'Arras', NULL),
(2, 'Speed Bad Club', 'SBC', 'Lens', NULL),
(3, 'La Souchezoise', 'SZ', 'Souchez', NULL),
(4, 'Association Sports et Détente de Beaurains', 'ASDB', 'Beaurains', NULL),
(5, 'Vitry Badminton Club', 'VABC', 'Vitry-en-Artois', NULL),
(6, 'Calais Badminton Club', 'CBC', 'Calais', NULL),
(7, 'Audruicq Badminton Club', 'ABC', 'Audruicq', NULL),
(8, 'Volant Opale Club', 'VOC', 'Boulogne-sur-Mer', NULL),
(9, 'Leforest Badminton Club', 'LBCL', 'Leforest', NULL),
(10, 'LE VOLANT AIROIS', 'LVA', 'Aire-sur-la-Lys', NULL),
(11, 'A.b.st Etienne Au Mont', 'ABS', 'Saint-Étienne-au-Mont', NULL),
(12, 'Ent. Sport. Badminton Courrières', 'ESBC', 'Courrières', NULL),
(13, 'Longuenesse Badminton Club', 'LBC', 'Longuenesse', NULL),
(14, 'C B Montreuil', 'CBM', 'Montreuil', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `fixtures`
--

CREATE TABLE `fixtures` (
  `id` int(11) NOT NULL,
  `season` varchar(9) NOT NULL,
  `competition` varchar(120) DEFAULT NULL,
  `matchday` int(11) DEFAULT NULL,
  `date_time` datetime NOT NULL,
  `venue_name` varchar(120) DEFAULT NULL,
  `venue_city` varchar(80) DEFAULT NULL,
  `home_team_id` int(11) NOT NULL,
  `away_team_id` int(11) NOT NULL,
  `status` enum('scheduled','played','postponed','forfeit') NOT NULL DEFAULT 'scheduled',
  `score_home` tinyint(4) DEFAULT NULL,
  `score_away` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `fixtures`
--

INSERT INTO `fixtures` (`id`, `season`, `competition`, `matchday`, `date_time`, `venue_name`, `venue_city`, `home_team_id`, `away_team_id`, `status`, `score_home`, `score_away`) VALUES
(9, '2024-2025', 'D6 Poule 7', 1, '2024-11-17 11:30:00', 'La Sensée', 'Corbehem', 1, 4, 'played', 4, 2),
(10, '2024-2025', 'D6 Poule 7', 1, '2024-11-17 14:00:00', 'La Sensée', 'Corbehem', 1, 5, 'played', 1, 5),
(11, '2024-2025', 'D6 Poule 7', 2, '2024-12-01 09:00:00', 'Salle Calypso', 'Calais', 1, 3, 'played', 4, 2),
(12, '2024-2025', 'D6 Poule 7', 2, '2024-12-01 11:30:00', 'Salle Calypso', 'Calais', 1, 6, 'played', 5, 1),
(13, '2024-2025', 'D6 Poule 7', 2, '2024-12-01 14:00:00', 'Salle Calypso', 'Calais', 1, 8, 'played', 5, 1),
(14, '2024-2025', 'D6 Poule 7', 3, '2025-01-26 09:00:00', 'Salle Branly', 'Boulogne-sur-Mer', 1, 7, 'played', 6, 0),
(15, '2024-2025', 'D6 Poule 7', 3, '2025-01-26 14:00:00', 'Salle Branly', 'Boulogne-sur-Mer', 1, 9, 'played', 4, 2),
(16, '2024-2025', 'D6 Poule 7', 4, '2025-02-16 09:00:00', 'Salle Jacques Duclos', 'Guesnain', 1, 10, 'played', 5, 1),
(17, '2024-2025', 'D6 Poule 7', 4, '2025-02-16 11:30:00', 'Salle Jacques Duclos', 'Guesnain', 1, 11, 'played', 6, 0),
(18, '2024-2025', 'D6 Poule 7', 4, '2025-02-16 14:00:00', 'Salle Jacques Duclos', 'Guesnain', 1, 12, 'played', 6, 0),
(19, '2024-2025', 'D6 Poule 7', 5, '2025-03-02 09:00:00', 'Mitterrand', 'Arras', 1, 13, 'played', 4, 2),
(20, '2024-2025', 'D6 Poule 7', 5, '2025-03-02 11:30:00', 'Mitterrand', 'Arras', 1, 14, 'played', 2, 4),
(21, '2024-2025', 'D6 Poule 7', 5, '2025-03-02 14:00:00', 'Mitterrand', 'Arras', 1, 15, 'played', 4, 2),
(22, '2024-2025', 'D6', 4, '2025-02-23 11:30:00', 'Salle Jacques DUCLOS', 'Vitry en Artois', 11, 1, 'played', 0, 6),
(23, '2024-2025', 'D6', 4, '2025-02-23 14:00:00', 'Salle Jacques DUCLOS', 'Vitry en Artois', 12, 1, 'played', 0, 6);

-- --------------------------------------------------------

--
-- Structure de la table `interviews`
--

CREATE TABLE `interviews` (
  `id` int(11) NOT NULL,
  `joueur_id` int(11) DEFAULT NULL,
  `date_interview` date DEFAULT NULL,
  `contenu` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `joueurs`
--

CREATE TABLE `joueurs` (
  `id` int(11) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `licence` varchar(20) NOT NULL,
  `classement_simple` varchar(50) NOT NULL,
  `classement_double` varchar(50) NOT NULL,
  `classement_mixte` varchar(50) NOT NULL,
  `photo` varchar(255) NOT NULL,
  `date_ajout` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `victoires_simple` int(11) NOT NULL DEFAULT 0,
  `matches_simple` int(11) NOT NULL DEFAULT 0,
  `victoires_double` int(11) NOT NULL DEFAULT 0,
  `matches_double` int(11) NOT NULL DEFAULT 0,
  `victoires_mixte` int(11) NOT NULL DEFAULT 0,
  `matches_mixte` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `joueurs`
--

INSERT INTO `joueurs` (`id`, `nom`, `prenom`, `licence`, `classement_simple`, `classement_double`, `classement_mixte`, `photo`, `date_ajout`, `victoires_simple`, `matches_simple`, `victoires_double`, `matches_double`, `victoires_mixte`, `matches_mixte`) VALUES
(1, 'DELANGUE', 'Baptiste', '07494875', 'D9 1061', 'P10 972', 'D9 1067', 'baptiste.jpeg', '2025-09-02 11:10:45', 0, 0, 0, 0, 0, 0),
(2, 'CORNET', 'Constance', '07032078', 'D9 777', 'P11 563', 'D9 1046', 'constance.jpeg', '2025-09-02 11:13:44', 0, 0, 0, 0, 0, 0),
(3, 'DOHEN', 'Marie', '07440250', 'D9 784', 'P10 607', 'D9 1087', 'marie.jpeg', '2025-09-02 11:13:44', 0, 0, 0, 0, 0, 0),
(4, 'DELATTRE', 'Pierre', '07492441', 'D9 1089', 'P10 976', 'P10 854', 'pierre.jpeg', '2025-09-02 11:13:44', 0, 0, 0, 0, 0, 0),
(5, 'DELEFOSSE', 'Vincent', '07485284', 'D9 1097', 'P10 889', 'D9 1023', 'vincent.jpeg', '2025-09-02 11:13:44', 0, 0, 0, 0, 0, 0),
(6, 'HOMBERT', 'Antoine', '00629197', 'D9 1106', 'P10 982', 'P10 830', 'antoine.jpeg', '2025-09-02 11:13:44', 0, 0, 0, 0, 0, 0),
(7, 'TERGEMINA', 'Benoît', '07439195', 'D9 1007', 'P10 967', 'P10 807', 'benito.jpeg', '2025-09-02 11:13:44', 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Structure de la table `lieux`
--

CREATE TABLE `lieux` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `adresse` varchar(255) NOT NULL,
  `code_postal` varchar(10) NOT NULL,
  `ville` varchar(100) NOT NULL,
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `lieux`
--

INSERT INTO `lieux` (`id`, `nom`, `adresse`, `code_postal`, `ville`, `latitude`, `longitude`) VALUES
(1, 'Complexe sportif de la Sensée', 'Complexe sportif de la Sensée, 62112 CORBEHEM', '62112', 'Corbehem', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `matchs`
--

CREATE TABLE `matchs` (
  `id` int(11) NOT NULL,
  `rencontre_id` int(11) DEFAULT NULL,
  `type_match` enum('Simple Homme','Simple Dame','Double Hommes','Double Dames','Double Mixte') DEFAULT NULL,
  `joueur1_id` int(11) DEFAULT NULL,
  `joueur2_id` int(11) DEFAULT NULL,
  `score` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `match_details`
--

CREATE TABLE `match_details` (
  `id` int(11) NOT NULL,
  `joueur_id` int(11) NOT NULL,
  `fixture_id` int(11) DEFAULT NULL,
  `adversaire_id` int(11) DEFAULT NULL,
  `nom_adversaire` varchar(100) DEFAULT NULL,
  `opponent_team` varchar(120) DEFAULT NULL,
  `binome` varchar(255) DEFAULT NULL,
  `date_match` datetime NOT NULL,
  `journee` tinyint(4) DEFAULT NULL,
  `lieu` varchar(150) DEFAULT NULL,
  `score` varchar(50) DEFAULT NULL,
  `resultat` enum('victoire','défaite') DEFAULT NULL,
  `type_match` enum('simple','double','mixte') DEFAULT NULL,
  `is_prochain` tinyint(1) DEFAULT 0,
  `discipline_code` varchar(3) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `match_details`
--

INSERT INTO `match_details` (`id`, `joueur_id`, `fixture_id`, `adversaire_id`, `nom_adversaire`, `opponent_team`, `binome`, `date_match`, `journee`, `lieu`, `score`, `resultat`, `type_match`, `is_prochain`, `discipline_code`) VALUES
(123, 1, 9, NULL, 'Nathan ZANELLI', 'ASDB 2', NULL, '2024-11-17 11:30:00', 1, 'Complexe sportif de la Sensée, 62112 Corbehem', '21-7 21-14', 'victoire', 'simple', 0, NULL),
(124, 4, 9, NULL, 'Alexis KWIATEK', 'ASDB 2', NULL, '2024-11-17 11:30:00', 1, 'Complexe sportif de la Sensée, 62112 Corbehem', '21-7 21-11', 'victoire', 'simple', 0, NULL),
(125, 7, 9, NULL, 'Romain PLOUVIEZ', 'ASDB 2', NULL, '2024-11-17 11:30:00', 1, 'Complexe sportif de la Sensée, 62112 Corbehem', '11-21 7-21', 'défaite', 'simple', 0, NULL),
(126, 3, 9, NULL, 'Sandrine HOLLANDE', 'ASDB 2', NULL, '2024-11-17 11:30:00', 1, 'Complexe sportif de la Sensée, 62112 Corbehem', '21-0 21-4', 'victoire', 'simple', 0, NULL),
(128, 7, 9, NULL, 'Jérémi FOVELLE & Alexis KWIATEK', 'ASDB 2', 'Antoine HOMBERT', '2024-11-17 11:30:00', 1, 'Complexe sportif de la Sensée, 62112 Corbehem', '21-11 21-7', 'victoire', 'double', 0, NULL),
(130, 3, 9, NULL, 'Romain PLOUVIEZ & Pauline DRANCOURT', 'ASDB 2', 'Baptiste DELANGUE', '2024-11-17 11:30:00', 1, 'Complexe sportif de la Sensée, 62112 Corbehem', '14-21 12-21', 'défaite', 'mixte', 0, 'XD'),
(131, 4, NULL, NULL, 'Florent DELRUE', 'VABC 2', NULL, '2024-11-17 14:00:00', 1, 'Complexe sportif de la Sensée, 62112 Corbehem', '16-21 15-21', 'défaite', 'simple', 0, NULL),
(132, 7, NULL, NULL, 'Remy FREVILLE', 'VABC 2', NULL, '2024-11-17 14:00:00', 1, 'Complexe sportif de la Sensée, 62112 Corbehem', '7-21 11-21', 'défaite', 'simple', 0, NULL),
(133, 6, NULL, NULL, 'Julian VAN AUTREEVE', 'VABC 2', NULL, '2024-11-17 14:00:00', 1, 'Complexe sportif de la Sensée, 62112 Corbehem', '21-9 21-3', 'victoire', 'simple', 0, NULL),
(134, 3, NULL, NULL, 'Eloïse CHARMETANT', 'VABC 2', NULL, '2024-11-17 14:00:00', 1, 'Complexe sportif de la Sensée, 62112 Corbehem', '16-21 16-21', 'défaite', 'simple', 0, NULL),
(135, 4, NULL, NULL, 'Remy FREVILLE & Sullivan LEFEBVRE', 'VABC 2', 'Antoine HOMBERT', '2024-11-17 14:00:00', 1, 'Complexe sportif de la Sensée, 62112 Corbehem', '13-21 15-21', 'défaite', 'double', 0, NULL),
(137, 1, NULL, NULL, 'Florent DELRUE & Maëla VAN EECKHOUTTE', 'VABC 2', 'Marie DOHEN', '2024-11-17 14:00:00', 1, 'Complexe sportif de la Sensée, 62112 Corbehem', '22-24 17-21', 'défaite', 'mixte', 0, 'XD'),
(139, 1, 11, NULL, 'Kilian DOUTREMEPUICH', 'SZ 3', NULL, '2024-12-01 09:00:00', 2, 'Complexe Sportif Calypso, 62100 Calais', '17-21 8-21', 'défaite', 'simple', 0, NULL),
(140, 7, 11, NULL, 'Axel ROUSSEL', 'SZ 3', NULL, '2024-12-01 09:00:00', 2, 'Complexe Sportif Calypso, 62100 Calais', '21-13 21-15', 'victoire', 'simple', 0, NULL),
(141, 6, 11, NULL, 'Gauthier HAUTECOEUR', 'SZ 3', NULL, '2024-12-01 09:00:00', 2, 'Complexe Sportif Calypso, 62100 Calais', '21-13 21-14', 'victoire', 'simple', 0, NULL),
(142, 3, 11, NULL, 'Lalie VERHEYDE', 'SZ 3', NULL, '2024-12-01 09:00:00', 2, 'Complexe Sportif Calypso, 62100 Calais', '21-8 24-22', 'victoire', 'simple', 0, NULL),
(144, 7, 11, NULL, 'Michel HENEMAN & Axel ROUSSEL', 'SZ 3', 'Antoine HOMBERT', '2024-12-01 09:00:00', 2, 'Complexe Sportif Calypso, 62100 Calais', '16-21 14-21', 'défaite', 'double', 0, NULL),
(146, 2, 11, NULL, 'Gauthier HAUTECOEUR & Elodie WARNIER', 'SZ 3', 'Pierre DELATTRE', '2024-12-01 09:00:00', 2, 'Complexe Sportif Calypso, 62100 Calais', '22-20 21-19', 'victoire', 'mixte', 0, 'XD'),
(147, 1, 12, NULL, 'Romain JOAN', 'CBC 4', NULL, '2024-12-01 11:30:00', 2, 'Complexe Sportif Calypso, 62100 Calais', '16-21 6-21', 'défaite', 'simple', 0, NULL),
(148, 4, 12, NULL, 'Louis DERNIS', 'CBC 4', NULL, '2024-12-01 11:30:00', 2, 'Complexe Sportif Calypso, 62100 Calais', '21-16 21-12', 'victoire', 'simple', 0, NULL),
(149, 6, 12, NULL, 'Sylvain LEMAITRE', 'CBC 4', NULL, '2024-12-01 11:30:00', 2, 'Complexe Sportif Calypso, 62100 Calais', '21-10 21-9', 'victoire', 'simple', 0, NULL),
(150, 2, 12, NULL, 'Valentine DRIN', 'CBC 4', NULL, '2024-12-01 11:30:00', 2, 'Complexe Sportif Calypso, 62100 Calais', '21-2 21-3', 'victoire', 'simple', 0, NULL),
(151, 4, 12, NULL, 'Louis DERNIS & Romain JOAN', 'CBC 4', 'Benoît TERGEMINA', '2024-12-01 11:30:00', 2, 'Complexe Sportif Calypso, 62100 Calais', '21-18 7-21 21-18', 'victoire', 'double', 0, NULL),
(153, 1, 12, NULL, 'Sylvain LEMAITRE & Candice DELRUE', 'CBC 4', 'Marie DOHEN', '2024-12-01 11:30:00', 2, 'Complexe Sportif Calypso, 62100 Calais', '9-21 21-18 21-18', 'victoire', 'mixte', 0, 'XD'),
(155, 4, 13, NULL, 'Jean Charles VERVA', 'ABC 2', NULL, '2024-12-01 14:00:00', 2, 'Complexe Sportif Calypso, 62100 Calais', '21-11 21-11', 'victoire', 'simple', 0, NULL),
(156, 7, 13, NULL, 'Noa VERVA', 'ABC 2', NULL, '2024-12-01 14:00:00', 2, 'Complexe Sportif Calypso, 62100 Calais', '17-21 22-20 21-16', 'victoire', 'simple', 0, NULL),
(157, 6, 13, NULL, 'Pierre FIEVEZ', 'ABC 2', NULL, '2024-12-01 14:00:00', 2, 'Complexe Sportif Calypso, 62100 Calais', '16-21 21-10 21-14', 'victoire', 'simple', 0, NULL),
(158, 2, 13, NULL, 'Lilou FABRE', 'ABC 2', NULL, '2024-12-01 14:00:00', 2, 'Complexe Sportif Calypso, 62100 Calais', '11-21 21-10 14-21', 'défaite', 'simple', 0, NULL),
(159, 4, 13, NULL, 'Pierre FIEVEZ & Noa VERVA', 'ABC 2', 'Benoît TERGEMINA', '2024-12-01 14:00:00', 2, 'Complexe Sportif Calypso, 62100 Calais', '21-13 23-21', 'victoire', 'double', 0, NULL),
(161, 1, 13, NULL, 'Benjamin CHRETIEN & Laurine HELLEC', 'ABC 2', 'Marie DOHEN', '2024-12-01 14:00:00', 2, 'Complexe Sportif Calypso, 62100 Calais', '21-12 21-16', 'victoire', 'mixte', 0, 'XD'),
(163, 6, NULL, NULL, 'Samuel LOUGEZ', 'SBC 3', NULL, '2025-01-26 09:00:00', 3, 'Salle du Lycée Branly, 62200 Boulogne-sur-Mer', '9-21 14-21', 'défaite', 'simple', 0, NULL),
(164, 1, NULL, NULL, 'Hugo HEDOIRE', 'SBC 3', NULL, '2025-01-26 09:00:00', 3, 'Salle du Lycée Branly, 62200 Boulogne-sur-Mer', '21-18 16-21 21-15', 'victoire', 'simple', 0, NULL),
(165, 7, NULL, NULL, 'Vincent BRASSEUR', 'SBC 3', NULL, '2025-01-26 09:00:00', 3, 'Salle du Lycée Branly, 62200 Boulogne-sur-Mer', '12-21 13-21', 'défaite', 'simple', 0, NULL),
(166, 3, NULL, NULL, 'Pauline LUCAS', 'SBC 3', NULL, '2025-01-26 09:00:00', 3, 'Salle du Lycée Branly, 62200 Boulogne-sur-Mer', '18-21 19-21', 'défaite', 'simple', 0, NULL),
(168, 7, NULL, NULL, 'Vincent BRASSEUR & Bruno SAUVAGE', 'SBC 3', 'Baptiste DELANGUE', '2025-01-26 09:00:00', 3, 'Salle du Lycée Branly, 62200 Boulogne-sur-Mer', '13-21 10-21', 'défaite', 'double', 0, NULL),
(170, 2, NULL, NULL, 'Bruno SAUVAGE & Pauline LUCAS', 'SBC 3', 'Vincent DELEFOSSE', '2025-01-26 09:00:00', 3, 'Salle du Lycée Branly, 62200 Boulogne-sur-Mer', '21-14 8-21 24-22', 'victoire', 'mixte', 0, 'XD'),
(171, 6, NULL, NULL, 'Benjamin THUEUX', 'CBC 5', NULL, '2025-01-26 11:30:00', 3, 'Salle du Lycée Branly, 62200 Boulogne-sur-Mer', '21-18 21-11', 'victoire', 'simple', 0, NULL),
(172, 5, NULL, NULL, 'Léo MAEGHT', 'CBC 5', NULL, '2025-01-26 11:30:00', 3, 'Salle du Lycée Branly, 62200 Boulogne-sur-Mer', '21-10 21-13', 'victoire', 'simple', 0, NULL),
(173, 7, NULL, NULL, 'Henry BEDOY', 'CBC 5', NULL, '2025-01-26 11:30:00', 3, 'Salle du Lycée Branly, 62200 Boulogne-sur-Mer', '21-7 21-13', 'victoire', 'simple', 0, NULL),
(174, 3, NULL, NULL, 'Stéphanie MALFAIT', 'CBC 5', NULL, '2025-01-26 11:30:00', 3, 'Salle du Lycée Branly, 62200 Boulogne-sur-Mer', '21-15 21-7', 'victoire', 'simple', 0, NULL),
(175, 1, NULL, NULL, 'Dominique GOIDIN & Benjamin THUEUX', 'CBC 5', 'Antoine HOMBERT', '2025-01-26 11:30:00', 3, 'Salle du Lycée Branly, 62200 Boulogne-sur-Mer', '21-15 22-20', 'victoire', 'double', 0, NULL),
(177, 5, NULL, NULL, 'Léo MAEGHT & Lily HAGNERE', 'CBC 5', 'Constance CORNET', '2025-01-26 11:30:00', 3, 'Salle du Lycée Branly, 62200 Boulogne-sur-Mer', '21-9 21-9', 'victoire', 'mixte', 0, 'XD'),
(179, 6, NULL, NULL, 'Bastien BIZE', 'VOC 3', NULL, '2025-01-26 14:00:00', 3, 'Salle du Lycée Branly, 62200 Boulogne-sur-Mer', '21-15 21-19', 'victoire', 'simple', 0, NULL),
(180, 1, NULL, NULL, 'Quentin ROLAND', 'VOC 3', NULL, '2025-01-26 14:00:00', 3, 'Salle du Lycée Branly, 62200 Boulogne-sur-Mer', '16-21 13-21', 'défaite', 'simple', 0, NULL),
(181, 5, NULL, NULL, 'Thierry LELEU', 'VOC 3', NULL, '2025-01-26 14:00:00', 3, 'Salle du Lycée Branly, 62200 Boulogne-sur-Mer', '21-18 19-21 21-16', 'victoire', 'simple', 0, NULL),
(182, 2, NULL, NULL, 'Alyssia RYSSEN', 'VOC 3', NULL, '2025-01-26 14:00:00', 3, 'Salle du Lycée Branly, 62200 Boulogne-sur-Mer', '21-14 21-10', 'victoire', 'simple', 0, NULL),
(183, 6, NULL, NULL, 'Bastien BIZE & Quentin ROLAND', 'VOC 3', 'Benoît TERGEMINA', '2025-01-26 14:00:00', 3, 'Salle du Lycée Branly, 62200 Boulogne-sur-Mer', '15-21 18-21', 'défaite', 'double', 0, NULL),
(185, 1, NULL, NULL, 'Thierry LELEU & Sandrine FUCHS', 'VOC 3', 'Marie DOHEN', '2025-01-26 14:00:00', 3, 'Salle du Lycée Branly, 62200 Boulogne-sur-Mer', '21-13 21-12', 'victoire', 'mixte', 0, 'XD'),
(187, 6, NULL, NULL, 'Adrien CACHERA', 'LBCL 5', NULL, '2025-02-23 09:00:00', 4, 'Salle Jacques DUCLOS, 62490 Vitry en Artois', '19-21 23-21 21-18', 'victoire', 'simple', 0, NULL),
(188, 5, NULL, NULL, 'Lucas HENNIQUAUT', 'LBCL 5', NULL, '2025-02-23 09:00:00', 4, 'Salle Jacques DUCLOS, 62490 Vitry en Artois', '13-21 21-14 21-16', 'victoire', 'simple', 0, NULL),
(189, 4, NULL, NULL, 'Thomas VAN BRUSSEL', 'LBCL 5', NULL, '2025-02-23 09:00:00', 4, 'Salle Jacques DUCLOS, 62490 Vitry en Artois', '21-13 21-9', 'victoire', 'simple', 0, NULL),
(190, 2, NULL, NULL, 'Elena OLEJKO', 'LBCL 5', NULL, '2025-02-23 09:00:00', 4, 'Salle Jacques DUCLOS, 62490 Vitry en Artois', '21-10 21-10', 'victoire', 'simple', 0, NULL),
(191, 4, NULL, NULL, 'Adrien CACHERA & Lucas HENNIQUAUT', 'LBCL 5', 'Vincent DELEFOSSE', '2025-02-23 09:00:00', 4, 'Salle Jacques DUCLOS, 62490 Vitry en Artois', '8-21 14-21', 'défaite', 'double', 0, NULL),
(192, 1, NULL, NULL, 'Jérémy CORNET & Suzy VAN BRUSSEL', 'LBCL 5', 'Marie DOHEN', '2025-02-23 09:00:00', 4, 'Salle Jacques DUCLOS, 62490 Vitry en Artois', '21-16 21-14', 'victoire', 'mixte', 0, NULL),
(199, 5, 23, NULL, 'Thomas LANOY', 'ABS 6', NULL, '2025-02-23 14:00:00', 4, 'Salle Jacques DUCLOS, 62490 Vitry en Artois', '9-21 22-20 9-21', 'victoire', 'simple', 0, NULL),
(200, 4, 23, NULL, 'François HEDOUX', 'ABS 6', NULL, '2025-02-23 14:00:00', 4, 'Salle Jacques DUCLOS, 62490 Vitry en Artois', '21-17 17-21 11-21', 'victoire', 'simple', 0, NULL),
(201, 1, 23, NULL, 'Mathéo LANOY', 'ABS 6', NULL, '2025-02-23 14:00:00', 4, 'Salle Jacques DUCLOS, 62490 Vitry en Artois', '14-21 21-19 19-21', 'victoire', 'simple', 0, NULL),
(202, 2, 23, NULL, NULL, 'ABS 6', NULL, '2025-02-23 14:00:00', 4, 'Salle Jacques DUCLOS, 62490 Vitry en Artois', 'WO (0-21 0-21)', 'victoire', 'simple', 0, NULL),
(203, 1, 23, NULL, 'Thomas LANOY & Mathéo LANOY', 'ABS 6', 'Antoine HOMBERT', '2025-02-23 14:00:00', 4, 'Salle Jacques DUCLOS, 62490 Vitry en Artois', '5-21 21-17 16-21', 'victoire', 'double', 0, NULL),
(204, 5, 23, NULL, NULL, 'ABS 6', 'Marie DOHEN', '2025-02-23 14:00:00', 4, 'Salle Jacques DUCLOS, 62490 Vitry en Artois', 'WO (0-21 0-21)', 'victoire', 'mixte', 0, 'XD'),
(205, 6, NULL, NULL, 'Rémy CHAMPMARTIN', 'LE VOLANT AIROIS 3', NULL, '2025-02-23 11:30:00', 4, 'Salle Jacques DUCLOS, 62490 Vitry en Artois', '11-21 14-21', 'victoire', 'simple', 0, NULL),
(206, 4, NULL, NULL, 'Valentin DURIEZ', 'LE VOLANT AIROIS 3', NULL, '2025-02-23 11:30:00', 4, 'Salle Jacques DUCLOS, 62490 Vitry en Artois', '15-21 11-21', 'victoire', 'simple', 0, NULL),
(207, 1, NULL, NULL, 'Frédéric DENISSELLE', 'LE VOLANT AIROIS 3', NULL, '2025-02-23 11:30:00', 4, 'Salle Jacques DUCLOS, 62490 Vitry en Artois', '11-21 16-21', 'victoire', 'simple', 0, NULL),
(208, 3, NULL, NULL, 'Laly GEUJON', 'LE VOLANT AIROIS 3', NULL, '2025-02-23 11:30:00', 4, 'Salle Jacques DUCLOS, 62490 Vitry en Artois', '7-21 12-21', 'victoire', 'simple', 0, NULL),
(209, 4, NULL, NULL, 'Frédéric DENISSELLE & Valentin DURIEZ', 'LE VOLANT AIROIS 3', 'Antoine HOMBERT', '2025-02-23 11:30:00', 4, 'Salle Jacques DUCLOS, 62490 Vitry en Artois', '15-21 15-21', 'victoire', 'double', 0, NULL),
(210, 5, NULL, NULL, 'Rémy CHAMPMARTIN & Kelly TETART', 'LE VOLANT AIROIS 3', 'Constance CORNET', '2025-02-23 11:30:00', 4, 'Salle Jacques DUCLOS, 62490 Vitry en Artois', '21-13 21-15', 'victoire', 'mixte', 0, NULL),
(230, 6, NULL, NULL, 'Michel BERTHE', 'Ent. Sport. Badminton Courrières 4', NULL, '2025-03-23 09:00:00', 5, 'François Mitterrand, 62000 Arras', '20-22 23-21 23-21', 'victoire', 'simple', 0, NULL),
(231, 4, NULL, NULL, 'Quentin WAHART', 'Ent. Sport. Badminton Courrières 4', NULL, '2025-03-23 09:00:00', 5, 'François Mitterrand, 62000 Arras', '21-8 21-17', 'victoire', 'simple', 0, NULL),
(232, 1, NULL, NULL, 'Regis TOUZART', 'Ent. Sport. Badminton Courrières 4', NULL, '2025-03-23 09:00:00', 5, 'François Mitterrand, 62000 Arras', '21-9 21-12', 'victoire', 'simple', 0, NULL),
(233, 2, NULL, NULL, 'Alexandrine CHAVATTE', 'Ent. Sport. Badminton Courrières 4', NULL, '2025-03-23 09:00:00', 5, 'François Mitterrand, 62000 Arras', '21-17 21-15', 'défaite', 'simple', 0, NULL),
(234, 1, NULL, NULL, 'Michel BERTHE & Quentin WAHART', 'Ent. Sport. Badminton Courrières 4', 'Benoît TERGEMINA', '2025-03-23 09:00:00', 5, 'François Mitterrand, 62000 Arras', '17-21 21-7 12-21', 'défaite', 'double', 0, NULL),
(235, 4, NULL, NULL, 'Regis TOUZART & Romane DECKE', 'Ent. Sport. Badminton Courrières 4', 'Marie DOHEN', '2025-03-23 09:00:00', 5, 'François Mitterrand, 62000 Arras', '24-22 21-18', 'victoire', 'mixte', 0, NULL),
(236, 6, NULL, NULL, 'Clément LEMAIRE', 'Longuenesse Badminton Club 4', NULL, '2025-03-23 11:30:00', 5, 'François Mitterrand, 62000 Arras', '15-21 16-21', 'défaite', 'simple', 0, NULL),
(237, 4, NULL, NULL, 'Lucas GAYET', 'Longuenesse Badminton Club 4', NULL, '2025-03-23 11:30:00', 5, 'François Mitterrand, 62000 Arras', '21-6 21-14', 'victoire', 'simple', 0, NULL),
(238, 1, NULL, NULL, 'Thomas MANTEL', 'Longuenesse Badminton Club 4', NULL, '2025-03-23 11:30:00', 5, 'François Mitterrand, 62000 Arras', '18-21 12-21', 'défaite', 'simple', 0, NULL),
(239, 3, NULL, NULL, 'Marie MATTE', 'Longuenesse Badminton Club 4', NULL, '2025-03-23 11:30:00', 5, 'François Mitterrand, 62000 Arras', '21-8 21-11', 'victoire', 'simple', 0, NULL),
(240, 1, NULL, NULL, 'Lucas GAYET & Clément LEMAIRE', 'Longuenesse Badminton Club 4', 'Benoît TERGEMINA', '2025-03-23 11:30:00', 5, 'François Mitterrand, 62000 Arras', '17-21  21-18 15-21', 'défaite', 'double', 0, NULL),
(241, 4, NULL, NULL, 'Maxime BRIOUL & Anais MARQUETTE', 'Longuenesse Badminton Club 4', 'Constance CORNET', '2025-03-23 11:30:00', 5, 'François Mitterrand, 62000 Arras', '21-15  10-21 10-21', 'défaite', 'mixte', 0, NULL),
(242, 6, NULL, NULL, 'Sébastien LEBAS', 'C B Montreuil 3', NULL, '2025-03-23 14:00:00', 5, 'François Mitterrand, 62000 Arras', '21-17 21-9', 'victoire', 'simple', 0, NULL),
(243, 4, NULL, NULL, 'Aurélien HANNEBIQUE', 'C B Montreuil 3', NULL, '2025-03-23 14:00:00', 5, 'François Mitterrand, 62000 Arras', '21-19 17-21 21-13', 'victoire', 'simple', 0, NULL),
(244, 7, NULL, NULL, 'Jérémy PATIN', 'C B Montreuil 3', NULL, '2025-03-23 14:00:00', 5, 'François Mitterrand, 62000 Arras', '15-21 17-21', 'défaite', 'simple', 0, NULL),
(245, 2, NULL, NULL, 'Océane LOTTE', 'C B Montreuil 3', NULL, '2025-03-23 14:00:00', 5, 'François Mitterrand, 62000 Arras', '21-8 21-6', 'victoire', 'simple', 0, NULL),
(246, 6, NULL, NULL, 'Aurélien HANNEBIQUE & Jérémy PATIN', 'C B Montreuil 3', 'Benoît TERGEMINA', '2025-03-23 14:00:00', 5, 'François Mitterrand, 62000 Arras', '17-21 13-21', 'défaite', 'double', 0, NULL),
(247, 1, NULL, NULL, 'Sébastien LEBAS & Sarah LECOEUCHE', 'C B Montreuil 3', 'Marie DOHEN', '2025-03-23 14:00:00', 5, 'François Mitterrand, 62000 Arras', '21-14 17-21 21-16', 'victoire', 'mixte', 0, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `photos`
--

CREATE TABLE `photos` (
  `id` int(11) NOT NULL,
  `rencontre_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `legende` varchar(255) DEFAULT NULL,
  `position` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `rencontres`
--

CREATE TABLE `rencontres` (
  `id` int(11) NOT NULL,
  `journee` tinyint(4) NOT NULL COMMENT '1 = J1, 2 = J2…',
  `date_rencontre` date NOT NULL,
  `heure` time NOT NULL,
  `lieu_id` int(11) NOT NULL,
  `domicile_id` int(11) NOT NULL,
  `exterieur_id` int(11) NOT NULL,
  `score_domicile` tinyint(4) NOT NULL,
  `score_exterieur` tinyint(4) NOT NULL,
  `resume` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Rencontres interclubs, groupées par journée';

--
-- Déchargement des données de la table `rencontres`
--

INSERT INTO `rencontres` (`id`, `journee`, `date_rencontre`, `heure`, `lieu_id`, `domicile_id`, `exterieur_id`, `score_domicile`, `score_exterieur`, `resume`) VALUES
(3, 1, '2025-11-17', '09:00:00', 1, 1, 2, 4, 2, NULL),
(4, 1, '2025-11-17', '09:00:00', 1, 3, 4, 4, 2, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `teams`
--

CREATE TABLE `teams` (
  `id` int(11) NOT NULL,
  `club_id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `short_name` varchar(40) NOT NULL,
  `season` varchar(9) NOT NULL,
  `level` varchar(80) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `teams`
--

INSERT INTO `teams` (`id`, `club_id`, `name`, `short_name`, `season`, `level`) VALUES
(1, 1, 'Badminton Club Arras 6', 'BCA 6', '2024-2025', ''),
(2, 2, 'Speed Bad Club 3', 'SBC 3', '2024-2025', ''),
(3, 3, 'La Souchezoise 3', 'SZ 3', '2024-2025', ''),
(4, 4, 'Association Sports et Détente de Beaurains 2', 'ASDB 2', '2024-2025', ''),
(5, 5, 'Vitry Badminton Club 2', 'VABC 2', '2024-2025', ''),
(6, 6, 'Calais Badminton Club 4', 'CBC 4', '2024-2025', ''),
(7, 6, 'Calais Badminton Club 5', 'CBC 5', '2024-2025', ''),
(8, 7, 'Audruicq Badminton Club 2', 'ABC 2', '2024-2025', ''),
(9, 8, 'Volant Opale Club 3', 'VOC 3', '2024-2025', ''),
(10, 9, 'Leforest Badminton Club 5', 'LBCL 5', '2024-2025', ''),
(11, 10, 'LE VOLANT AIROIS 3', 'LVA 3', '2024-2025', ''),
(12, 11, 'A.b.st Etienne Au Mont 6', 'ABS 6', '2024-2025', ''),
(13, 12, 'Ent. Sport. Badminton Courrières 4', 'ESBC 4', '2024-2025', ''),
(14, 13, 'Longuenesse Badminton Club 4', 'LBC 4', '2024-2025', ''),
(15, 14, 'C B Montreuil 3', 'CBM 3', '2024-2025', '');

-- --------------------------------------------------------

--
-- Structure de la table `types_match`
--

CREATE TABLE `types_match` (
  `id` int(11) NOT NULL,
  `nom` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `v_match_details_for_stats`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `v_match_details_for_stats` (
`id` int(11)
,`joueur_id` int(11)
,`type_match` varchar(6)
,`resultat` varchar(8)
,`journee` tinyint(4)
,`date_match` datetime
);

-- --------------------------------------------------------

--
-- Structure de la vue `v_match_details_for_stats`
--
DROP TABLE IF EXISTS `v_match_details_for_stats`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_match_details_for_stats`  AS SELECT `md`.`id` AS `id`, `md`.`joueur_id` AS `joueur_id`, `md`.`type_match` AS `type_match`, `md`.`resultat` AS `resultat`, `md`.`journee` AS `journee`, `md`.`date_match` AS `date_match` FROM `match_details` AS `md`union all select `md`.`id` AS `id`,`j2`.`id` AS `joueur_id`,`md`.`type_match` AS `type_match`,`md`.`resultat` AS `resultat`,`md`.`journee` AS `journee`,`md`.`date_match` AS `date_match` from (`match_details` `md` join `joueurs` `j2` on(concat(`j2`.`prenom`,' ',`j2`.`nom`) collate utf8mb4_general_ci = trim(`md`.`binome`) collate utf8mb4_general_ci)) where `md`.`binome` is not null and `md`.`binome` <> ''  ;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `adversaires`
--
ALTER TABLE `adversaires`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `classements`
--
ALTER TABLE `classements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `joueur_id` (`joueur_id`);

--
-- Index pour la table `classement_equipes`
--
ALTER TABLE `classement_equipes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_saison_poule_rang` (`saison`,`division`,`poule`,`rang`),
  ADD KEY `idx_saison_poule` (`saison`,`division`,`poule`);

--
-- Index pour la table `clubs`
--
ALTER TABLE `clubs`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `fixtures`
--
ALTER TABLE `fixtures`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_fixture_unique` (`season`,`matchday`,`home_team_id`,`away_team_id`,`date_time`),
  ADD KEY `idx_dt` (`date_time`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_home` (`home_team_id`),
  ADD KEY `idx_away` (`away_team_id`);

--
-- Index pour la table `interviews`
--
ALTER TABLE `interviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `joueur_id` (`joueur_id`);

--
-- Index pour la table `joueurs`
--
ALTER TABLE `joueurs`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `lieux`
--
ALTER TABLE `lieux`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `matchs`
--
ALTER TABLE `matchs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rencontre_id` (`rencontre_id`),
  ADD KEY `joueur1_id` (`joueur1_id`),
  ADD KEY `joueur2_id` (`joueur2_id`);

--
-- Index pour la table `match_details`
--
ALTER TABLE `match_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_md_date` (`date_match`),
  ADD KEY `idx_md_player_date` (`joueur_id`,`date_match`),
  ADD KEY `idx_md_fixture` (`fixture_id`),
  ADD KEY `idx_md_joueur` (`joueur_id`),
  ADD KEY `idx_matchdetails_discipline` (`discipline_code`);

--
-- Index pour la table `photos`
--
ALTER TABLE `photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rencontre_id` (`rencontre_id`);

--
-- Index pour la table `rencontres`
--
ALTER TABLE `rencontres`
  ADD PRIMARY KEY (`id`),
  ADD KEY `journee` (`journee`),
  ADD KEY `date_rencontre` (`date_rencontre`),
  ADD KEY `fk_rencontres_lieux` (`lieu_id`),
  ADD KEY `fk_rencontres_dom` (`domicile_id`),
  ADD KEY `fk_rencontres_ext` (`exterieur_id`);

--
-- Index pour la table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_teams_club` (`club_id`);

--
-- Index pour la table `types_match`
--
ALTER TABLE `types_match`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `adversaires`
--
ALTER TABLE `adversaires`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `classements`
--
ALTER TABLE `classements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `classement_equipes`
--
ALTER TABLE `classement_equipes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `clubs`
--
ALTER TABLE `clubs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT pour la table `fixtures`
--
ALTER TABLE `fixtures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT pour la table `interviews`
--
ALTER TABLE `interviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `joueurs`
--
ALTER TABLE `joueurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT pour la table `lieux`
--
ALTER TABLE `lieux`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `matchs`
--
ALTER TABLE `matchs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `match_details`
--
ALTER TABLE `match_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=248;

--
-- AUTO_INCREMENT pour la table `photos`
--
ALTER TABLE `photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `rencontres`
--
ALTER TABLE `rencontres`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `types_match`
--
ALTER TABLE `types_match`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `classements`
--
ALTER TABLE `classements`
  ADD CONSTRAINT `classements_ibfk_1` FOREIGN KEY (`joueur_id`) REFERENCES `joueurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `fixtures`
--
ALTER TABLE `fixtures`
  ADD CONSTRAINT `fk_fix_away` FOREIGN KEY (`away_team_id`) REFERENCES `teams` (`id`),
  ADD CONSTRAINT `fk_fix_home` FOREIGN KEY (`home_team_id`) REFERENCES `teams` (`id`);

--
-- Contraintes pour la table `interviews`
--
ALTER TABLE `interviews`
  ADD CONSTRAINT `interviews_ibfk_1` FOREIGN KEY (`joueur_id`) REFERENCES `joueurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `matchs`
--
ALTER TABLE `matchs`
  ADD CONSTRAINT `matchs_ibfk_1` FOREIGN KEY (`rencontre_id`) REFERENCES `rencontres` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `matchs_ibfk_2` FOREIGN KEY (`joueur1_id`) REFERENCES `joueurs` (`id`),
  ADD CONSTRAINT `matchs_ibfk_3` FOREIGN KEY (`joueur2_id`) REFERENCES `joueurs` (`id`);

--
-- Contraintes pour la table `photos`
--
ALTER TABLE `photos`
  ADD CONSTRAINT `photos_ibfk_1` FOREIGN KEY (`rencontre_id`) REFERENCES `rencontres` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `teams`
--
ALTER TABLE `teams`
  ADD CONSTRAINT `fk_teams_club` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
