<?php

namespace Supermetrics;

class DatabaseService {
    private $mysqli;

    /**
     * @param $mysqli : the Mysqli connection to wrap. If null, connects to default database
     */
    public function __construct($mysqli = null) {

        $environmentService = \Supermetrics::get('EnvironmentService');

        $this->mysqli = new \mysqli(
            $environmentService->getEnvironmentSetting('hostname', 'database'),
            $environmentService->getEnvironmentSetting('username', 'database'),
            $environmentService->getEnvironmentSetting('password', 'database'),
            $environmentService->getEnvironmentSetting('database', 'database')
        );

        if (!$this->mysqli) {
            throw new \Exception(mysqli_connect_error());
        }

        if ($this->mysqli->connect_errno) {
            throw new \Exception("Database Connection Error " . $this->mysqli->connect_errno . ": " . $this->mysqli->connect_error);
        }

        if ($this->mysqli->errno) {
            throw new \Exception("Database Connection Error " . $this->mysqli->errno . ": " . $this->mysqli->error);
        }

        $$this->mysqli->set_charset('utf8');
    }

    /**
     * Executes an SQL query and returns the first result row as an associative array
     *
     * @param $sql : a string containing the SQL query. Question mark placeholders are supported
     * @param $params : an array of values to bind to the Question mark placeholders in the provided query string
     * @throws exception if query results in an error
     * @return array|null An associative array representing the first row of the result or null if the result was empty
     */
    public function getRow($sql, $params = []) {
        $result = $this->_query($sql, $params);

        return $result->fetch_assoc();
    }

    private function _query($sql, $params) {

        // MySQL does not support every possible query through the prepared statement mechanism, so if there are no
        // params, and the query is not a SELECT query, we run it through the mysqli->query method.
        //
        // The reason that we send SELECT statements through the prepared statement mechansim even when they have no
        // parameters is because mysqli has slightly different behavior for the result values when making a direct
        // query vs. making a query as a prepared statement. When a SELECT is made as a prepared statement, the types
        // of result set column values match the database (strings for strings and numbers for numbers), but when a
        // SELECT is made directly, the result set column values are always strings. This inconsistency can be very
        // frustrating when values are encoded via JSON and sent to the client, so we always send SELECT statements
        // throug the prepared statement mechanism so that result value behavior is always the same (where numeric
        // types come back as PHP numbers and encode as JSON numbers).

        if (count($params) === 0 && !$this->isSelectQuery($sql)) {
            $result = $this->mysqli->query($sql);

            if ($result === false) {
                throw new \Exception($this->mysqli->error);
            } else {
                return $result;
            }
        }

        $bindParams = [''];
        foreach ($params as $param) {
            switch (gettype($param)) {
                case 'boolean':
                case 'integer':
                    $bindParams[0] .= 'i';
                    break;
                case 'double':
                    $bindParams[0] .= 'd';
                    break;
                case 'NULL':
                case 'string':
                    $bindParams[0] .= 's';
                    break;
                default:
                    throw new \Exception('cannot bind param of type ' . gettype($param));
            }

            $bindParams[] = $param;
        }

        $stmt = $this->mysqli->prepare($sql);

        if (!$stmt) {
            throw new \Exception($this->mysqli->error);
        }

        if (count($bindParams) > 1) {
            // create array of refs for the mysqli bind_param function
            $bindParamRefs = [];
            foreach ($bindParams as $key => $val) {
                $bindParamRefs[$key] = &$bindParams[$key];
            }

            call_user_func_array(array($stmt, 'bind_param'), $bindParamRefs);
        }

        $ok = $stmt->execute();

        if (!$ok) {
            throw new \Exception($stmt->error);
        }

        $result = $stmt->get_result();

        if ($result === false && $stmt->error) {
            // sometimes errors don't happen until you actually fetch results
            // for instance, this happens when a subquery that is expected to return one result returns more than one
            throw new \Exception($stmt->error);
        }

        return $result;
    }

    private function isSelectQuery($sql) {
        return preg_match('/^\s*select\s/i', $sql);
    }
}
