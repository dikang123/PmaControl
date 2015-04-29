ALTER TABLE `cleaner_main` ADD `pid` INT NOT NULL DEFAULT '0' AFTER `prefix`;
UPDATE `pmacontrol-dev`.`menu` SET `url` = 'monitoring/query/' WHERE `menu`.`id` = 19;
UPDATE `pmacontrol-dev`.`menu` SET `icon` = '<i class="glyphicon glyphicon-trash" style="font-size:12px"></i>' WHERE `menu`.`id` = 51;
INSERT INTO `pmacontrol-dev`.`version` (`id`, `date`, `version`, `comment`) VALUES (NULL, '2015-04-27 17:00:00', '0.6', 'upgrade cleaner_main, to add pid in database');
