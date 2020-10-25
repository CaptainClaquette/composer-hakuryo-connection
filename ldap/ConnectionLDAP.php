<?php

namespace hakuryo\ldap;
use Exception;
/**
 * Description of ConnectionLDAP
 *
 * @author Hakuryo
 */
class ConnectionLDAP {

    use LdapUtils;

    const MOD_ADD = 0;
    const MOD_REPLACE = 1;
    const MOD_DEL = 2;

    public $connection;
    private $search_options;

    public function __construct(string $host, string $login, string $password, LdapSearchOptions $search_options = null) {
        if ($this->connection = ldap_connect("ldap://$host")) {
            ldap_set_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, 3);
            if (!ldap_bind($this->connection, $login, $password)) {
                throw new Exception("Can't bind to ldap server $host cause : " . $this->getLastError(), -1);
            }
            $this->search_options = $search_options === null ? new LdapSearchOptions(self::get_root_dn()) : $search_options;
        } else {
            throw new Exception("Can't connect to ldap server $host cause : " . $this->getLastError(), -1);
        }
    }

    public static function fromFile(string $path, string $section = null): ConnectionLDAP {
        $conf = $section === null ? parse_ini_file($path) : parse_ini_file($path, true)[$section];
        $host = $conf["HOST"];
        $user = $conf["USER"];
        $password = $conf["PWD"];
        $base_dn = $conf["DN"];
        $ldap = new ConnectionLDAP($host, $user, $password, new LdapSearchOptions($base_dn));
        return $ldap;
    }

    /**
     * Retourne le resultat de $filter avec les attribut specifie dans $returnedAttrs.
     * @param String $filter Filtre ldap
     * @param array $returnedAttrs [Optionnel] Tableau d'attribut a retourner. Defaut = ['*']
     * @see ldap_search
     * @return array Tableau associatif avec les noms d'attributs ldap en tant que clef.
     */
    public function search(string $filter, array $returnedAttrs = ['*']): array {
        $research = false;
        if ($this->search_options->get_scope() === LdapSearchOptions::SEARCH_SCOPE_SUB) {
            $research = @ldap_search($this->connection, $this->search_options->get_base_dn(), utf8_encode($filter), $returnedAttrs, 0, $this->search_options->get_result_limit());
        } else {
            $research = @ldap_list($this->connection, $this->search_options->get_base_dn(), utf8_encode($filter), $returnedAttrs, 0, $this->search_options->get_result_limit());
        }
        if (!$research) {
            throw new Exception("Can't perform research cause : " . $this->getLastError());
        }
        $entrys = @ldap_get_entries($this->connection, $research);
        return $entrys['count'] > 0 ? $this->clear_ldap_result($entrys) : [];
    }

    /**
     * Retourne l'entree corespondant a $filter avec les attributs specifie dans $returnedAttrs.
     * @param string $filter le filtre LDAP
     * @param array $returnedAttrs [Optionnel] Tableau d'attribut a retourner. Defaut = ['*']
     * @return stdclass retourne un stdClass vide si aucun resultat ne correspond a $filter
     */
    public function get_entry(string $filter, array $returnedAttrs = ['*']): \stdclass {
        $limit = $this->search_options->get_result_limit();
        $this->search_options->set_result_limit(1);
        $res = $this->search($filter, $returnedAttrs);
        $this->search_options->set_result_limit($limit);
        return count($res) > 0 ? $res[0] : new \stdClass();
    }

    public function modify(string $entry_dn, array $target_entry_attr, int $modify_type): bool {
        $result = false;
        switch ($modify_type) {
            case self::MOD_ADD:
                $result = ldap_mod_add($this->connection, $entry_dn, $target_entry_attr);
                break;
            case self::MOD_DEL:
                $result = ldap_mod_del($this->connection, $entry_dn, $target_entry_attr);
                break;
            case self::MOD_REPLACE:
                $result = ldap_mod_replace($this->connection, $entry_dn, $target_entry_attr);
                break;
        }
        if (!$result) {
            throw new Exception("Can't performe modification of $entry_dn cause : " . $this->getLastError());
        }
        return $result;
    }

    public function add(string $entry_dn, array $ldap_entry_attr): bool {
        if (!@ldap_add($this->connection, $entry_dn, $ldap_entry_attr)) {
            throw new Exception("Can't add ldap entry $entry_dn cause : " . $this->getLastError());
        }
        return true;
    }

    public function delete(string $entry_dn): bool {
        if (!@ldap_delete($this->connection, $entry_dn)) {
            throw new Exception("Can't delete ldap entry $entry_dn cause : " . $this->getLastError());
        }
        return true;
    }

    public function disconect() {
        ldap_close($this->connection);
    }

    // Getter & Setter
    public function get_search_options(): LdapSearchOptions {
        return $this->search_options;
    }

}
