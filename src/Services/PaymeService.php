<?php
namespace TurgunboyevUz\Payme\Services;

use TurgunboyevUz\Payme\Enums\PaymeState;
use TurgunboyevUz\Payme\Exceptions\PaymeException;
use TurgunboyevUz\Payme\Models\PaymeTransaction;
use TurgunboyevUz\Payme\Models\PaymeOrder;
use TurgunboyevUz\Payme\Models\PaymeAccount;
use TurgunboyevUz\Payme\Payme;
use TurgunboyevUz\Payme\Traits\JsonRPC;
use TurgunboyevUz\Payme\Traits\PaymeHelper;

class PaymeService{
    use JsonRPC, PaymeHelper;

    protected $minAmount = 1000;
    protected $maxAmount = 1000000;

    protected $timeout = 6000*1000; 
    
    protected $identity = 'id';
    protected $callback_url;
    protected $type;

    public $params = [];
    public $config = [];

    public $db;

    public function __construct(array $config, array $params){
        $this->minAmount = $config['payme_min_amount'];
        $this->maxAmount = $config['payme_max_amount'];
        $this->identity = $config['payme_account'];

        $this->callback_url = $config['payme_callback_url'];
        $this->type = $config['payme_type'];

        $this->params = $params;
        $this->config = $config;
    }

    /**
     * @throws PaymeException
     */
    public function CheckPerformTransaction()
    {
        if(!$this->hasParam(['amount', 'account'])){
            throw new PaymeException(PaymeException::JSON_RPC_ERROR);
        }else{
            $amount = $this->params['amount'];

            if(!$this->isValidAmount($amount)){
                throw new PaymeException(PaymeException::WRONG_AMOUNT);
            }else{
                $account = $this->params['account'];
                
                if(!array_key_exists($this->identity, $account)){
                    throw new PaymeException(PaymeException::USER_NOT_FOUND);
                }else{
                    $account = $account[$this->identity];

                    if($this->type == 1){
                        $db = new PaymeOrder($this->config);
                        $user = $db->getOrder($account);

                        if(!$user){
                            throw new PaymeException(PaymeException::USER_NOT_FOUND);
                        }else{
                            if($user['state'] == PaymeState::Pending){
                                if($user['amount'] == $amount){
                                    return $this->successCheckPerformTransaction();
                                }else{
                                    throw new PaymeException(PaymeException::WRONG_AMOUNT);
                                }
                            }else{
                                if($user['state'] == PaymeState::Done){
                                    throw new PaymeException(PaymeException::PENDING_PAYMENT);
                                }

                                if($user['state'] == PaymeState::Cancelled or $user['state'] == PaymeState::Cancelled_After_Success){
                                    throw new PaymeException(PaymeException::USER_NOT_FOUND);
                                }
                            }
                        }
                    }else{
                        $db = new PaymeAccount($this->config);
                        $user = $db->getAccount($account);

                        if(!$user){
                            throw new PaymeException(PaymeException::USER_NOT_FOUND);
                        }else{
                            return $this->successCheckPerformTransaction();
                        }
                    }
                }
            }
        }
    }

    /**
     * @throws PaymeException
     */
    public function CreateTransaction(){
        if(!$this->hasParam(['id', 'time', 'amount', 'account'])){
            throw new PaymeException(PaymeException::JSON_RPC_ERROR);
        }
        
        $id = $this->params['id'];
        $time = $this->params['time'];
        $amount = $this->params['amount'];
        $account = $this->params['account'];

        if(!array_key_exists($this->identity, $account)){
            throw new PaymeException(PaymeException::USER_NOT_FOUND);
        }

        $account = $account[$this->identity];
        if($this->type == 1){
            $user = (new PaymeOrder($this->config))->getOrder($account);
        }else{
            $user = (new PaymeAccount($this->config))->getAccount($account);
        }

        if(!$user){
            throw new PaymeException(PaymeException::USER_NOT_FOUND);
        }

        if(!$this->isValidAmount($amount) or $amount != $user['amount']){
            throw new PaymeException(PaymeException::WRONG_AMOUNT);
        }

        $db = new PaymeTransaction($this->config);
        $transaction = $db->getTransaction($id);

        if($transaction){
            if ($transaction['state'] != PaymeState::Pending) {
                throw new PaymeException(PaymeException::CANT_PERFORM_TRANS);
            }

            if(!$this->checkTimeout($transaction['create_time'])){
                $db->setTransaction($id, [
                    'state' => PaymeState::Cancelled,
                    'reason' => 4
                ]);

                throw new PaymeException(PaymeException::CANT_PERFORM_TRANS, [
                    "uz" => "Vaqt tugashi o'tdi",
                    "ru" => "Тайм-аут прошел",
                    "en" => "Timeout passed"
                ]);
            }

            return $this->successCreateTransaction(
                $transaction['create_time'],
                $transaction['transaction'],
                $transaction['state']
            );
        }

        if($db->getTransactionBy('owner_id', $account)){
            throw new PaymeException(PaymeException::PENDING_PAYMENT);
        }

        $create_time = $this->microtime();
        $db->createTransaction([
            'transaction' => $id,
            'payme_time' => $time,
            'amount' => $amount,
            'state' => PaymeState::Pending,
            'create_time' => $create_time,
            'owner_id' => $account,
        ]);

        return $this->successCreateTransaction(
            $create_time,
            $id,
            PaymeState::Pending
        );
    }

    /**
     * @throws PaymeException
     */
    public function PerformTransaction(){
        if(!$this->hasParam('id')){
            throw new PaymeException(PaymeException::JSON_RPC_ERROR);
        }
        $id = $this->params['id'];

        $db = new PaymeTransaction($this->config);
        $transaction = $db->getTransaction($id);
        
        if(!$transaction)
        {
            throw new PaymeException(PaymeException::TRANS_NOT_FOUND);
        }

        if($transaction['state'] != PaymeState::Pending){
            if($transaction['state'] == PaymeState::Done)
            {
                return $this->successPerformTransaction($transaction['state'], $transaction['perform_time'], $transaction['transaction']);
            }else{
                throw new PaymeException(PaymeException::CANT_PERFORM_TRANS);
            }
        }

        if(!$this->checkTimeout($transaction['create_time']))
        {
            $db->setTransaction($id, [
                'state'=>PaymeState::Cancelled,
                'reason'=>4
            ]);

            throw new PaymeException(PaymeException::CANT_PERFORM_TRANS, [
                "uz" => "Vaqt tugashi o'tdi",
                "ru" => "Тайм-аут прошел",
                "en" => "Timeout passed"
            ]);
        }

        $perform_time = $this->microtime();
        $db->setTransaction($id, [
            'state'=>PaymeState::Done,
            'perform_time'=>$perform_time
        ]);

        $paid_at = date('Y-m-d H:i:s');
        if($this->type == 1){
            $db = new PaymeOrder($this->config);
            $db->setOrder($transaction['owner_id'], [
                'state'=>PaymeState::Done,
                'paid_at'=>$paid_at
            ]);

            $this->sendData($this->callback_url, [
                'order_id'=>$transaction['owner_id'],
                'state'=>PaymeState::Done,
                'paid_at'=>$paid_at
            ]);
        }else{
            $db = new PaymeAccount($this->config);
            $user = $db->getAccount($transaction['owner_id']);

            $this->sendData($this->callback_url, [
                'id'=>$user['user_id'],
                'state'=>PaymeState::Done,
                'amount'=>$transaction['amount']
            ]);
        }

        return $this->successPerformTransaction(PaymeState::Done, $perform_time, $transaction['transaction']);
    }

    /**
     * @throws PaymeException
     */
    public function CancelTransaction(){
        if(!$this->hasParam(['id', 'reason'])){
            throw new PaymeException(PaymeException::JSON_RPC_ERROR);
        }
        if(!array_key_exists('reason', $this->params)){
            throw new PaymeException(PaymeException::JSON_RPC_ERROR);
        }

        $id = $this->params['id'];
        $reason = $this->params['reason'];

        $db = new PaymeTransaction($this->config);
        $transaction = $db->getTransaction($id);
        $cancelTime = $this->microtime();
        if(!$transaction){
            throw new PaymeException(PaymeException::TRANS_NOT_FOUND);
        }

        if ($transaction['state'] == PaymeState::Pending) {
            $db->setTransaction($id, [
                'state'=>PaymeState::Cancelled,
                'reason'=>$reason,
                'cancel_time'=>$cancelTime
            ]);

            return $this->successCancelTransaction(PaymeState::Cancelled, $cancelTime, $id);
        }

        if ($transaction['state'] != PaymeState::Done) {
            return $this->successCancelTransaction($transaction['state'], $transaction['cancel_time'], $id);
        }

        $db->setTransaction($id, [
            'state'=>PaymeState::Cancelled_After_Success,
            'reason'=>$reason,
            'cancel_time'=>$cancelTime
        ]);

        if($this->type == 2){
            $this->sendData($this->callback_url, [
                'id'=>((new PaymeAccount($this->config))->getAccount($transaction['owner_id']))['id'],
                'state'=>PaymeState::Cancelled_After_Success
            ]);
        }

        return $this->successCancelTransaction(PaymeState::Cancelled_After_Success, $cancelTime, $id);
    }


    /**
     * @throws PaymeException
     */
    public function CheckTransaction(){
        if(!$this->hasParam('id'))
        {
            throw new PaymeException(PaymeException::JSON_RPC_ERROR);
        }

        $id = $this->params['id'];

        $db = new PaymeTransaction($this->config);
        $transaction = $db->getTransaction($id);

        if($transaction)
        {
            return $this->successCheckTransaction(
                $transaction['create_time'],
                $transaction['perform_time'],
                $transaction['cancel_time'],
                $transaction['transaction'],
                $transaction['state'],
                $transaction['reason']
            );
        }else{
            throw new PaymeException(PaymeException::TRANS_NOT_FOUND);
        }
    }

    public function GetStatement(){
        if(!$this->hasParam(['from', 'to'])){
            throw new PaymeException(PaymeException::JSON_RPC_ERROR);
        }

        $from = $this->params['from'];
        $to = $this->params['to'];

        $db = new PaymeTransaction($this->config);
        $transaction = $db->getTransactionBetween($from, $to);

        if(!$transaction){
            return $this->successGetStatement([]);
        }
        
        $statement = [];
        foreach($transaction as $element){
            $statement[] = [
                'id'=>$element['transaction'],
                'time'=>$this->microtime(),
                'amount'=>intval($element['amount']),
                'account'=>[
                    $this->identity => $element['owner_id']
                ],
                'create_time'=>intval($element['create_time']),
                'perform_time'=>intval($element['perform_time']),
                'cancel_time'=>intval($element['cancel_time']),
                'transaction'=>intval($element['id']),
                'state'=>intval($element['state']),
                'reason'=> isset($element['reason']) ? intval($element['reason']) : null,
                'receivers'=>[]
            ];
        }

        return $this->successGetStatement($statement);
    }

    public function SetFiscalData(){
        // pass
    }
}
?>