ALTER TABLE `daemon_main` ADD `refresh_time` INT NOT NULL AFTER `log_file`;
ALTER TABLE `daemon_main` ADD `thread_concurency` INT NOT NULL AFTER `refresh_time`;
UPDATE `daemon_main` SET `refresh_time` = '3' WHERE `daemon_main`.`id` = 1;
UPDATE `daemon_main` SET `thread_concurency` = '8' WHERE `daemon_main`.`id` = 1;
