<?php

namespace TurgunboyevUz\Payme\Middleware;

use TurgunboyevUz\Payme\Exceptions\PaymeException;
use Closure;

class PaymeCheck
{
    protected $config;
    protected $next;
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     * @throws PaymeException
     */

    public function __construct(array $config){
        $this->config = $config;
    }

    public function handle(Closure $next)
    {
        $headers = getallheaders();
        $authorization = $headers['Authorization'] ?? null;
        if(!$authorization ||
            !preg_match('/^\s*Basic\s+(\S+)\s*$/i', $authorization, $matches) ||
            base64_decode($matches[1]) != $this->config['payme_login'] . ":" . $this->config['payme_key'])
        {
            throw new PaymeException(PaymeException::AUTH_ERROR);
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? null;

        if(!in_array($ip, $this->config['allowed_ips']))
        {
            throw new PaymeException(PaymeException::AUTH_ERROR);
        }

        return $next($this->config);
    }
}

?>