CREATE DATABASE `test` /*!40100 COLLATE 'utf8_general_ci' */;
CREATE TABLE `test_table` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`some_field` VARCHAR(50) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
;
