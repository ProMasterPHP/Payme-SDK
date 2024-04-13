<?php
require "src/autoload.php";

use TurgunboyevUz\Payme\Payme;


$config = require('config.php');
echo (new Payme())->handle($config);
?>