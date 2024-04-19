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
        return $this->insert('payme_order', [
            'amount' => $amount,
            'params' => json_encode($params),
            'details' => json_encode($details)
        ])->lastInsertID();
    }

    public function getOrder($id = 0){
        return $this->select("payme_order", ["*"], [ "id" => $id ])->fetch() ?? false;
    }

    public function setOrder($id, $params = []){
        return $this->update("payme_order", $params, [ "id" => $id ]);
    }
}
?>