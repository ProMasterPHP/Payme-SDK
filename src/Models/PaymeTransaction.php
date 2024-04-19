<?php
namespace TurgunboyevUz\Payme\Models;

use TurgunboyevUz\Payme\DB\PaymeDB;

class PaymeTransaction extends PaymeDB{
    public function __construct($config){
        parent::__construct($config);
    }

    public function migrate(){
        $this->query("CREATE TABLE IF NOT EXISTS payme_transaction (
            `id` INT PRIMARY KEY AUTO_INCREMENT,
            `transaction` VARCHAR(255),
            `code` VARCHAR(255),
            `state` VARCHAR(255),
            `owner_id` VARCHAR(255),
            `amount` BIGINT,
            `reason` VARCHAR(255),
            `payme_time` VARCHAR(255),
            `cancel_time` VARCHAR(255),
            `create_time` VARCHAR(255),
            `perform_time` VARCHAR(255),
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );");
    }

    public function createTransaction($params = []){
        return $this->insert('payme_transaction', $params)->lastInsertID();
    }

    public function getTransactionBy($key, $value){
        return $this->select("payme_transaction", ["*"], [ $key => $value ])->fetch() ?? false;
    }

    public function getTransactionBetween($from, $to){
        $query = $this->query("SELECT * FROM `payme_transaction` WHERE create_time >= :fromDate AND create_time <= :toDate", [
            'fromDate'=>$from,
            'toDate'=>$to
        ]);

        if($query->rowCount() > 0){
            return $query->fetchAll();
        }else{
            return false;
        }
    }

    public function getTransaction($id){
        return $this->getTransactionBy('transaction', $id);
    }

    public function setTransaction($id, $params = []){
        $this->update("payme_transaction", $params, [ "transaction" => $id ]);
    }
}
?>