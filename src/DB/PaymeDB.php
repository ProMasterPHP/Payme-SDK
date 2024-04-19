<?php
namespace TurgunboyevUz\Payme\DB;

use PDO;
use Exception;

class PaymeDB{
    public $conn;
    protected $stmt;

    public function __construct($config){
        $this->conn = self::connection($config['sql']['dbtype'], $config['sql']['dsn'], $config['sql']['dbuser'], $config['sql']['dbpass']);
    }

    public static function connection($dbtype, $dsn_param, $username, $password)
    {
        $dsn = $dbtype . ':' . http_build_query($dsn_param, '', ';');

        return new PDO($dsn, $username, $password, [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }

    /**
     * @param string $query Query that will be executed
     * @param array $params Params that will be passed to the query
     * @return $this
     */
    public function query(string $query, array $params = []){
        $stmt = $this->conn->prepare($query);
        
        if($stmt->execute($params)){
            $this->stmt = $stmt;

            return $this;
        }else{
            throw new Exception(json_encode([
                'code'=>$stmt->errorCode(),
                'description'=>$stmt->errorInfo()
            ]));
        }
    }

    public function fetch(){
        return $this->stmt->fetch();
    }

    public function fetchAll(){
        return $this->stmt->fetchAll();
    }

    public function rowCount(){
        return $this->stmt->rowCount();
    }

    public function lastInsertID(){
        return $this->conn->lastInsertId();
    }

    /**
     * @param string $table
     * @param array $values
     * @param array $where
     * @param mixed $limit 0 => limit, 1 => offset
     * @param array $order_by 0 => column, 1 => direction
     */
    public function select(string $table, array $values = [], array $where = [], mixed $limit = [], array $order_by = []){
        $query = "SELECT ".implode(', ', $values)." FROM `$table`";

        if(!empty($where)){
            $query .= " WHERE " . $this->prepareWhereParams($where);
        }

        if(!empty($limit)){
            if(is_array($limit)){
                $query .= " LIMIT " . $limit[0] . ", " . $limit[1];
            }else{
                $query .= " LIMIT " . $limit;
            }
        }

        if(!empty($order_by)){
            $query .= " ORDER BY " . $order_by[0] . $order_by[1];
        }

        $query .= ";";
        return $this->query($query, $where);
    }

    public function insert($table, $params){
        $columns = "";
        $values = "";

        foreach($params as $key => $value){
            $columns .= "$key, ";
            $values .= ":$key, ";
        }

        return $this->query("INSERT INTO $table (".rtrim($columns, ', ').") VALUES (".rtrim($values, ', ').")", $params);
    }

    public function update($table, $params, $where = []){
        return $this->query("UPDATE $table SET " . $this->prepareUpdateParams($params) . " WHERE " . $this->prepareWhereParams($where) . ";", array_merge($params, $where));
    }

    public function delete($table, $where = []){
        return $this->query("DELETE FROM $table WHERE " . $this->prepareWhereParams($where) . ";", $where);
    }

    private function prepareUpdateParams($params){
        $str = "";
        foreach($params as $key => $value){
            $str .= "{$key} = :{$key}, ";
        }

        return rtrim($str, ', ');
    }

    private function prepareWhereParams($params){
        $str = "";
        foreach($params as $key => $value){
            $str .= "{$key} = :{$key} AND ";
        }

        return rtrim($str, ' AND ');
    }
}
?>