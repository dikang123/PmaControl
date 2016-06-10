CREATE TABLE IF NOT EXISTS `architecture` (
  `id` int(11) NOT NULL AUTO_INCREMENT, 
  `date` datetime NOT NULL, 
  `data` blob NOT NULL, 
  `display` blob NOT NULL, 
  PRIMARY KEY (`id`)
) ENGINE = InnoDB AUTO_INCREMENT = 567091 DEFAULT CHARSET = latin1;


CREATE TABLE IF NOT EXISTS `haproxy_main` (
  `id` int(11) NOT NULL AUTO_INCREMENT, 
  `hostname` varchar(100) NOT NULL, 
  `ip` char(15) NOT NULL, 
  `vip` char(15) NOT NULL, 
  `csv` varchar(250) NOT NULL, 
  `stats_login` varchar(50) NOT NULL, 
  `stats_password` varchar(50) NOT NULL, 
  `private_key` int(250) NOT NULL, 
  `path_conf` varchar(200) NOT NULL, 
  `date_refresh` datetime NOT NULL, 
  `config` text NOT NULL, 
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = latin1;


CREATE TABLE IF NOT EXISTS `haproxy_main_input` (
  `id` int(11) NOT NULL AUTO_INCREMENT, 
  `id_haproxy_main` int(11) NOT NULL, 
  `name` varchar(50) NOT NULL, 
  `mask` varchar(15) NOT NULL, 
  `port` int(11) NOT NULL, 
  `mode` varchar(30) NOT NULL, 
  PRIMARY KEY (`id`), 
  KEY `id_haproxy_main` (`id_haproxy_main`), 
  CONSTRAINT `haproxy_main_input_ibfk_1` FOREIGN KEY (`id_haproxy_main`) REFERENCES `haproxy_main` (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = latin1;


CREATE TABLE IF NOT EXISTS `haproxy_main_output` (
  `id` int(11) NOT NULL AUTO_INCREMENT, 
  `id_haproxy_input` int(11) NOT NULL, 
  `name` varchar(50) NOT NULL, 
  `ip` varchar(15) NOT NULL, 
  `port` int(11) NOT NULL, 
  PRIMARY KEY (`id`), 
  KEY `id_haproxy_input` (`id_haproxy_input`), 
  CONSTRAINT `haproxy_main_output_ibfk_1` FOREIGN KEY (`id_haproxy_input`) REFERENCES `haproxy_main_input` (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = latin1;

CREATE TABLE IF NOT EXISTS `ha_proxy_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT, 
  `id_haproxy_main` int(11) NOT NULL, 
  `id_haprox_input` int(11) NOT NULL, 
  `date` datetime NOT NULL, 
  `server_status` char(10) NOT NULL, 
  `server_role` int(11) NOT NULL, 
  `queue_cur` int(11) NOT NULL, 
  `queue_max` int(11) NOT NULL, 
  `queue_limit` int(11) NOT NULL, 
  `session_rate_cur` int(11) NOT NULL, 
  `session_rate_max` int(11) NOT NULL, 
  `session_rate_limit` int(11) NOT NULL, 
  `session_cur` int(11) NOT NULL, 
  `session_max` int(11) NOT NULL, 
  `session_limit` int(11) NOT NULL, 
  `bytes_in` int(11) NOT NULL, 
  `bytes_out` int(11) NOT NULL, 
  `enabled` int(11) NOT NULL, 
  PRIMARY KEY (`id`), 
  KEY `id_haproxy_main` (`id_haproxy_main`), 
  KEY `id_haprox_input` (`id_haprox_input`), 
  CONSTRAINT `ha_proxy_stats_ibfk_1` FOREIGN KEY (`id_haproxy_main`) REFERENCES `haproxy_main` (`id`), 
  CONSTRAINT `ha_proxy_stats_ibfk_2` FOREIGN KEY (`id_haprox_input`) REFERENCES `haproxy_main_input` (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = latin1;



CREATE TABLE IF NOT EXISTS `link__architecture__mysql_server` (
  `id` int(11) NOT NULL AUTO_INCREMENT, 
  `id_mysql_server` int(11) NOT NULL, 
  `id_architecture` int(11) NOT NULL, 
  PRIMARY KEY (`id`), 
  UNIQUE KEY `id_mysql_server` (
    `id_mysql_server`, `id_architecture`
  ), 
  KEY `id_architecture` (`id_architecture`), 
  CONSTRAINT `link__architecture__mysql_server_ibfk_1` FOREIGN KEY (`id_mysql_server`) REFERENCES `mysql_server` (`id`) ON DELETE CASCADE ON UPDATE CASCADE, 
  CONSTRAINT `link__architecture__mysql_server_ibfk_2` FOREIGN KEY (`id_architecture`) REFERENCES `architecture` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = latin1;


	

CREATE TABLE IF NOT EXISTS `link__haproxy_main_output__mysql_server` (
  `id` int(11) NOT NULL AUTO_INCREMENT, 
  `id_haproxy_output` int(11) NOT NULL, 
  `id_mysql_server` int(11) NOT NULL, 
  PRIMARY KEY (`id`), 
  KEY `id_haproxy_output` (`id_haproxy_output`), 
  KEY `id_mysql_server` (`id_mysql_server`), 
  CONSTRAINT `link__haproxy_main_output__mysql_server_ibfk_1` FOREIGN KEY (`id_haproxy_output`) REFERENCES `haproxy_main_output` (`id`), 
  CONSTRAINT `link__haproxy_main_output__mysql_server_ibfk_2` FOREIGN KEY (`id_mysql_server`) REFERENCES `mysql_server` (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = latin1;


ALTER TABLE 
  `mysql_replication_stats` 
ADD 
  `ssh_ok` int(11) NOT NULL; 
ALTER TABLE 
  `mysql_replication_stats` MODIFY `time_zone` varchar(50) NOT NULL;


ALTER TABLE 
  `mysql_status_max_date` MODIFY CONSTRAINT `mysql_status_max_date_ibfk_2` FOREIGN KEY (`id_mysql_server`) REFERENCES `mysql_server` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE 
  `mysql_status_value_int` 
DROP 
  INDEX `date`, 
ADD 
  INDEX `date` (
    `date`, `id_mysql_server`, `id_mysql_status_name`
  ); 
ALTER TABLE 
  `mysql_status_value_int` 
ADD 
  INDEX `id_mysql_server_4` (
    `id_mysql_server`, `id_mysql_status_name`
  ); 
ALTER TABLE 
  `mysql_status_value_int` 
DROP 
  INDEX `id_mysql_server`; 
ALTER TABLE 
  `mysql_status_value_int` 
ADD 
  UNIQUE `id_mysql_server` (
    `id_mysql_server`, `id_mysql_status_name`, 
    `date`
  );

CREATE TABLE IF NOT EXISTS `tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT, 
  `name` varchar(50) NOT NULL, 
  `color` char(6) NOT NULL, 
  `background` char(6) NOT NULL, 
  PRIMARY KEY (`id`), 
  UNIQUE KEY `name` (`name`)
) ENGINE = InnoDB DEFAULT CHARSET = latin1;




-- phpMyAdmin SQL Dump
-- version 4.5.4.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jun 01, 2016 at 04:19 PM
-- Server version: 10.1.14-MariaDB-1~xenial
-- PHP Version: 7.0.4-7ubuntu2.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pmacontrol`
--

-- --------------------------------------------------------

--
-- Table structure for table `menu`
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
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`id`, `parent_id`, `bg`, `bd`, `active`, `icon`, `title`, `url`, `class`, `position`, `group_id`) VALUES
(1, 0, 0, 1, 1, '<span class="glyphicon glyphicon glyphicon-home" style="font-size:12px"></span>', 'Dashboard', '{LINK}server/listing/main', '', 1, 1),
(2, 0, 2, 3, 0, '<span class="glyphicon glyphicon-th" style="font-size:12px"></span>', 'Alarms', '{LINK}server/listing/', '', 2, 1),
(3, 2, 4, 5, 1, '<i class="fa fa-object-group" style="font-size:14px"></i>', 'Architecture', '{LINK}architecture/index/', '', 1, 1),
(7, 11, 10, 19, 1, '<span class="glyphicon glyphicon-floppy-disk" style="font-size:12px"></span>', 'Backups', '{LINK}backup/listing/', '', 5, 1),
(18, 6, 21, 22, 1, '<span class="glyphicon glyphicon-user" style="font-size:12px"></span>', 'Users', '{LINK}user/index/', '', 2, 1),
(19, 5, 6, 7, 1, '<span class="glyphicon glyphicon-list-alt" style="font-size:12px"></span>', 'Queries analyzer', '{LINK}monitoring/query/', '', 3, 1),
(30, 25, 11, 12, 1, '<span class="glyphicon glyphicon-th" style="font-size:12px"></span>', 'Backup\'s list', '{LINK}backup/dump/', '', 2, 1),
(42, 0, 15, 16, 1, '<span class="glyphicon glyphicon-hdd" style="font-size:12px"></span>', 'Storage area', '{LINK}backup/storageArea/', '', 0, 1),
(43, 0, 17, 18, 1, '<span class="glyphicon glyphicon-cog" style="font-size:12px"></span>', 'Schedules', '{LINK}backup/settings', '', 0, 1),
(44, 0, 20, 37, 1, '<span class="glyphicon glyphicon-cog" style="font-size:12px"></span>', 'Settings', '', '', 0, 1),
(45, 0, 29, 30, 1, '<i class="fa fa-share-alt" style="font-size:16px" aria-hidden="true"></i>\n', 'HAproxy', '{LINK}ToolsBox/memory', '', 0, 1),
(46, 0, 35, 36, 1, '<i class="fa fa-server" aria-hidden="true" style="font-size:14px"></i>', 'Servers', '{LINK}Server/settings', '', 0, 1),
(47, 0, 38, 47, 1, '<i class="fa fa-language" style="font-size:14px"></i>', 'Language', '', '', 0, 1),
(48, 0, 39, 40, 1, '<img class="country" src="[IMG]country/type1/fr.gif" width="18" height="12">', 'French', 'fr{PATH}', '', 2, 1),
(49, 0, 41, 42, 1, '<img class="country" src="[IMG]country/type1/uk.gif" width="18" height="12">', 'English', 'en{PATH}', '', 1, 1),
(50, 0, 48, 57, 1, '<i style="font-size: 16px" class="fa fa-puzzle-piece"></i>', 'Plugins', '', '', 0, 1),
(51, 0, 49, 50, 1, '<i class="glyphicon glyphicon-trash" style="font-size:12px"></i>', 'Cleaner', '{LINK}cleaner/index/', '', 0, 1),
(60, 0, 1, 2, 1, '<span class="glyphicon glyphicon-off"></span>', 'Login', '{LINK}user/connection/', '', 0, 3),
(61, 0, 3, 4, 1, '<span class="glyphicon glyphicon-user"></span>', 'Register', '{LINK}user/register/', '', 0, 3),
(62, 0, 5, 6, 1, '<span class="glyphicon glyphicon-envelope"></span>', 'Lost password', '{LINK}user/lost_password/', '', 0, 3),
(63, 25, 13, 14, 1, '<span class="glyphicon glyphicon-film" style="font-size:12px"></span>', 'Daemons / Crontab', '{LINK}backup/gant/', '', 3, 1),
(64, 0, 51, 52, 1, '<i class="glyphicon glyphicon-transfer" style="font-size:12px"></i>', 'Compare', '{LINK}compare/index/', '', 0, 1),
(65, 0, 73, 74, 1, '<span class="glyphicon glyphicon-off"></span>', 'Logout', '{LINK}user/logout/', '', 0, 1),
(66, 0, 45, 46, 1, '<img class="country" src="[IMG]country/type1/ru.gif" width="18" height="12">', 'Russian', 'ru{PATH}', '', 1, 1),
(67, 0, 53, 54, 1, '<span class="glyphicon glyphicon-search" aria-hidden="true"></span>', 'Scan network', '{LINK}scan/index/', '', 0, 1),
(68, 0, 62, 72, 1, '<i class="fa fa-question" style="font-size:16px" aria-hidden="true"></i>\n\n\n', 'Help', 'https://github.com/Glial/PmaControl/issues', '', 0, 1),
(69, 0, 55, 56, 1, '<img src="[IMG]main/spider-icon32.png" height="16" width="16px">', 'Spider', '{LINK}spider/index/', '', 0, 1),
(70, 0, 64, 65, 1, '<i class="fa fa-book" style="font-size:16px"></i>\n\n', 'Online docs and support', 'https://github.com/Glial/PmaControl/issues', '', 0, 1),
(71, 0, 66, 67, 1, '<i class="fa fa-refresh" style="font-size:16px"></i>\r\n\r\n', 'Check for update', '{LINK}update/index', '', 0, 1),
(72, 0, 70, 71, 1, '<i class="fa fa-info-circle" style="font-size:16px"></i>\n\n', 'About', '{LINK}About/index', '', 0, 1),
(73, 0, 68, 69, 1, '<i class="fa fa-bug" style="font-size:16px"></i>\r\n\r\n', 'Report issue', 'https://github.com/Glial/PmaControl/issues', '', 0, 1),
(74, 6, 23, 24, 1, '<span class="glyphicon glyphicon-user" style="font-size:12px"></span>', 'Groups', '{LINK}user/index/', '', 2, 1),
(75, 6, 25, 26, 1, '<span class="glyphicon glyphicon-user" style="font-size:12px"></span>', 'Clients', '{LINK}user/index/', '', 2, 1),
(76, 6, 25, 26, 1, '<span class="glyphicon glyphicon-user" style="font-size:12px"></span>', 'Environment', '{LINK}user/index/', '', 2, 1),
(77, 0, 31, 32, 1, '<i class="fa fa-share-alt" style="font-size:16px" aria-hidden="true"></i>\r\n', 'MaxScale', '{LINK}ToolsBox/memory', '', 0, 1),
(78, 0, 33, 34, 1, '<span class="glyphicon glyphicon-calendar" style="font-size:12px"></span>', 'Daemon', '{LINK}ToolsBox/indexUsage', '', 0, 1),
(79, 0, 43, 44, 1, '<img class="country" src="[IMG]country/type1/jp.gif" width="18" height="12">', 'Japeneese', 'ja{PATH}', '', 1, 1),
(80, 0, 57, 58, 1, '<span class="glyphicon glyphicon-th-list" style="font-size:12px" aria-hidden="true"></span>', 'MySQL-sys', '{LINK}mysqlsys/index/', '', 0, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
