<?php
namespace TurgunboyevUz\Payme\Handlers;

use TurgunboyevUz\Payme\Enums\PaymeMethods;
use TurgunboyevUz\Payme\Exceptions\PaymeException;

class PaymeRequestHandler{
    public $method;
    public $params;

    /**
     * @throws PaymeException
     */
    public function __construct()
    {
        if($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new PaymeException(PaymeException::INVALID_HTTP_METHOD);
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if(!isset($data['method']) || !isset($data['params'])) {
            throw new PaymeException(PaymeException::JSON_PARSING_ERROR);
        }

        $this->method = $data['method'];
        $this->params = $data['params'];

        if(!in_array($this->method, PaymeMethods::getConstants())) {
            throw new PaymeException(PaymeException::METHOD_NOT_FOUND);
        }
    }
}
?>