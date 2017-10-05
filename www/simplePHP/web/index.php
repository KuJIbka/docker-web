<?php
phpinfo();

ini_set('error_reporting', E_ALL);
ini_set("display_startup_errors", "1");
ini_set('display_errors', 1);
echo "Start check DB...<br />\n";
$dbConf = [
    'host' => 'mysql',
    'port' => '3306',
    'dbname' => 'simple_php',
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
  CREATE TABLE IF NOT EXISTS `test_table` (
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

$st = $db->query('SELECT some_field FROM `test_table`');
if ($st->fetchColumn(0) === 'someValue') {
    echo "DB - OK<br />\n";
} else {
    echo "DB - ERROR<br />\n";
}
$db->exec('DROP TABLE test_table');

# ------------------------------------------------------------------
echo "Start check Redis...<br />\n";
$redis = new \Redis();
$redis->connect(
    'redis',
    6379,
    null,
    null,
    0
);
$redis->set('someKey', 'someValue');
if ($redis->get('someKey') === 'someValue') {
    echo "Redis - OK<br />\n";
} else {
    echo "Redis - ERROR<br />\n";
}

# ------------------------------------------------------------------
echo "Start check Mongo...<br />\n";
$manager = new MongoDB\Driver\Manager('mongodb://root:secret@mongo:27017/');
$command = new MongoDB\Driver\Command([ 'create' => 'testCollation' ]);
$manager->executeCommand('test', $command);
$insert = new MongoDB\Driver\BulkWrite();
$insert->insert(['someKey' => 'someValue']);
$writeConcert = new MongoDB\Driver\WriteConcern(
    MongoDB\Driver\WriteConcern::MAJORITY,
    1000
);
$result = $manager->executeBulkWrite('test.testCollation', $insert, $writeConcert);
if ($result->getInsertedCount()) {
    echo "Mongo - OK<br />\n";
} else {
    echo "Mongo - ERROR<br />\n";
}
$command = new MongoDB\Driver\Command([ 'drop' => 'testCollation' ]);
