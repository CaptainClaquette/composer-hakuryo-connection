<?php

namespace hakuryo\ldap;

/**
 * Description of ConnectionLDAP
 *
 * @author Hakuryo
 */
class ConnectionLDAP {

    public $connection;

    public function __construct($host, $login, $password) {
        $this->connection = ldap_connect("ldap://$host");
        ldap_bind($this->connection, $login, $password);
        ldap_set_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, 3);
    }
    
    

}
