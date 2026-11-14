<?php

namespace CryptoNoisesTezos\Syncs;

use CryptoNoisesTezos\Vendors;

/**
 * Loads TzKT items (transactions, bigmap updates) of a block range with as few requests as possible.
 *
 * Items come sorted by id, i.e. by level. When a page is full, the last level of the page may be cut by the limit:
 * it is dropped and the range ends right before it (the caller loads it next time). When one level alone does not
 * fit into a page, its items are paged by id.
 */
final class LevelRangeLoader
{
    const kPAGE_LIMIT = 10000;

    /** @var array<int, array> level => items, only levels that have items */
    public array $itemsByLevel = [];

    /** Last level whose items are fully loaded (<= the requested levelTo) */
    public int $levelTo;

    public int $itemsCount = 0;

    public static function Load(string $tzktPath, int $levelFrom, int $levelTo): ?self
    {
        $range = new self();
        $idAfter = null;

        while (true)
        {
            $page = Vendors\Tzkt::GetLevelRangePage($tzktPath, $levelFrom, $levelTo, self::kPAGE_LIMIT, $idAfter);

            if ($page === null)
                return null;

            foreach ($page as $item)
                $range->itemsByLevel[(int) $item['level']][] = $item;

            if (count($page) < self::kPAGE_LIMIT)
            {
                $range->levelTo = $levelTo;
                break;
            }

            $last = end($page);
            $idAfter = $last['id'];

            if ($last['level'] > $levelFrom)
            {
                // The last level may be incomplete: keep only the levels before it
                unset($range->itemsByLevel[(int) $last['level']]);
                $range->levelTo = $last['level'] - 1;
                break;
            }

            // A single level has more items than one page: continue paging inside it
            $levelTo = $levelFrom;
        }

        foreach ($range->itemsByLevel as $items)
            $range->itemsCount += count($items);

        return $range;
    }
}
