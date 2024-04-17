<?php
return [
    'payme_login'=>'Paycom', //Default: Paycom
    'payme_merchant_id'=>'MERCHANT_ID', //Merchant ID (Kassa ID)
    'payme_key'=>'KEY', //For testing Test Key
    'payme_min_amount'=>1000,
    'payme_max_amount'=>999999999,
    'payme_account'=>'order_id',
    'payme_type'=>1, //Bir martalik - 1 | Jamg'arib boriladigan - 2
    'payme_callback_url'=>'', //To'lovlar haqidagi ma'lumot kelib tushadigan url

    'allowed_ips' => [
        '185.178.51.131', '185.178.51.132', '195.158.31.134', '195.158.31.10', '195.158.28.124', '195.158.5.82', '127.0.0.1'
    ],
    
    'sql'=>[
        'dsn'=>[
            'host'=>'localhost',
            'port'=>'3306',
            'dbname'=>'', //DB nomi
            'charset'=>'utf8mb4'
        ],

        'dbtype'=>'mysql',
        'dbuser'=>'', //DB user
        'dbpass'=>'' //DB password
    ]
];
?>
