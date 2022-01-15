<?php

namespace XDC\PHP\Foundation\Transaction;

include "Xdc.php";
use Exception;
use XDC\PHP\Foundation\Xdc;

class SignedTransaction{
    private $hash;
    private $eth;

    public function __construct(string $hash, Xdc $eth = null){
        $this->hash = $hash;
        $this->eth  = $eth;
    }

    public function getSignedHash(){
        return $this->hash;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function send(){
        if (!isset($this->eth)){
            throw new Exception('XDC client not provided. Signed transaction have not be broadcasted');
        }
        return $this->eth->sendRawTransaction($this->hash);
    }
}
