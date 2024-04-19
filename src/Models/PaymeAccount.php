<?php
namespace TurgunboyevUz\Payme\Models;

use TurgunboyevUz\Payme\DB\PaymeDB;

class PaymeAccount extends PaymeDB{
    public function __construct($config){
        parent::__construct($config);
    }

    public function migrate(){
        $this->conn->query("CREATE TABLE IF NOT EXISTS payme_account (
            `id` INT PRIMARY KEY AUTO_INCREMENT,
            `user_id` BIGINT,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );");
    }

    public function createAccount($user_id){
        $user = $this->getAccountByID($user_id);
        if(!$user){
            return $this->insert("payme_account", ["user_id"=>$user_id])->lastInsertID();
        }else{
            return $user['id'];
        }
    }

    public function getAccountByID($user_id){
        return $this->select("payme_account", ["*"], ["user_id"=>$user_id])->fetch() ?? false;
    }

    public function getAccount($id){
        return $this->select("payme_account", ["*"], ["id"=>$id])->fetch() ?? false;
    }
    
    public function setAccount($userId, $params = []){
        $this->update("payme_account", $params, [ "user_id" => $userId ]);
    }    
}
?>