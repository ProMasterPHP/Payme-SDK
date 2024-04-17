<?php
namespace TurgunboyevUz\Payme\Models;

use TurgunboyevUz\Payme\DB\PaymeDB;

class PaymeOrder extends PaymeDB{
    public function __construct($config){
        parent::__construct($config);
    }

    public function migrate(){
        return $this->query("CREATE TABLE IF NOT EXISTS payme_order (
            `id` INT PRIMARY KEY AUTO_INCREMENT,
            `state` INT DEFAULT 1,
            `amount` BIGINT,
            `params` TEXT,
            `details` TEXT,
            `paid_at` TIMESTAMP DEFAULT NULL,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );");
    }

    public function createOrder(int $amount = 0, array $params = [], array $details = []){
        $this->query("INSERT INTO `payme_order`(`amount`, `params`, `details`) VALUES(:amount, :params, :details)", [
            'amount'=>$amount,
            'params'=>json_encode($params),
            'details'=>json_encode($details)
        ]);

        return ($this->query("SELECT LAST_INSERT_ID() as `order_id` FROM `payme_order`;")->fetch())['order_id'];
    }

    public function getOrder($id = 0){
        $query = $this->query("SELECT * FROM `payme_order` WHERE `id` = :id", [
            'id'=>$id
        ]);

        if($query->rowCount() > 0){
            return $query->fetch();
        }else{
            return false;
        }
    }

    public function setOrder($id, $params = []){
        $this->query("UPDATE `payme_order` SET".$this->prepareUpdate($params)." WHERE `id`='{$id}'", $params);
    }
}
?>