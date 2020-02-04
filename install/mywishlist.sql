-- phpMyAdmin SQL Dump
-- version 4.7.3
-- https://www.phpmyadmin.net/
--
-- Hôte : tortevois.mysql.db
-- Généré le :  Dim 13 jan. 2019 à 13:54
-- Version du serveur :  5.5.60-0+deb7u1-log
-- Version de PHP :  5.6.38-0+deb8u1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `tortevois`
--

-- --------------------------------------------------------

--
-- Structure de la table `mwl_gifts`
--

CREATE TABLE `mwl_gifts` (
  `id_gift` int(11) NOT NULL,
  `title` varchar(64) COLLATE utf8_bin NOT NULL,
  `texte` text COLLATE utf8_bin NOT NULL,
  `add_time` timestamp NULL DEFAULT NULL,
  `upd_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Structure de la table `mwl_subscribers`
--

CREATE TABLE `mwl_subscribers` (
  `id_user` int(11) NOT NULL,
  `id_wishlist` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Structure de la table `mwl_users`
--

CREATE TABLE `mwl_users` (
  `id_user` int(11) NOT NULL,
  `username` varchar(32) COLLATE utf8_bin NOT NULL,
  `password` varchar(60) COLLATE utf8_bin NOT NULL,
  `email` varchar(32) COLLATE utf8_bin NOT NULL,
  `reg_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_activate` tinyint(1) DEFAULT '0',
  `actkey` varchar(32) COLLATE utf8_bin NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Structure de la table `mwl_wishlists`
--

CREATE TABLE `mwl_wishlists` (
  `id_wishlist` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `title` varchar(64) COLLATE utf8_bin NOT NULL,
  `is_shared` tinyint(1) DEFAULT NULL,
  `add_time` timestamp NULL DEFAULT NULL,
  `upd_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Structure de la table `mwl_wishlist_gifts`
--

CREATE TABLE `mwl_wishlist_gifts` (
  `id_wishlist` int(11) NOT NULL,
  `id_gift` int(11) NOT NULL,
  `id_reserver` int(11) DEFAULT NULL,
  `add_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `mwl_gifts`
--
ALTER TABLE `mwl_gifts`
  ADD PRIMARY KEY (`id_gift`);

--
-- Index pour la table `mwl_subscribers`
--
ALTER TABLE `mwl_subscribers`
  ADD UNIQUE KEY `subscribe` (`id_user`,`id_wishlist`) USING BTREE;

--
-- Index pour la table `mwl_users`
--
ALTER TABLE `mwl_users`
  ADD PRIMARY KEY (`id_user`);

--
-- Index pour la table `mwl_wishlists`
--
ALTER TABLE `mwl_wishlists`
  ADD PRIMARY KEY (`id_wishlist`);

--
-- Index pour la table `mwl_wishlist_gifts`
--
ALTER TABLE `mwl_wishlist_gifts`
  ADD UNIQUE KEY `id_wishlist` (`id_wishlist`,`id_gift`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `mwl_gifts`
--
ALTER TABLE `mwl_gifts`
  MODIFY `id_gift` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=200;
--
-- AUTO_INCREMENT pour la table `mwl_users`
--
ALTER TABLE `mwl_users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
--
-- AUTO_INCREMENT pour la table `mwl_wishlists`
--
ALTER TABLE `mwl_wishlists`
  MODIFY `id_wishlist` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=181;COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
