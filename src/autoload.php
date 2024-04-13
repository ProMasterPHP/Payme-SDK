<?php
$prefix = 'TurgunboyevUz\Payme';

spl_autoload_register(function ($className) use($prefix){
    if(stripos($className, $prefix)!==false){
        $baseDir = __DIR__ . '/';
        $className = str_replace($prefix, '', $className);

        $filePath = $baseDir . str_replace('\\', '/', $className) . '.php';

        if (file_exists($filePath)) {
            require $filePath;
        }else{
            echo die($filePath." file doesn't exist!");
        }
    }
});

?>