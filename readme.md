# Hakuryo Libs

## Install

> composer require hakuryo/connection:^2

## Dependencies

### Mandatory

- PHP >= 7.x 

### Optionnal

> Only If you want to use ConnectionDB to connect to an ORACLE database

- Oracle Instantclient
- PHP PDO_OCI

## Breaking change

### version 2.x.x

- Merge ConnectionOCI into ConnectionDB
- Add php pdo_oci optional dependency

## ConnectionDB

### Exemple INI file

```INI
[mysql]
HOST = "localhost"
DB = mydb
USER = "root"
PWD = "mypass"
PORT = 1234
DRIVER = "mysql" ;Accepted Values are oci,mysql

[oracle]
HOST = "localhost"
DB = mydb
USER = "root"
PWD = "mypass"
PORT = 1234
DRIVER = "oci" ;Accepted Values are oci,mysql

```

### ConnectionDB usage

```PHP

require "./vendor/autoload.php";

use hakuryo\db\ConnectionDB;
//Connection to mysql
$db = ConnectionDB::from_file('config.ini', 'mysql');
//Usage of anonnymous params
$rq = "SELECT * FROM users";
// search function is for multiple result
print_r($db->search($rq, [1234], false));
$db =null;

//Connection to oracle
$db = ConnectionDB::from_file('config.ini', 'oracle');
$rq = "SELECT firstname FROM users WHERE id = :id";
//Usage of named params
// get function return the first line of the result
$result $db->get($rq, ["id"=>1234]);

// Check if result is relevant
if(property_exist($result,'id')){
    print_r($result);
}
$db =null;

//Connection with a config.ini without section
$db = ConnectionDB::from_file('config_without_section.ini');
$rq = "INSERT INTO users (firstname,lastname) VALUES (:fname,:lname)";
//Modify is use to perform update, insert or delete operation
print_r($db->modify($rq, ["fname"=>"Bob","lname"=>"Moran"]));
$db =null;

```

## ConnectionLDAP


### Exemple INI file
```INI
[ldap]
HOST="ldap://myldap.mydomain.com"
;LDAPS
;HOST="ldaps://myldap.mydomain.com"
USER="cn=admin,dc=mydomain, dc=com"
DN="dc=mydomain, dc=com"
PWD="my_super_secure_password"
```

### ConnectionLDAP usage

```PHP
require_once './vendor/autoload.php';

use hakuryo\ldap\ConnectionLDAP;

// Basic connection 
$ldap = new ConnectionLDAP("myldap.mydomain.com","uid=user,ou=people,dc=mydomain,dc=com")

// From File
$ldap = ConnectionLDAP::fromFile("path_to_my_ldap_ini_file");

// You can specify a section of your ini file
$ldap = ConnectionLDAP::fromFile("path_to_my_ldap_ini_file","ldap_section");

//ldap_search
$ldap_filter = "memberof=cn=admin,ou=groups,dc=mydomain,dc=com";
$attr_list = ["uid","displayname","sn","givenname"];
$results = $ldap->search($ldap_filer,$attr_list);
foreach($result as $entry){
    echo json_encode($entry,JSON_PRETTY_PRINT);
}

// get an specifique entry
$ldap->get_entry($ldap_filer,$attr_list);

// Modify serach_options
$ldap->get_search_options()->set_base_dn("ou=my_ou,dc=exemple,dc=com");
$ldap->get_search_options()->set_result_limit(1);
$ldap->get_search_options()->set_scope(LdapSearchOptions::SEARCH_SCOPE_ONE_LEVEL);

// You can chain modification
$ldap->get_search_options()->set_result_limit(1)->set_scope(LdapSearchOptions::SEARCH_SCOPE_ONE_LEVEL);

```
