<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace hakuryo\db;

/**
 * Description of ConnectionOCI
 *
 * @author Hakuryo
 */
class ConnectionOCI {
    
    public function __construct($config_path) {
        $conf = parse_ini_file($path);
        $host = $conf["HOST"];
        $db = $conf["DB"];
        $user = $conf["USER"];
        $pwd = $conf["PWD"];
        $port = $conf["PORT"];
        $conn = oci_connect($user, $pwd, "$host/$db");
    }
}
