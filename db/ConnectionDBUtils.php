<?php

namespace hakuryo\db;

trait ConnectionDBUtils {

    private static function parse_ini($path, $section): \stdClass {
        $raw_conf = $section === null ? parse_ini_file($path) : parse_ini_file($path, true)[$section];
        $keys = ["HOST", "DB", "USER", "PWD", "PORT", "DRIVER"];
        $config = new \stdClass();
        foreach ($keys as $key) {
            if (array_key_exists($key, $raw_conf)) {
                if ($key === 'DRIVER' && !in_array(strtolower($raw_conf[$key]), ['oci', 'mysql'])) {
                    throw new \Exception("Wrong 'DRIVER' key value, acceptable values are 'oci','mysql'");
                }
            } else {
                throw new \Exception("You must provide a ini file with the followings keys 'HOST','DB','USER','PWD','PORT','DRIVER'");
            }
        }
        $config->user = $raw_conf['USER'];
        $config->pwd = $raw_conf['PWD'];
        if ($raw_conf['DRIVER'] === 'mysql') {
            $config->dsn = "mysql:host=" . $raw_conf['HOST'] . ";dbname=" . $raw_conf['DB'] . ";port=" . intval($raw_conf['PORT']);
        } else {
            $config->dsn = "oci:dbname=". $raw_conf['HOST'] .":". intval($raw_conf['PORT'])."/". $raw_conf['DB'];
        }
        return $config;
    }

}
