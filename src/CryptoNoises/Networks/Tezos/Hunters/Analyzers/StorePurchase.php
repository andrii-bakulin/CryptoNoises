<?php

namespace CryptoNoises\Networks\Tezos\Hunters\Analyzers;

use CryptoNoises\Config;
use CryptoNoises\Core\Log;
use CryptoNoises\Networks\Tezos\Hunters\AbstractHunter;
use CryptoNoises\Networks\Tezos\TezosNetwork;

class StorePurchase
{
    public static function Analyze(array $recInit, array $subRecords): void
    {
        if (Config::cfg()->isProduction())
            return;

        if ($recInit['amount'] == 0)
            return;

        if (!TezosNetwork::IsWalletAddressValid($recInit['sender']))
            return;

        if (!TezosNetwork::IsContractAddressValid($recInit['target']))
            return;

        $recTransfer = AbstractHunter::ExtractRecordByEntrypoint($subRecords, 'transfer');

        if ($recTransfer == null)
            return;

        // So
        // 'wallet' send some money(XTZ) to 'contract'
        // and a sub-transaction contains a 'transfer' record
        //
        // This looks like a purchase

        // Contract calls that look like a store purchase (XTZ sent, then a 'transfer') but are not one
        switch ($recInit['target'].'/'.$recInit['entrypoint'])
        {
            case 'KT1XcWbaiVWNTjCb5CGrtJweYwSZ4tDfxLhm/mint':
            case 'KT1R6d6bqeSrkHPoxjsgkKtQEb4LpWS6SDXg/swap_batch':
                return;
        }

        Log::Notice('AnalyzerHunter', 'StorePurchase', "Found possible purchase at #{$recInit['target']} @ {$recInit['entrypoint']}", [
            'id'      => $recInit['id'],
            'level'   => $recInit['level'],
            'counter' => $recInit['counter'],
            'subrecs' => count($subRecords),
        ]);
    }
}
