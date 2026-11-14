<?php

namespace CryptoNoisesTezos\Analyzers;

use CryptoNoisesTezos\Models;

class AnalyzeHelper
{
    public static function GetLastFullySyncedBlock(): ?int
    {
        $lastTransactionsSyncedBlock = self::GetLastTransactionsSyncedBlock();
        $lastBigmapsSyncedBlock = self::GetLastBigmapsSyncedBlock();

        if ($lastTransactionsSyncedBlock === null || $lastBigmapsSyncedBlock === null)
            return null;

        return min($lastTransactionsSyncedBlock, $lastBigmapsSyncedBlock);
    }

    //------------------------------------------------------------------------------------------------------------------

    public static function GetLastTransactionsSyncedBlock(): ?int
    {
        return Models\FixerTransactionsSync::model()->getLastSuccessLevelIndex();
    }

    public static function GetLastBigmapsSyncedBlock(): ?int
    {
        return Models\FixerBigmapUpdatesSync::model()->getLastSuccessLevelIndex();
    }

    public static function GetLastBigmapsAnalyzedBlock(): ?int
    {
        return Models\FixerBigmapUpdatesAnalyze::model()->getLastSuccessLevelIndex();
    }
}
