CREATE TABLE `mysql_galera` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `date` datetime NOT NULL,
 `name` varchar(500) NOT NULL,
 `arbiter` int(11) NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE `mysql_node` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `id_mysql_galera` int(11) NOT NULL,
 `id_mysql_server` int(11) NOT NULL,
 `size` int(11) NOT NULL,
 `ready` int(11) NOT NULL,
 `status` int(11) NOT NULL,
 `nodes` varchar(200) NOT NULL,
 PRIMARY KEY (`id`),
 KEY `id_mysql_galera` (`id_mysql_galera`),
 KEY `id_mysql_server` (`id_mysql_server`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



