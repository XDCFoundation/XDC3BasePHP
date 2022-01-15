<?php


namespace XDC\PHP\Foundation;

include "XRC.php";

use XDC\PHP\Foundation\Transaction\TransactionBuilder;
use XDC\PHP\Foundation\XRC;
use XDC\PHP\Utils\Number;

abstract class StandardXRC721Token extends XRC{

    protected $gasPriceModifier = 0;
    protected $contractAddress;
    protected $decimals;
    protected $gasLimits = [
        'approve'      => 3000000,
        'transfer'     => 3000000,
        'transferFrom' => 3000000,
        'default'      => 3000000
    ];


    public function __construct($ethClient, $timeout = 10){
        $abi = file_get_contents(__DIR__ . '/../resources/xrc721.abi.json');
        parent::__construct($this->contractAddress, $abi, $ethClient, $timeout);
    }

    public function name(): string{
        return $this->call('name')[0];
    }

    public function symbol(): string{
        return $this->call('symbol')[0];
    }

    public function totalSupply(): string{
        return $this->call('totalSupply')[0];
    }

    public function ownerOf(int $tokenId) {
        return $this->call('ownerOf', [$tokenId])[0];
    }

    public function balanceOf(string $ownerAddress){
        $balance = ($this->call('balanceOf', [$ownerAddress])[0]->toString());
        return $balance;
    }

    public function tokenURI(int $tokenId) {
        return $this->call('tokenURI', [$tokenId])[0];
    }

    public function tokenByIndex(int $index) {
        return $this->call('tokenByIndex', [$index])[0];
    }

    public function tokenOfOwnerByIndex(String $ownerAddress,int $index) {
        return $this->call('tokenOfOwnerByIndex', [$ownerAddress,$index])[0];
    }

    public function supportsInterface(String $interfaceId) {
        $result =  ($this->call('supportsInterface', [$interfaceId])[0]);
        if ($result==1){
            return "True";
        }else{
            return "False";
        }
    } 

    public function approve721(string $to, int $tokenId){
        $data = $this->buildApproveData($to, $tokenId);
        $ownerAddress = $this->ownerOf($tokenId);
        $nonce = Number::toHex($this->getEth()
            ->getTransactionCount( $ownerAddress, 'pending'));
        $gasLimit = $this->getGasLimit('approve');
        $gasPrice = $this->getSafeGasPrice();
        return (new TransactionBuilder())
            ->setEth($this->getEth())
            ->to($this->contractAddress)
            ->nonce($nonce)
            ->gasPrice($gasPrice)
            ->gasLimit($gasLimit)
            ->data($data)
            ->amount(0)
            ->build();
    }
    public function buildApproveData(string $to, int $tokenId){
        return $this->getContract()
            ->at($this->contractAddress)
            ->getData('approve', $to, $tokenId);
    }

    public function getApproved(int $tokenId) {
        return $this->call('getApproved', [$tokenId])[0];
    }

    public function transfer( string $from, string $to, int $tokenId){
        $data = $this->buildTTransferData($from, $to, $tokenId);
        $nonce = Number::toHex($this->getEth()
            ->getTransactionCount($from, 'pending'));
        $gasLimit = $this->getGasLimit('transferFrom');
        $gasPrice = $this->getSafeGasPrice(); return (new TransactionBuilder())
            ->setEth($this->getEth())
            ->to($this->contractAddress)
            ->nonce($nonce)
            ->gasPrice($gasPrice)
            ->gasLimit($gasLimit)
            ->data($data)
            ->amount(0)
            ->build();
    }
    public function buildTTransferData(string $from, string $to, int $tokenId){
        return $this->getContract()
            ->at($this->contractAddress)
            ->getData('transferFrom', $from, $to, $tokenId);
    }
    
    public function transferFrom721( string $from, string $to, int $tokenId){
        $data = $this->buildTransferFromData($from, $to, $tokenId);
        $ownerAddress= $this->getApproved($tokenId);
        $nonce = Number::toHex($this->getEth()
            ->getTransactionCount($ownerAddress, 'pending'));
        $gasLimit = $this->getGasLimit('transferFrom');
        $gasPrice = $this->getSafeGasPrice();
        return (new TransactionBuilder())
            ->setEth($this->getEth())
            ->to($this->contractAddress)
            ->nonce($nonce)
            ->gasPrice($gasPrice)
            ->gasLimit($gasLimit)
            ->data($data)
            ->amount(0)
            ->build();
    }
    public function buildTransferFromData(string $from, string $to, int $tokenId){
        return $this->getContract()
            ->at($this->contractAddress)
            ->getData('transferFrom', $from, $to, $tokenId);
    }

    public function safeTransferFrom( string $from, string $to, int $tokenId){
        $data = $this->buildSafeTransferFromData($from, $to, $tokenId);
        $ownerAddress= $this->getApproved($tokenId);
        $nonce = Number::toHex($this->getEth()
            ->getTransactionCount($ownerAddress, 'pending'));
        $gasLimit = $this->getGasLimit('transferFrom');
        $gasPrice = $this->getSafeGasPrice();
        return (new TransactionBuilder())
            ->setEth($this->getEth())
            ->to($this->contractAddress)
            ->nonce($nonce)
            ->gasPrice($gasPrice)
            ->gasLimit($gasLimit)
            ->data($data)
            ->amount(0)
            ->build();
    }
    public function buildSafeTransferFromData(string $from, string $to, int $tokenId){
        return $this->getContract()
            ->at($this->contractAddress)
            ->getData('safeTransferFrom', $from, $to, $tokenId);
    }

    public function setApprovalForAll( string $to, bool $approve, $tokenId){
        $data = $this->buildsetApprovalForAllData($to, $approve);
        $ownerAddress = $this->ownerOf($tokenId);
        $nonce = Number::toHex($this->getEth()
            ->getTransactionCount( $ownerAddress , 'pending'));
        $gasLimit = $this->getGasLimit('approve');
        $gasPrice = $this->getSafeGasPrice();
        return (new TransactionBuilder())
            ->setEth($this->getEth())
            ->to($this->contractAddress)
            ->nonce($nonce)
            ->gasPrice($gasPrice)
            ->gasLimit($gasLimit)
            ->data($data)
            ->amount(0)
            ->build();
    }
    public function buildsetApprovalForAllData(string $to, bool $approve){
        return $this->getContract()
            ->at($this->contractAddress)
            ->getData('setApprovalForAll', $to, $approve);
    }

    public function isApprovedForAll( string $owner, string $operator) : bool{
        return $this->call('isApprovedForAll', [$owner, $operator])[0];
    }
    
    public function getGasLimit($action = ''){
        return isset($this->gasLimits[$action]) ? $this->gasLimits[$action] : $this->gasLimits['default'];
    }
    public function getSafeGasPrice(){
        $gasPrice = $this->getEth()
                         ->gasPrice()
        ;

        $modified = floatval(Number::fromWei($gasPrice, 'gwei')) + $this->gasPriceModifier;
        return Number::toWei($modified, 'gwei')
                     ->toString()
            ;
    }
}
