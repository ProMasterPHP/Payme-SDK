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
            $this->query("INSERT INTO `payme_account`(`user_id`) VALUES(:user_id)", [
                'user_id'=>$user_id
            ]);

            return ($this->query("SELECT LAST_INSERT_ID() as id FROM `payme_account`;")->fetch())['id'];
        }else{
            return $user['id'];
        }
    }

    public function getAccountByID($user_id){
        $query = $this->query("SELECT * FROM `payme_account` WHERE `user_id` = :user_id", [
            'user_id'=>$user_id
        ]);
    
        if($query->rowCount() > 0){
            return $query->fetch();
        }else{
            return false;
        }
    }

    public function getAccount($id){
        $query = $this->query("SELECT * FROM `payme_account` WHERE `id` = :id", [
            'id'=>$id
        ]);
    
        if($query->rowCount() > 0){
            return $query->fetch();
        }else{
            return false;
        }
    }
    
    public function setAccount($userId, $params = []){
        $this->query("UPDATE `payme_account` SET".$this->prepareUpdate($params)." WHERE `user_id`='{$userId}'", $params);
    }    
}
?>