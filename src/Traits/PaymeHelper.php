<?php

namespace TurgunboyevUz\Payme\Traits;

trait PaymeHelper
{
    protected function microtime(): int
    {
        return (time() * 1000);
    }

    private function checkTimeout($created_time): bool
    {
        return $this->microtime() <= (intval($created_time) + $this->timeout);
    }

    public function isValidAmount($amount): bool
    {
        if ($amount < $this->minAmount || $amount > $this->maxAmount) {
            return false;
        }

        return true;
    }

    public function successCreateTransaction($createTime, $transaction, $state)
    {
        return $this->success([
            'create_time' => intval($createTime),
            'perform_time' => 0,
            'cancel_time' => 0,
            'transaction' => strval($transaction),
            'state' => intval($state),
            'reason' => null
        ]);
    }

    public function successCheckPerformTransaction()
    {
        return $this->success([
            'allow' => true
        ]);
    }

    public function successPerformTransaction($state, $performTime, $transaction)
    {
        return $this->success([
            'state' => intval($state),
            'perform_time' => intval($performTime),
            'transaction' => strval($transaction),
        ]);
    }

    public function successCheckTransaction($createTime, $performTime, $cancelTime, $transaction, $state, $reason)
    {
        return $this->success([
            'create_time' => intval($createTime) ?? 0,
            'perform_time' => intval($performTime) ?? 0,
            'cancel_time' => intval($cancelTime) ?? 0,
            'transaction' => strval($transaction),
            'state' => intval($state),
            'reason' => isset($reason)?intval($reason):null
        ]);
    }

    public function successCancelTransaction($state, $cancelTime, $transaction)
    {
        return $this->success([
            'state' => intval($state),
            'cancel_time' => intval($cancelTime),
            'transaction' => strval($transaction)
        ]);
    }

    public function successGetStatement(array $transactions){
        return $this->success([
            'transactions'=>$transactions
        ]);
    }

    public function hasParam($param): bool
    {
        if (is_array($param)) {
            foreach ($param as $item) {
                if(!$this->hasParam($item)) return false;
            }
            return true;
        } else {
            return isset($this->params[$param]) && !empty($this->params[$param]);
        }
    }

    public function sendData($callback_url, $data){
        $ch = curl_init($callback_url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-type: application/json'
        ]);

        curl_exec($ch);
        curl_close($ch);
    }
}
?>