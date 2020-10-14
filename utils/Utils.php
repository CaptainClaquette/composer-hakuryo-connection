<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Utils
 *
 * @author dere01
 */
class Utils {

    public static function parse_ini($path, $section) {
        $confs = parse_ini_file($path);
        if (array_key_exists($section, $confs)) {
            $config = new stdClass();
        }
    }

}
