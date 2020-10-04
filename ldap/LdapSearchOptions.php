<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace hakuryo\ldap;

/**
 * Description of LdapSearchOptions
 *
 * @author Hakuryo
 */
class LdapSearchOptions {

    public $base_dn = "";
    public $result_limit = 0;
    public $sort_by_attr = null;

    public function __construct() {
        
    }

    public function set_base_dn(string $dn) {
        $this->base_dn = $dn;
    }

}
