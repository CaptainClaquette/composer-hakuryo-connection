# Hakuryo Libs

## ConnectionDB

```PHP

require_once './vendor/autoload.php';

use hakuryo\db\ConnectionDB;

//Connect from a file
$db = ConnectionDB::from_file(__DIR__ . "/config/db.ini");
foreach ($db->search("SELECT * FROM event WHERE id = :id",["id"=> 17]) as $entry) {
    echo json_encode($entry,JSON_PRETTY_PRINT);
}
// close the connection
$db = null;

```
### Exemple INI file
```INI
[SQL]
HOST = "localhost"
DB = mydb
USER = "root"
PWD = "mypass"
PORT = 1234
```
## ConnectionOCI

```PHP

use hakuryo\db\ConnectionOCI;

$oci = new ConnectionOCI(__DIR__ . "/config/oci.ini");
foreach ($db->search("SELECT * FROM event") as $entry) {
    echo json_encode($entry,JSON_PRETTY_PRINT);
}

echo json_encode($db->get("SELECT * FROM event WHERE id = :id",["id"=> 17]),JSON_PRETTY_PRINT);

// close the connection
$db = null;
```

### Exemple INI file
```INI
[SQL]
HOST = "localhost"
DB = mydb
USER = "root"
PWD = "mypass"
PORT = 1234
```

## ConnectionLDAP

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