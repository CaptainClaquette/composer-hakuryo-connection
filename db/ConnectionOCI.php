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

    const QUERY_TYPE_SEARCH = 1;
    const QUERY_TYPE_MODIFY = 2;

    public $connection;

    /**
     * Create a new instance of ConnectionOCI from a ini file.
     * The ini file MUST have the following keys : HOST,DB,USER,PWD,PORT
     * @param type $config_path the location of the ini file
     * @return ConnectionOCI
     */
    public function __construct($config_path) {
        $conf = parse_ini_file($config_path);
        $host = $conf["HOST"];
        $db = $conf["DB"];
        $user = $conf["USER"];
        $pwd = $conf["PWD"];
        $port = $conf["PORT"];
        $this->connection = oci_connect($user, $pwd, "$host:$port/$db");
    }

    /**
     * Perform a SELECT/SHOW/DESCRIB request and expect multiple result
     * @param string $request the request to execute. You can use preparedQuery placeholder in $request
     * @param array $args An associative array where the keys are the placeholders of the preparedQuery
     * @return array An array of stdClass object.
     * @throws Exception If the query is not SELECT/SHOW/DESCRIB
     */
    public function search(string $request, array $args = []): array {
        $this->check_query_type($request, self::QUERY_TYPE_SEARCH);
        $res = [];
        $statement = oci_parse($this->connection, $request);
        $this->bind_values($statement, $args);
        oci_execute($statement);
        while ($obj = oci_fetch_object($statement)) {
            array_push($res, $obj);
        }
        return $res;
    }

    /**
     * Perform a SELECT/SHOW/DESCRIB query and get only the first result
     * @param string $request the request to execute. You can use preparedQuery placeholder in $request
     * @param array $args An associative array where the keys are the placeholders of the preparedQuery
     * @return array An array of stdClass object.
     * @throws Exception If the query is not SELECT/SHOW/DESCRIB
     */
    public function get($request, $args = []): array {
        $this->check_query_type($request, self::QUERY_TYPE_SEARCH);
        $statement = oci_parse($this->connection, $request);
        $this->bind_values($statement, $args);
        oci_execute($statement);
        $obj = oci_fetch_object($statement);
        return $obj ? $obj : null;
    }
    
    /**
     * Perform a UPDATE/INSERT/DELETE and return the number of affected rows
     * @param string $request the request to execute. You can use preparedQuery placeholder in $request
     * @param array $args An associative array where the keys are the placeholders of the preparedQuery
     * @return int the number of affected rows
     * @throws Exception If the query is not UPDATE/INSERT/DELETE
     */
    public function modify($request, $args = []): int {
        $this->check_query_type($request, self::QUERY_TYPE_MODIFY);
        $res = [];
        $statement = oci_parse($this->connection, $request);
        $this->bind_values($statement, $args);
        oci_execute($statement);
        return oci_num_rows($statement);
    }
    
    /**
     * Close the oci conection
     */
    public function disconnect() {
        oci_close($this->connection);
    }

    private function bind_values(Ressource &$stmt, array $args) {
        foreach ($args as $key => $value) {
            oci_bind_by_name($stmt, ":$key", $value);
        }
    }

    private function check_query_type(string $query, int $type) {
        $rqType = explode(' ', $query);
        switch ($type) {
            case self::QUERY_TYPE_SEARCH:
                if (preg_match("/insert|delete|update/", strtolower($rqType[0]))) {
                    throw new Exception('The query must be of type : SELECT, DESCRIBE or SHOW');
                }
                break;
            case self:: QUERY_TYPE_MODIFY:
                if (!preg_match("/insert|delete|update/", strtolower($rqType[0]))) {
                    throw new Exception('The query must be of type : UPDATE, DELETE or INSERT');
                }
                break;
        }
    }

}
