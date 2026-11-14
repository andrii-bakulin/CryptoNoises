<?php

namespace CryptoNoises\Networks\Tezos\Hunters\Analyzers;

use CryptoNoises\Config;
use CryptoNoises\Core\Log;
use Exception;

class UntrackedTransfer
{
    public static function Analyze(array $recInit, array $subRecords): void
    {
        if (Config::cfg()->isProduction())
            return;

        try
        {
            if ($recInit['entrypoint'] == 'transfer')
            {
                if (self::IsEmptyTransfer($recInit))
                    return;

                throw new Exception("Untracked 'transfer' entrypoint event in recInit for contract #{$recInit['target']}");
            }

            foreach ($subRecords as $record)
            {
                if ($record['entrypoint'] == 'transfer')
                {
                    if (self::IsEmptyTransfer($record))
                        continue;

                    throw new Exception("Untracked 'transfer' entrypoint event in subRecord for contract #{$recInit['target']}");
                }
            }
        }
        catch (Exception $e)
        {
            Log::Notice('AnalyzerHunter', 'UntrackedTransfer', $e->getMessage(), [
                'id'      => $recInit['id'],
                'level'   => $recInit['level'],
                'counter' => $recInit['counter'],
                'subrecs' => count($subRecords),
            ]);
        }
    }

    private static function IsEmptyTransfer(array $record): bool
    {
        // {"entrypoint":"transfer","value":[{"txs":[],"from_":"<address>"}]}

        if (is_array($record['parameter']['value']) &&
            count($record['parameter']['value']) == 1 &&
            isset($record['parameter']['value'][0]['txs']) &&
            empty($record['parameter']['value'][0]['txs']))
            return true; // Sometimes an empty transfer is sent; ignore it

        return false;
    }
}
