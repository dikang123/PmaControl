-- phpMyAdmin SQL Dump
-- version 4.5.4.1
-- http://www.phpmyadmin.net
--
-- Client :  localhost
-- Généré le :  Ven 18 Mars 2016 à 11:40
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
-- Structure de la table `daemon_main`
--

CREATE TABLE `daemon_main` (
  `id` int(11) NOT NULL,
  `name` char(10) NOT NULL,
  `date` datetime NOT NULL,
  `pid` int(11) NOT NULL,
  `id_user_main` int(11) NOT NULL,
  `log_file` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Contenu de la table `daemon_main`
--

INSERT INTO `daemon_main` (`id`, `name`, `date`, `pid`, `id_user_main`, `log_file`) VALUES
(1, 'main', '2016-02-24 00:00:00', 0, 0, '');

--
-- Index pour les tables exportées
--

--
-- Index pour la table `daemon_main`
--
ALTER TABLE `daemon_main`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- AUTO_INCREMENT pour les tables exportées
--

--
-- AUTO_INCREMENT pour la table `daemon_main`
--
ALTER TABLE `daemon_main`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

