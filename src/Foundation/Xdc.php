<?php

namespace XDC\PHP\Foundation;

include "XdcBase.php";
use XDC\PHP\Foundation\XdcBase;

class Xdc extends XdcBase{
    public function getTransactionCount(string $address, string $blockParameter = 'latest'){
        return $this->call('getTransactionCount', [$address, $blockParameter])
                    ->toString();
    }

    public function gasPrice(){
        return $this->call('gasPrice')
                    ->toString();
    }

    public function sendRawTransaction(string $hash){
        return (string)$this->call('sendRawTransaction', [$hash]);
    }
}
