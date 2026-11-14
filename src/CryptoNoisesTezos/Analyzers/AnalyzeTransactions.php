<?php

namespace CryptoNoisesTezos\Analyzers;

use CryptoNoisesTezos\Models;
use CryptoCore\Vss\Vss;

class AnalyzeTransactions
{
    public bool $verbose = false;

    public ?string $applyOnlyHunter = null;

    public function analyze(int $level): bool
    {
        /** @var TransactionHunters\AbstractHunter[] $hunters */
        $hunters = [
            'Profiles_Tzkt'         => new TransactionHunters\Profiles_Tzkt(),                                                                     // It should be before other hunters which may break the hunters-queue
            'Profiles_Tzprofiles'   => new TransactionHunters\Profiles_Tzprofiles(),

            'Tokens_FxHash_v2'      => new TransactionHunters\Tokens_FxHash_TokenInstance_v2(),
            'Tokens_FxHash_v1'      => new TransactionHunters\Tokens_FxHash_TokenInstance_v1(),
            'Tokens_Kalamint'       => new TransactionHunters\Tokens_Kalamint(),
            'Tokens_Objkt'          => new TransactionHunters\Tokens_Objkt(),
            'Tokens_Rarible'        => new TransactionHunters\Tokens_Rarible(),
            'Tokens_Teia'           => new TransactionHunters\Tokens_Teia(),
        ];

        $transactions = Models\Log\Transactions::model()->selectByLevel_AppliedOnly($level);

        if (empty($transactions))
            return false;

        $allRecords = Models\Log\Transactions::PrepareAndGroupTransactionsInBlocks($transactions);

        // One DB transaction per level (not per operation group / per hunter): a commit is a disk flush, and a full
        // sync from block 1 analyzes millions of levels
        Vss::dbTezos()->transactionBegin();

        foreach ($allRecords as $records)
        {
            $recInit = TransactionHunters\AbstractHunter::ExtractInitRecord($records);

            if ($recInit == null)
                continue;

            foreach ($hunters as $hunterId => $hunterInstance)
            {
                if ($this->applyOnlyHunter && $this->applyOnlyHunter != $hunterId)
                    continue;

                $hunterInstance->verbose = $this->verbose;

                if ($hunterInstance->analyze($recInit, $records))
                    break;
            }
        }

        //--------------------------------------------------------------------------------------------------------------
        // Finalize

        foreach ($hunters as $hunter)
            $hunter->finalize();

        Vss::dbTezos()->transactionCommit();

        return true;
    }
}
