<?php

namespace CryptoNoisesTezos\Syncs;

/**
 * Syncs one block level from TzKT into the raw log tables.
 */
interface LevelSyncInterface
{
    // Error text for an empty TzKT answer; FixerTezosTransactionsAnalyze retries levels that failed with it
    const kERROR_NoData = 'TzKT returned no data';

    /**
     * TzKT list endpoint that returns this sync's items for a level range, e.g. "operations/transactions".
     */
    public function tzktRangePath(): string;

    /**
     * @param array|null $items Items of this level already loaded by LevelRangeLoader (null = load them from TzKT here)
     * @return int|null         Number of synced items, null on error (see getLastErrorMessage)
     */
    public function sync(int $level, ?array $items = null): ?int;

    public function getLastErrorMessage(): ?string;
}
