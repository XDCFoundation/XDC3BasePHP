<?php



namespace XDC\PHP\Foundation\Contracts;

use XDC\PHP\Foundation\XRC;

interface EventLogBuilderInterface{
    public function build(\stdClass $log): array;
    public function setContract(XRC $contract);
}
