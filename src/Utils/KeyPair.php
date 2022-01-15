<?php


namespace XDC\PHP\Utils;

use Web3p\EthereumUtil\Util;

class KeyPair{
    public static function privateKeyToAddress(string $privateKey): string{
        $util      = new Util();
        $publicKey = $util->privateKeyToPublicKey($privateKey);
        return $util->publicKeyToAddress($publicKey);
    }
}
