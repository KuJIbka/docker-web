<?php
phpinfo();

ini_set('error_reporting', E_ALL);
ini_set("display_startup_errors", "1");
ini_set('display_errors', 1);
$dbConf = [
    'host' => 'mysql',
    'port' => '3306',
    'dbname' => 'test',
    'user' => 'root',
    'password' => 'secret',
    'charset' => 'utf8',
];

$db = new \PDO(
    'mysql:host=' . $dbConf['host'] . ';port='.$dbConf['port'] . ';dbname=' . $dbConf['dbname'],
    $dbConf['user'],
    $dbConf['password']
);
$db->exec('set names ' . $dbConf['charset']);
$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
$db->exec("
  CREATE TABLE `test_table` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`some_field` VARCHAR(50) NULL DEFAULT '' COLLATE 'utf8_unicode_ci',
	PRIMARY KEY (`id`)
  )
  COLLATE='utf8_unicode_ci'
  ENGINE=InnoDB
  ;"
);
$db->exec("
    INSERT INTO `test_table` (`id`, `some_field`)
    VALUES (1, 'someValue') 
    ON DUPLICATE KEY
        UPDATE `some_field` = 'someValue'
;");

$st = $db->query('SELECT * FROM `test_table`');
var_dump($st->fetchAll(\PDO::FETCH_ASSOC));

