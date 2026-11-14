<?php

namespace CryptoNoises\GatewayTezos;

class BigmapGate
{
    public static function GetValueByBigmapAndKey(int $bigmap, ?string $key)
    {
        return \CryptoNoisesTezos\Core\Bigmap::GetValueByBigmapAndKey($bigmap, $key);
    }
}
