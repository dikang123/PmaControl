/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * Author:  aurelien
 * Created: 14 avr. 2016
 */
ALTER TABLE `daemon_main` ADD `max_delay` int(11) NOT NULL;



CREATE TABLE `mysql_status_max_date` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_mysql_server` int(11) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_mysql_server` (`id_mysql_server`),
  UNIQUE KEY `id_mysql_server_2` (`id_mysql_server`,`date`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

ALTER TABLE `mysql_status_max_date` ADD FOREIGN KEY (`id_mysql_server`) REFERENCES `mysql_server`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;


/* bad idea 
DELIMITER //

CREATE TRIGGER refresh_max_date
AFTER INSERT
   ON mysql_status_value_int FOR EACH ROW
BEGIN
    REPLACE INTO mysql_status_max_date  (`id_mysql_server`,`date`) VALUES (NEW.`id_mysql_server`, NEW.`date`);
END; //

DELIMITER ;
*/