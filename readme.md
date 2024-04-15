
# Payme-SDK (Integration with system Payme.uz)


## Installation

```bash
  git clone https://github.com/ProMasterPHP/Payme-SDK.git
  mv Payme-SDK/* ./
  mv index.php Payme.php
  rm -rf Payme-SDK
```

## Add your configs to config.php
```php
return [
    'payme_login'=>'Paycom', //Default: Paycom
    'payme_merchant_id'=>'MERCHANT_ID', //Merchant ID (Kassa ID)
    'payme_key'=>'KEY', //For testing Test Key
    'payme_min_amount'=>1000,
    'payme_max_amount'=>1000000,
    'payme_account'=>'order_id', //Account object
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
```
# Set database (db.php)
```php
require __DIR__."/src/autoload.php";
$config = require("config.php");

use TurgunboyevUz\Payme\Models\PaymeTransaction;
use TurgunboyevUz\Payme\Models\PaymeOrder;
use TurgunboyevUz\Payme\Models\PaymeAccount;

$transaction = new PaymeTransaction($config);
$orders = new PaymeOrder($config); // payme_type => 1
$account = new PaymeAccount($config); // payme_type => 2

$transaction->migrate();
$orders->migrate();
$account->migrate();
```
```bash
php db.php
```
# Endpoint URL
```
https://domain.name/directory/subdirectory/Payme.php
```

## Usage/Examples

## Main file
```php
require __DIR__."/src/autoload.php";
$config = require("config.php");

use TurgunboyevUz\Payme\Models\PaymeOrder; // payme_type => 1
use TurgunboyevUz\Payme\Models\PaymeAccount; // payme_type => 2

/**
 * One time payment
 * Order ochish
 * @param amount Buyurtmaning umumiy miqdori (UZS da)
 * @param details Buyurtma haqidagi qo'shimcha ma'lumotlar.
 * @return int order_id
*/
$amount = 10000; //UZS
$details = [
    'owner_id' => 1 //Ex: buyurtmachi ID raqami
];

$orders = new PaymeOrder($config);
$order_id = $orders->createOrder($amount, $details);

/**
 * One time payment
 * Orderni yaroqsizga chiqarish
 * Buyurtmachi buyurtmani bekor qilgan vaqtda ushbu buyurtmani yaroqsizga chiqarish shart!
 * @param id Buyurtma ID raqami
 * @param params Buyurtma uchun maxsus argumentlar
 * @return void
*/

$order_id = 1;
$params = [
    'state' => -1 //Buyurtma bekor qilinganda $params ning 'state' argumentiga -1 yuboriladi
];

$orders = new PaymeOrder($config);
$orders->setOrder($order_id, $params);
```

## Callback URL dan ma'lumotlarni qabul qilib olish
```php
$payme = json_decode(file_get_contents('php://input'), true);
$order_id = $payme['order_id'];
$details = $payme['details']; //order yaratish vaqtida kiritilgan qo'shimcha argumentlar
$paid_at = $payme['paid_at']; //to'lov amalga oshirilgan vaqt
```


## Laravel alternative
- [Laravel Payme](https://github.com/khamdullaevuz/laravel-payme)

## Authors
- [@TurgunboyevUz](https://www.github.com/TurgunboyevUz/)
- [@khamdullaevuz](https://www.github.com/khamdullaevuz/)


## License

[MIT](https://choosealicense.com/licenses/mit/)