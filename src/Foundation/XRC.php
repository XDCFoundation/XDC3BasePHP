<?php


namespace XDC\PHP\Foundation;

include "Xdc.php";
use kornrunner\Keccak;
use XDC\PHP\Foundation\Xdc;
use Web3\Contract;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;
use Web3\Web3;

abstract class XRC
{
    /**
     * @var Web3
     */
    private $web3;
    /**
     * @var string
     */
    private $contractAddress;
    /**
     * @var string
     */
    private $abi;
    /**
     * @var Contract
     */
    private $contract;
    /**
     * @var Eth
     */
    private $eth;
    /**
     * @var array
     */
    private $topics = [];

    /**
     * ERC20 constructor.
     * @param string $contractAddress
     * @param string $abi
     * @param string $ethClient
     * @param int $timeout
     */
    public function __construct(string $contractAddress, string $abi, string $ethClient, $timeout = 2)
    {
        $web3                  = new Web3(new HttpProvider(new HttpRequestManager($ethClient, $timeout)));
        $this->web3            = $web3;
        $this->abi             = $abi;
        $this->contractAddress = $contractAddress;
        $this->contract        = new Contract($web3->getProvider(), $abi);
        $this->eth             = new Xdc($web3);
        $this->generateTopics();
    }

    /**
     * @param string $method
     * @param array $arguments
     * @return array|null
     */
    public function call(string $method, array $arguments = [])
    {
        $params = [$method];
        foreach ($arguments as $argument)
        {
            $params[] = $argument;
        }
        $result   = null;
        $params[] = function ($err, $response) use (&$result) {
            if ($err)
            {
                throw $err;
            }
            $result = $response;
        };
        call_user_func_array([$this->contract->at($this->contractAddress), 'call'], $params);

        return $result;
    }

    /**
     * @return array
     */
    public function getTopics()
    {
        return $this->topics;
    }

    /**
     * @return Contract
     */
    public function getContract()
    {
        return $this->contract;
    }

    /**
     * @return Eth
     */
    public function getEth()
    {
        return $this->eth;
    }

    /**
     * @return Web3
     */
    public function getWeb3()
    {
        return $this->web3;
    }

    private function generateTopics()
    {
        $events = $this->contract->getEvents();
        foreach ($events as $key => $event)
        {
            $topic              = sprintf("%s(%s)", $key, implode(',', array_column($event['inputs'], 'type')));
            $this->topics[$key] = Keccak::hash($topic, 256);
        }
    }
}
