<?php

namespace XDC\PHP\Foundation\Transaction;


use kornrunner\Ethereum\Transaction as BaseTransaction;
use XDC\PHP\Foundation\Xdc;

include "SignedTransaction.php";




class Transaction{
    private $transaction;
    private $eth;

    public function __construct(BaseTransaction $transaction, Xdc $eth = null){
        $this->transaction = $transaction;
        $this->eth         = $eth;
    }

    public function sign($privateKey){
        $privateKey = str_replace('0x', '', $privateKey);
        return new SignedTransaction('0x' . $this->transaction->getRaw($privateKey), $this->eth);
    }
}
