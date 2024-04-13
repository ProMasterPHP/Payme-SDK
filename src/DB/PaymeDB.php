<?php
namespace TurgunboyevUz\Payme\DB;

use PDO;

class PaymeDB{
    public $conn;

    public function __construct($config){
        $dsn = $config['sql']['dbtype'] . ':' . http_build_query($config['sql']['dsn'], '', ';');

        $this->conn = new PDO($dsn, $config['sql']['dbuser'], $config['sql']['dbpass'], [
           PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC 
        ]);
    }

    public function query($query, $params = []){
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);

        return $stmt;
    }

    public function prepareUpdate($params = []){
        $str = "";
        foreach($params as $key => $value){
            $str .= " `{$key}` = :{$key},";
        }

        return substr($str, 0, -1);
    }
}
?>