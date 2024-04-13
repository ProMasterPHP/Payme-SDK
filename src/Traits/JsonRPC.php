<?php
namespace TurgunboyevUz\Payme\Traits;

trait JsonRPC
{
    public function success($result)
    {
        return json_encode([
            'jsonrpc' => '2.0',
            'result' => $result,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function error($error)
    {
        return json_encode([
            'jsonrpc' => '2.0',
            'error' => $error,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
?>