<?php
namespace TurgunboyevUz\Payme\Enums;

use ReflectionClass;

class PaymeMethods{
    public const CheckPerformTransaction = 'CheckPerformTransaction';
    public const CreateTransaction = 'CreateTransaction';
    public const PerformTransaction = 'PerformTransaction';
    public const CancelTransaction = 'CancelTransaction';
    public const CheckTransaction = 'CheckTransaction';
    public const GetStatement = 'GetStatement';
    public const SetFiscalData = 'SetFiscalData';

    public static function getConstants(){
        $reflection = new ReflectionClass(__CLASS__);
        return $reflection->getConstants();
    }
}
?>