<?php


namespace XDC\PHP\Foundation;

use XDC\PHP\Utils\Number;
include "Transaction/TransactionBuilder.php";
use XDC\PHP\Foundation\Transaction\TransactionBuilder;



abstract class StandardXRC20Token extends XRC{

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
        $abi = file_get_contents(__DIR__ . '/../resources/xrc20.abi.json');
        parent::__construct($this->contractAddress, $abi, $ethClient, $timeout);
    }


    //XRC20_READ OPERATIONS
    //1// Name Method
    public function name(): string{
        return $this->call('name')[0];
    }

    //2// Symbol Method
    public function symbol(): string{
        return $this->call('symbol')[0];
    }

    //3// Decimal Method
    public function decimals(): int{
        if ($this->decimals)
        {
            return $this->decimals;
        }
        return $this->decimals = intval($this->call('decimals')[0]->toString());
    }

    //4// Balanceof Method
    /**
     * @param string $address
     * @return string
     */
    public function balanceOf(string $address){
        return Number::scaleDown($this->call('balanceOf', [$address])['balance']->toString(), $this->decimals());
    }

    //5// TotalSupply Method
    /**
     * @return string
     */
    public function totalSupply(): string{
        return $this->call('totalSupply')[0];
        if ($this->totalSupply)
        {
            return $this->totalSupply;
        }
        return $this->totalSupply = intval($this->call('totalSupply')[0]->toString());
    }


    //6// Allowance Method 
    public function allowance(string $ownerAddress, string $spenderAddress){
        return Number::scaleDown($this->call('allowance', [$ownerAddress, $spenderAddress])[0]->toString(), $this->decimals());
    }



    //XRC20_WRITE OPERATIONS
    //1// TransferXDC Method for Transfer XDC
    /**
     * @param string $from
     * @param string $to
     * @param float $amount
     * @return Transaction\Transaction
     */
    public function transferxdc($from,$to,float $amount){
        $amount   = Number::scaleUp($amount, $this->decimals());
        $amount = Number::toHex($amount);
        $data     = $this->buildTransferData($to, $amount);
        $nonce    = Number::toHex($this->getEth()->getTransactionCount($from, 'pending'));
        $gasLimit = $this->getGasLimit('transferxdc');
        $gasPrice = $this->getSafeGasPrice();
        return (new TransactionBuilder())
            ->setEth($this->getEth())
            ->to($to)
            ->nonce($nonce)
            ->gasPrice($gasPrice)
            ->gasLimit($gasLimit)
            ->data($data)
            ->amount($amount)
            ->build();
    }


    //2// Transfer Method For TransferToken
    /**
     * @param string $from
     * @param string $to
     * @param float $amount
     * @return Transaction\Transaction
     */
    public function transfer(string $from, string $to, float $amount){
        $amount   = Number::scaleUp($amount, $this->decimals());
        $data     = $this->buildTransferData($to, $amount);
        $nonce    = Number::toHex($this->getEth()
                                       ->getTransactionCount($from, 'pending'));
        $gasLimit = $this->getGasLimit('transfer');
        $gasPrice = $this->getSafeGasPrice();

        return (new TransactionBuilder())
            ->setEth($this->getEth())
            ->to($this->contractAddress)
            ->nonce($nonce)
            ->gasPrice($gasPrice)
            ->gasLimit($gasLimit)
            ->data($data)
            ->amount(0)
            ->build()
            ;
    }
    public function buildTransferData(string $to, $amount){
        return $this->getContract()
                    ->at($this->contractAddress)
                    ->getData('transfer', $to, $amount)
            ;
    }
    

    //3// Approve Method For Gave Permission To Others To Use Our Token Balance
    public function approve(string $ownerAddress, string $spenderAddress, string $amount){
        $amount   = Number::scaleUp($amount, $this->decimals());
        $data     = $this->buildApproveData($spenderAddress, $amount);
        $nonce    = Number::toHex($this->getEth()
                                       ->getTransactionCount($ownerAddress, 'pending'));
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
            ->build()
            ;
    }
    public function buildApproveData(string $to, $amount){
        return $this->getContract()
                    ->at($this->contractAddress)
                    ->getData('approve', $to, $amount)
            ;
    }

    //4// Increased Allowance Method To Increase Already Approved Allowance
    public function increaseAllowance(string $ownerAddress, string $spenderAddress, string $amount, string $gasLimit = 'default', string $gasPrice = 'default'){
        $original_allowance = $this->allowance($ownerAddress,$spenderAddress);
        $amount = Number::scaleUp($amount + $original_allowance , $this->decimals());
        $data = $this->buildApproveData($spenderAddress, $amount);
        $nonce = Number::toHex($this->getEth()
        ->getTransactionCount($ownerAddress, 'pending'));
        if (strtolower($gasLimit) === 'default')
        {
        $gasLimit = $this->getGasLimit('approve');
        }
        if (strtolower($gasPrice) === 'default')
        {
        $gasPrice = $this->getSafeGasPrice();
        } return (new TransactionBuilder())
        ->setEth($this->getEth())
        ->to($this->contractAddress)
        ->nonce($nonce)
        ->gasPrice($gasPrice)
        ->gasLimit($gasLimit)
        ->data($data)
        ->amount(0)
        ->build()
        ;
    }


    //5// Decrease Allowance Method To Decrease Already Approved Allowance
    public function decreaseAllowance(string $ownerAddress, string $spenderAddress, string $amount, string $gasLimit = 'default', string $gasPrice = 'default'){
        $original_allowance = $this->allowance($ownerAddress,$spenderAddress);
        $amount = Number::scaleUp( $original_allowance - $amount, $this->decimals());
        // $am = $this->allowance($spenderAddress, $ownerAddress, $this->decimals())+ $amount;
        $data = $this->buildApproveData($spenderAddress, $amount);
        $nonce = Number::toHex($this->getEth()
        ->getTransactionCount($ownerAddress, 'pending'));
        if (strtolower($gasLimit) === 'default')
        {
        $gasLimit = $this->getGasLimit('approve');
        }
        if (strtolower($gasPrice) === 'default')
        {
        $gasPrice = $this->getSafeGasPrice();
        } return (new TransactionBuilder())
        ->setEth($this->getEth())
        ->to($this->contractAddress)
        ->nonce($nonce)
        ->gasPrice($gasPrice)
        ->gasLimit($gasLimit)
        ->data($data)
        ->amount(0)
        ->build()
        ;
    }


    //6// TransferFrom Method to Send Token Balance From One Account To Other Account By Using allowance Get By Others
    /**
     * @param string $spender
     * @param string $from
     * @param string $to
     * @param float $amount
     * @return Transaction\Transaction
     */
    public function transferFrom(string $spender, string $from, string $to, float $amount){
        $amount   = Number::scaleUp($amount, $this->decimals());
        $data     = $this->buildTransferFromData($from, $to, $amount);
        $nonce    = Number::toHex($this->getEth()
                                       ->getTransactionCount($spender, 'pending'));
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
            ->build()
            ;
    }
    public function buildTransferFromData(string $from, string $to, $amount){
        return $this->getContract()
                    ->at($this->contractAddress)
                    ->getData('transferFrom', $from, $to, $amount)
            ;
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
