<?php

require_once './vendor/autoload.php';

use hakuryo\db\ConnectionDB;

$db = ConnectionDB::from_file(__DIR__ . "/config/db.ini");
foreach ($db->search("SELECT * FROM event WHERE id = :id",["id"=> 17]) as $entry) {
    echo json_encode($entry,JSON_PRETTY_PRINT);
}

$db = null;

