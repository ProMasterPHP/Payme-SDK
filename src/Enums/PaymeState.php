<?php
namespace TurgunboyevUz\Payme\Enums;

use ReflectionClass;

class PaymeState{
    public const Pending = 1;
    public const Done = 2;
    public const Cancelled = -1;
    public const Cancelled_After_Success = -2;

    public static function getConstants(){
        $reflection = new ReflectionClass(__CLASS__);
        return $reflection->getConstants();
    }
}
?>