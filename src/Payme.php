<?php
namespace TurgunboyevUz\Payme;

use TurgunboyevUz\Payme\Handlers\PaymeRequestHandler;
use TurgunboyevUz\Payme\Middleware\PaymeCheck;
use TurgunboyevUz\Payme\Services\PaymeService;
use TurgunboyevUz\Payme\Exceptions\PaymeException;
use TurgunboyevUz\Payme\Traits\JsonRPC;


class Payme{

    /**
     * @throws PaymeException
    */
    use JsonRPC;
    
    function handle(array $config){
        try{
            return (new PaymeCheck($config))->handle(function(array $config){
                $handler = new PaymeRequestHandler;
            
                return (new PaymeService($config, $handler->params))->{$handler->method}();
            });
        }catch(PaymeException $e){
            echo $this->error($e->error);
        }
    }
}
?>