ALTER TABLE `mysql_replication_stats` ADD `is_available` INT NOT NULL AFTER `date`;

ALTER TABLE `mysql_database` CHANGE `size` `data_length` BIGINT(20) NOT NULL;
ALTER TABLE `mysql_database` ADD `data_free` BIGINT NOT NULL AFTER `data_length`;
ALTER TABLE `mysql_database` ADD `index_length` BIGINT NOT NULL AFTER `data_free`;
