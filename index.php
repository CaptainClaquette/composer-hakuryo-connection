<?php

require_once './vendor/autoload.php';

use hakuryo\db\ConnectionDB;
use hakuryo\ldap\ConnectionLDAP;
use hakuryo\ldap\LdapSearchOptions;

//Connect from a file
//$db = ConnectionDB::from_file(__DIR__ . "/config/db.ini");
//foreach ($db->search("SELECT * FROM event WHERE id = :id", ["id" => 17]) as $entry) {
//    echo json_encode($entry, JSON_PRETTY_PRINT);
//}
//// close the connection
//$db = null;
//$ldap = new ConnectionLDAP("serial-player.fr","cn=admin,dc=serial-player,dc=fr","WgRRaAhDv4CXUAUkhJyQ");
try {
    $ldap = ConnectionLDAP::fromFile(__DIR__ . "/config/ldap.ini", "ldap");
    $ldap->get_search_options()->set_scope(LdapSearchOptions::SEARCH_SCOPE_ONE_LEVEL);
//print_r($ldap->search("uid=kevin"));
    print_r($ldap->get_entry("uid=kevin"));
    echo "PASSWORD : " . $ldap->format_password("Jormuggand51") . PHP_EOL;
    $ldap->create_groupOfNames("test", "ou=groups,dc=serial-player,dc=fr", ["uid=kevin,ou=people,dc=serial-player,dc=fr"]);
    $ldap->disconect();
} catch (Exception $ex) {
    echo $ex->getMessage();
}

