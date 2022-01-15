<?php

namespace XDC\PHP\TOKEN20;


include "Utils\Number.php";
use XDC\PHP\Utils\Number;
include "Foundation\StandardXRC20Token.php";
use XDC\PHP\Foundation\StandardXRC20Token;

class token20 extends StandardXRC20Token{
    protected $contractAddress;

    public function __construct($contractAddress, $ethClient, $timeout = 10){
        $this->contractAddress = $contractAddress;
        parent::__construct($ethClient, $timeout);
    }
    
}
