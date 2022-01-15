<?php

namespace XDC\PHP\TOKEN721;

include "Foundation/StandardXRC721.php";
use XDC\PHP\Foundation\StandardXRC721Token;


class token721 extends StandardXRC721Token{
    protected $contractAddress;
    public function __construct($contractAddress, $ethClient, $timeout = 10){
        $this->contractAddress = $contractAddress;
        parent::__construct($ethClient, $timeout);
    }
   
}
