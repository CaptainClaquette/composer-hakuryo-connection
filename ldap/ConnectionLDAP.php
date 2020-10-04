<?php

namespace hakuryo\ldap;

/**
 * Description of ConnectionLDAP
 *
 * @author Hakuryo
 */
class ConnectionLDAP {

    public $connection;
    public $search_options;

    public function __construct($host, $login, $password) {
        $this->connection = ldap_connect("ldap://$host");
        ldap_bind($this->connection, $login, $password);
        ldap_set_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, 3);
        $this->search_options = new LdapSearchOptions();
        $this->search_options->base_dn = self::get_root_dn();
    }

    public static function fromFile($path): ConnectionLDAP {
        $conf = parse_ini_file($path);
        $host = $conf["HOST"];
        $user = $conf["USER"];
        $password = $conf["PWD"];
        $base_dn = $conf["DN"];
        $this->connection = ldap_connect($host);
        ldap_bind($this->connection, $user, $password);
        ldap_set_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, 3);
        $ldap = new ConnectionLDAP($host, $user, $password);
        $ldap->search_options->set_base_dn($base_dn);
        return $ldap;
    }

    /**
     * @param String $filter Filtre ldap
     * @param Array $returnedAttrs [Optionnel] Tableau d'attribut a retourner. Defaut = null
     * @param Interger $nbresult [Optionnel] Nombre de resultats maximum souhaite. Defaut = 0 (acunne limite).
     * Attention si le nombre de resultats souhaités est inferieur au nombre de resultats retourne. Un warning est lance (partial result)
     * @see ldap_search
     * @return Array Tableau associatif avec les noms d'attributs ldap en tant que clef.
     */
    public function search(string $filter, array $returnedAttrs = ['*']): Array {
        if ($this->check_ldap_con()) {
            $research = ldap_search($this->connection, $this->search_options->base_dn, utf8_encode($filter), $returnedAttrs, 0, $this->search_options->result_limit);
        }
        if ($this->search_options->sort_by_attr) {
            ldap_sort($this->connection, $research, $this->search_options->sort_by_attr);
        }
        $entrys = ldap_get_entries($this->connection, $research);
        return ConnectionLDAP::clear_ldap_result($entrys);
    }

    public static function clear_ldap_result($entrys) {
        $res = array();
        unset($entrys["count"]);
        foreach ($entrys as $line) {
            $temp = [];
            $temp['dn'] = $line["dn"];
            unset($line["count"]);
            unset($line["dn"]);
            foreach ($line as $key => $value) {
                if (is_string($key)) {
                    unset($value["count"]);
                    $temp[utf8_encode($key)] = count($value) > 1 ? $value : utf8_encode($value[0]);
                }
            }
            ksort($temp, SORT_NATURAL);
            array_push($res, (object) $temp);
        }
        return $res;
    }

    public function getLastError() {
        $msg = "";
        ldap_get_option($this->con, LDAP_OPT_DIAGNOSTIC_MESSAGE, $msg);
        return "[ERROR_CODE]" . ldap_errno($this->con) . " " . ldap_error($this->con) . " " . "$msg";
    }

    public function format_password($pass) {
        return "{SHA}" . base64_encode(pack("H*", sha1($pass)));
    }

    public function disconect() {
        ldap_close($this->connection);
    }

    public function check_ldap_con() {
        if (!$this->connection) {
            throw new Exception("LDAP Connection lost, try to check ConnectionLDAP con variable");
        }
        return true;
    }

    private static function get_root_dn() {
        $mydn = ldap_exop_whoami($this->connection);
        $matches = array();
        preg_match('/dc=.*/', $mydn, $matches);
        if (count($matches) > 0) {
            return $matches[0];
        } else {
            throw new Exception("Can't retrieve root_dn from provided user dn");
        }
    }

}
