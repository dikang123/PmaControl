ALTER TABLE `mysql_replication_stats` ADD `is_available` INT NOT NULL AFTER `date`;

ALTER TABLE `mysql_database` CHANGE `size` `data_length` BIGINT(20) NOT NULL;
ALTER TABLE `mysql_database` ADD `data_free` BIGINT NOT NULL AFTER `data_length`;
ALTER TABLE `mysql_database` ADD `index_length` BIGINT NOT NULL AFTER `data_free`;


DROP TABLE `menu`;


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
(7, 11, 10, 19, 0, '<span class="glyphicon glyphicon-floppy-disk" style="font-size:12px"></span>', 'Backups', '{LINK}backup/listing/', '', 5, 1),
(18, 6, 8, 9, 0, '<span class="glyphicon glyphicon-user" style="font-size:12px"></span>', 'Members', '{LINK}user/index/', '', 2, 1),
(19, 5, 6, 7, 1, '<span class="glyphicon glyphicon-list-alt" style="font-size:12px"></span>', 'Queries analyzer', '{LINK}monitoring/query/', '', 3, 1),
(30, 25, 11, 12, 0, '<span class="glyphicon glyphicon-th" style="font-size:12px"></span>', 'Backup''s list', '{LINK}backup/dump/', '', 2, 1),
(42, 0, 15, 16, 0, '<span class="glyphicon glyphicon-hdd" style="font-size:12px"></span>', 'Storage area', '{LINK}backup/storageArea/', '', 0, 1),
(43, 0, 17, 18, 0, '<span class="glyphicon glyphicon-cog" style="font-size:12px"></span>', 'Schedules', '{LINK}backup/settings', '', 0, 1),
(44, 0, 20, 25, 1, '<span class="glyphicon glyphicon-briefcase" style="font-size:12px"></span>', 'Tools box', '', '', 0, 1),
(45, 0, 21, 22, 1, '<span class="glyphicon glyphicon-briefcase" style="font-size:12px"></span>', 'Memory', '{LINK}ToolsBox/memory', '', 0, 1),
(46, 0, 23, 24, 1, '<span class="glyphicon glyphicon-briefcase" style="font-size:12px"></span>', 'Index usage', '{LINK}ToolsBox/indexUsage', '', 0, 1),
(47, 0, 26, 33, 1, '<i class="fa fa-language" style="font-size:14px"></i>', 'Language', '', '', 0, 1),
(48, 0, 27, 28, 1, '<img class="country" src="[IMG]country/type1/fr.gif" width="18" height="12">', 'French', 'fr{PATH}', '', 2, 1),
(49, 0, 29, 30, 1, '<img class="country" src="[IMG]country/type1/uk.gif" width="18" height="12">', 'English', 'en{PATH}', '', 1, 1),
(50, 0, 34, 39, 1, '<i style="font-size: 16px" class="fa fa-puzzle-piece"></i>', 'Plugins', '', '', 0, 1),
(51, 0, 35, 36, 1, '<i class="glyphicon glyphicon-trash" style="font-size:12px"></i>', 'Cleaner', '{LINK}cleaner/index/', '', 0, 1),
(60, 0, 1, 2, 1, '<span class="glyphicon glyphicon-off"></span>', 'Login', '{LINK}user/connection/', '', 0, 3),
(61, 0, 3, 4, 1, '<span class="glyphicon glyphicon-user"></span>', 'Register', '{LINK}user/register/', '', 0, 3),
(62, 0, 5, 6, 1, '<span class="glyphicon glyphicon-envelope"></span>', 'Lost password', '{LINK}user/lost_password/', '', 0, 3),
(63, 25, 13, 14, 0, '<span class="glyphicon glyphicon-film" style="font-size:12px"></span>', 'Glove pattern', '{LINK}backup/gant/', '', 3, 1),
(64, 0, 37, 38, 1, '<i class="glyphicon glyphicon-transfer" style="font-size:12px"></i>', 'Compare', '{LINK}compare/index/', '', 0, 1),
(65, 0, 40, 41, 1, '<span class="glyphicon glyphicon-off"></span>', 'Logout', '{LINK}user/logout/', '', 0, 1),
(66, 0, 31, 32, 1, '<img class="country" src="[IMG]country/type1/ru.gif" width="18" height="12">', 'Russian', 'ru{PATH}', '', 1, 1);

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
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;




update `menu` set bg=bg+1, bd=bd+1 where `group_id` =1 and bd > 38;


INSERT INTO `menu` (`id`, `parent_id`, `bg`, `bd`, `active`, `icon`, `title`, `url`, `class`, `position`, `group_id`) VALUES (NULL, '0', '39', '40', '1', '<i class="glyphicon glyphicon-transfer" style="font-size:12px"></i>', 'Scan network', '{LINK}scan/index/', '', '0', '1');

UPDATE `menu` SET `icon` = '<span class="glyphicon glyphicon-search" aria-hidden="true"></span>' WHERE `menu`.`id` = 67;


CREATE TABLE `mysql_status_name` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `name` varchar(64) NOT NULL,
 `type` int(11) NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


ALTER TABLE `mysql_server` ADD `error` TEXT NOT NULL AFTER `kernel`;


CREATE TABLE `mysql_status_value_double` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `id_mysql_server` int(11) NOT NULL,
 `id_mysql_status_name` int(11) NOT NULL,
 `date` datetime NOT NULL,
 `value` int(11) NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=TokuDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


CREATE TABLE `mysql_status_value_int` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `id_mysql_server` int(11) NOT NULL,
 `id_mysql_status_name` int(11) NOT NULL,
 `date` datetime NOT NULL,
 `value` int(11) NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=TokuDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

CREATE TABLE `mysql_status_value_text` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `id_mysql_server` int(11) NOT NULL,
 `id_mysql_status_name` int(11) NOT NULL,
 `date` datetime NOT NULL,
 `value` double NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=TokuDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
