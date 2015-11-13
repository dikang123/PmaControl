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