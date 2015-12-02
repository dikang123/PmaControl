ALTER TABLE `mysql_replication_stats` ADD `is_available` INT NOT NULL AFTER `date`;

ALTER TABLE `mysql_database` CHANGE `size` `data_length` BIGINT(20) NOT NULL;
ALTER TABLE `mysql_database` ADD `data_free` BIGINT NOT NULL AFTER `data_length`;
ALTER TABLE `mysql_database` ADD `index_length` BIGINT NOT NULL AFTER `data_free`;


INSERT INTO  `pmacontrol`.`menu` (
`id` ,
`parent_id` ,
`bg` ,
`bd` ,
`icon` ,
`title` ,
`url` ,
`class` ,
`position` ,
`group_id`
)
VALUES (
NULL ,  '0',  '37',  '38',  '<i class="glyphicon glyphicon-transfer" style="font-size:12px"></i>',  'Compare',  'Compare/index/',  '',  '0',  '1'
);

UPDATE  `pmacontrol`.`menu` SET  `bd` =  '39' WHERE  `menu`.`id` =50;

DELETE FROM `menu`;

INSERT INTO `menu` VALUES (1,0,0,1,'<span class=\"glyphicon glyphicon glyphicon-home\" style=\"font-size:12px\"></span>','Home','home/index/','',1,1),(2,0,2,3,'<span class=\"glyphicon glyphicon-th\" style=\"font-size:12px\"></span>','Dashboard','server/listing/','',2,1),(3,2,4,5,'<span class=\"glyphicon glyphicon glyphicon-th-list\" style=\"font-size:12px\"></span>','Master / Slave','replication/status/','',1,1),(7,11,10,21,'<span class=\"glyphicon glyphicon-floppy-disk\" style=\"font-size:12px\"></span>','Backups','backup/listing/','',5,1),(18,6,8,9,'<span class=\"glyphicon glyphicon-user\" style=\"font-size:12px\"></span>','Members','user/index/','',2,1),(19,5,6,7,'<span class=\"glyphicon glyphicon-list-alt\" style=\"font-size:12px\"></span>','Queries analyzer','monitoring/query/','',3,1),(29,25,11,12,'<span class=\"glyphicon glyphicon-th\" style=\"font-size:12px\" style=\"font-size:12px\"></span>','Backup\'s list','backup/listing/','',1,1),(30,25,13,14,'<span class=\"glyphicon glyphicon-th\" style=\"font-size:12px\"></span>','Backup\'s list (new)','backup/dump/','',2,1),(42,0,17,18,'<span class=\"glyphicon glyphicon-hdd\" style=\"font-size:12px\"></span>','Storage area','backup/storageArea/','',0,1),(43,0,19,20,'<span class=\"glyphicon glyphicon-cog\" style=\"font-size:12px\"></span>','Schedules','backup/settings','',0,1),(44,0,22,27,'<span class=\"glyphicon glyphicon-briefcase\" style=\"font-size:12px\"></span>','Tools box','','',0,1),(45,0,23,24,'<span class=\"glyphicon glyphicon-briefcase\" style=\"font-size:12px\"></span>','Memory','ToolsBox/memory','',0,1),(46,0,25,26,'<span class=\"glyphicon glyphicon-briefcase\" style=\"font-size:12px\"></span>','Index usage','ToolsBox/indexUsage','',0,1),(47,0,28,33,'<i class=\"fa fa-language\" style=\"font-size:14px\"></i>','Language','','',0,1),(48,0,29,30,'<img class=\"country\" src=\"[IMG]country/type1/fr.gif\" width=\"18\" height=\"12\">','French','Reporting/detail_order/','',0,1),(49,0,31,32,'<img class=\"country\" src=\"[IMG]country/type1/uk.gif\" width=\"18\" height=\"12\">','English','Reporting/getIdProdItem/','',0,1),(50,0,34,39,'<i style=\"font-size: 16px\" class=\"fa fa-puzzle-piece\"></i>','Plugins','','',0,1),(51,0,35,36,'<i class=\"glyphicon glyphicon-trash\" style=\"font-size:12px\"></i>','Cleaner','Cleaner/index/','',0,1),(60,0,1,2,'<span class=\"glyphicon glyphicon-off\"></span>','Login','user/connection/','',0,3),(61,0,3,4,'<span class=\"glyphicon glyphicon-user\"></span>','Register','user/register/','',0,3),(62,0,5,6,'<span class=\"glyphicon glyphicon-envelope\"></span>','Lost password','user/lost_password/','',0,3),(63,25,15,16,'<span class=\"glyphicon glyphicon-film\" style=\"font-size:12px\"></span>','Glove pattern','backup/gant/','',3,1),(64,0,37,38,'<i class=\"glyphicon glyphicon-transfer\" style=\"font-size:12px\"></i>','Compare','Compare/index/','',0,1),(65,0,40,41,'<span class=\"glyphicon glyphicon-off\"></span>','Logout','user/logout/','',0,1);
