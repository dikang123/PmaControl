-- phpMyAdmin SQL Dump
-- version 4.5.4.1
-- http://www.phpmyadmin.net
--
-- Client :  localhost
-- Généré le :  Jeu 17 Mars 2016 à 11:27
-- Version du serveur :  10.1.12-MariaDB-1~jessie
-- Version de PHP :  5.6.17-0+deb8u1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `pmacontrol`
--

-- --------------------------------------------------------

--
-- Structure de la table `menu`
--

DROP TABLE IF EXISTS `menu`;
CREATE TABLE `menu` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `parent_id` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `bg` int(11) NOT NULL,
  `bd` int(11) NOT NULL,
  `active` int(11) NOT NULL,
  `icon` text NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `class` varchar(255) NOT NULL DEFAULT '',
  `position` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `group_id` tinyint(3) UNSIGNED NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Contenu de la table `menu`
--

INSERT INTO `menu` (`id`, `parent_id`, `bg`, `bd`, `active`, `icon`, `title`, `url`, `class`, `position`, `group_id`) VALUES
(1, 0, 0, 1, 1, '<span class="glyphicon glyphicon glyphicon-home" style="font-size:12px"></span>', 'Dashboard', '{LINK}server/listing/', '', 1, 1),
(2, 0, 2, 3, 0, '<span class="glyphicon glyphicon-th" style="font-size:12px"></span>', 'Alarms', '{LINK}server/listing/', '', 2, 1),
(3, 2, 4, 5, 0, '<span class="glyphicon glyphicon glyphicon-th-list" style="font-size:12px"></span>', 'Architecture', '{LINK}replication/status/', '', 1, 1),
(7, 11, 10, 19, 1, '<span class="glyphicon glyphicon-floppy-disk" style="font-size:12px"></span>', 'Backups', '{LINK}backup/listing/', '', 5, 1),
(18, 6, 8, 9, 1, '<span class="glyphicon glyphicon-user" style="font-size:12px"></span>', 'Members', '{LINK}user/index/', '', 2, 1),
(19, 5, 6, 7, 1, '<span class="glyphicon glyphicon-list-alt" style="font-size:12px"></span>', 'Queries analyzer', '{LINK}monitoring/query/', '', 3, 1),
(30, 25, 11, 12, 1, '<span class="glyphicon glyphicon-th" style="font-size:12px"></span>', 'Backup\'s list', '{LINK}backup/dump/', '', 2, 1),
(42, 0, 15, 16, 1, '<span class="glyphicon glyphicon-hdd" style="font-size:12px"></span>', 'Storage area', '{LINK}backup/storageArea/', '', 0, 1),
(43, 0, 17, 18, 1, '<span class="glyphicon glyphicon-cog" style="font-size:12px"></span>', 'Schedules', '{LINK}backup/settings', '', 0, 1),
(44, 0, 20, 25, 0, '<span class="glyphicon glyphicon-briefcase" style="font-size:12px"></span>', 'Tools box', '', '', 0, 1),
(45, 0, 21, 22, 0, '<span class="glyphicon glyphicon-briefcase" style="font-size:12px"></span>', 'Memory', '{LINK}ToolsBox/memory', '', 0, 1),
(46, 0, 23, 24, 0, '<span class="glyphicon glyphicon-briefcase" style="font-size:12px"></span>', 'Index usage', '{LINK}ToolsBox/indexUsage', '', 0, 1),
(47, 0, 26, 33, 1, '<i class="fa fa-language" style="font-size:14px"></i>', 'Language', '', '', 0, 1),
(48, 0, 27, 28, 1, '<img class="country" src="[IMG]country/type1/fr.gif" width="18" height="12">', 'French', 'fr{PATH}', '', 2, 1),
(49, 0, 29, 30, 1, '<img class="country" src="[IMG]country/type1/uk.gif" width="18" height="12">', 'English', 'en{PATH}', '', 1, 1),
(50, 0, 34, 43, 1, '<i style="font-size: 16px" class="fa fa-puzzle-piece"></i>', 'Plugins', '', '', 0, 1),
(51, 0, 35, 36, 1, '<i class="glyphicon glyphicon-trash" style="font-size:12px"></i>', 'Cleaner', '{LINK}cleaner/index/', '', 0, 1),
(60, 0, 1, 2, 1, '<span class="glyphicon glyphicon-off"></span>', 'Login', '{LINK}user/connection/', '', 0, 3),
(61, 0, 3, 4, 1, '<span class="glyphicon glyphicon-user"></span>', 'Register', '{LINK}user/register/', '', 0, 3),
(62, 0, 5, 6, 1, '<span class="glyphicon glyphicon-envelope"></span>', 'Lost password', '{LINK}user/lost_password/', '', 0, 3),
(63, 25, 13, 14, 0, '<span class="glyphicon glyphicon-film" style="font-size:12px"></span>', 'Glove pattern', '{LINK}backup/gant/', '', 3, 1),
(64, 0, 37, 38, 1, '<i class="glyphicon glyphicon-transfer" style="font-size:12px"></i>', 'Compare', '{LINK}compare/index/', '', 0, 1),
(65, 0, 46, 47, 1, '<span class="glyphicon glyphicon-off"></span>', 'Logout', '{LINK}user/logout/', '', 0, 1),
(66, 0, 31, 32, 1, '<img class="country" src="[IMG]country/type1/ru.gif" width="18" height="12">', 'Russian', 'ru{PATH}', '', 1, 1),
(67, 0, 39, 40, 1, '<span class="glyphicon glyphicon-search" aria-hidden="true"></span>', 'Scan network', '{LINK}scan/index/', '', 0, 1),
(68, 0, 44, 45, 1, '<i class="fa fa-bug" style="font-size:14px"></i>\r\n\r\n', 'Report a bug', 'https://github.com/Glial/PmaControl/issues', '', 0, 1),
(69, 0, 41, 42, 1, '<img src="[IMG]main/spider-icon32.png" height="16" width="16px">', 'Spider', '{LINK}spider/index/', '', 0, 1);

--
-- Index pour les tables exportées
--

--
-- Index pour la table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables exportées
--

--
-- AUTO_INCREMENT pour la table `menu`
--
ALTER TABLE `menu`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

