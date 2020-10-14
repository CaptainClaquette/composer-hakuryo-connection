<?php

namespace hakuryo\db;

use PDO;
use PDOStatement;

class ConnectionDB extends PDO {

    const QUERY_TYPE_SEARCH = 1;
    const QUERY_TYPE_MODIFY = 2;

    public function __construct(string $dsn, string $username = NULL, string $passwd = NULL, array $options = NULL) {
        parent::__construct($dsn, $username, $passwd, $options);
        $this->query("SET NAMES 'utf8'");
    }

    public static function from_file($path, $section = "DB"): ConnectionDB {
        $conf = parse_ini_file($path);
        $host = $conf["HOST"];
        $db = $conf["DB"];
        $user = $conf["USER"];
        $pwd = $conf["PWD"];
        $port = $conf["PORT"];
        $url = "mysql:host=$host;dbname=$db;port=$port";
        $con = new ConnectionDB($url, $user, $pwd);
        $con->query("SET NAMES 'utf8'");
        return $con;
    }

    /**
     * Perform a SELECT/SHOW/DESCRIB request and expect multiple results
     * @param string $request the request to execute. You can use preparedQuery placeholder in $request
     * @param array $args Argument of the prepared query
     * @param bool $assoc if assoc is true, $args must be an associative array with
     * string key else a 0 indexed array
     * @return array An array of stdClass object.
     * @throws Exception If the query is not SELECT/SHOW/DESCRIB
     */
    public function search(string $request,array $args = [], $assoc = true): array {
        $this->check_query_type($request, self::QUERY_TYPE_SEARCH);
        $stmt = $this->prepare($request);
        $this->bind_values($stmt, $args, $assoc);
        $stmt->execute();
        return $this->cast_data($stmt);
    }
    
    /**
     * Perform a SELECT/SHOW/DESCRIB request and get the first result
     * @param string $request the request to execute. You can use preparedQuery placeholder in $request
     * @param array $args Argument of the prepared query
     * @param bool $assoc if assoc is true, $args must be an associative array with
     * string key else a 0 indexed array
     * @return array An array of stdClass object.
     * @throws Exception If the query is not SELECT/SHOW/DESCRIB
     */
    public function get($request, $args = [], $assoc = true): array {
        $result = $this->search($request, $args, $assoc);
        return count($result) > 0 ? $result[0] : null;
    }
    
    /**
     * Perform a UPDATE/INSERT/DELETE and return the number of affected rows
     * @param string $request the request to execute. You can use preparedQuery placeholder in $request
     * @param array $args Argument of the prepared query
     * @param bool $assoc if assoc is true, $args must be an associative array with
     * string key else a 0 indexed array
     * @return int the number of affected rows
     * @throws Exception If the query is not UPDATE/INSERT/DELETE
     */
    public function modify($request, $args = [], $assoc = true): int {
        $this->check_query_type($request, self::QUERY_TYPE_MODIFY);
        $stmt = $this->prepare($request);
        $assoc ? $this->bind_assoc_values($stmt, $args) : $this->bind_values($stmt, $args);
        $stmt->execute();
        return $stmt->rowCount();
    }

    private function bind_values(PDOStatement &$stmt, array $args, $assoc) {
        if ($assoc) {
            foreach ($args as $k => $v) {
                $stmt->bindValue($k, $v);
            }
        } else {
            for ($i = 0; $i < count($args); $i++) {
                $stmt->bindValue($i + 1, $v);
            }
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

    private function cast_data(PDOStatement $stmt) {
        $result = [];
        $metas = [];
        foreach (range(0, $stmt->columnCount() - 1) as $column_index) {
            $metas[] = $stmt->getColumnMeta($column_index);
        }
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $obj = new \stdClass();
            foreach ($row as $index => $value) {
                $meta = $metas[$index];
                $name = $meta['name'];
                $obj->$name = $this->get_casted_value($meta, $value);
            }
            $result[] = $obj;
        }
        return $result;
    }

    private function get_casted_value($meta, $value) {
        switch ($meta['native_type']) {
            case 'LONG':
            case 'INT':
                return intval($value);
            case 'TIMESTAMP':
                return \DateTime::createFromFormat("Y-m-d H:i:s", $value)->getTimestamp();
            case 'TINY':
                return $meta['len'] > 1 ? intval($value) : intval($value) == 1;
            default :
                return $value;
        }
    }

}
