<?php

namespace CryptoNoisesTezos\Models;

use CryptoCore\Vss\Vss;

class FixerBigmapUpdatesSync extends FixerAbstract
{
    public static function model($className = __CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'fixer_bigmap_updates_sync';
    }

    public function getNextJobToCheck(int $threadId, int $offset): ?array
    {
        $fieldKey = 'items_count_check'.$threadId;

        $sql = "
            SELECT *
            FROM {$this->tableName()}
            WHERE   level <= (SELECT MAX(level) FROM {$this->tableName()}) - :offset
                AND $fieldKey is null
            ORDER BY level ASC 
            LIMIT 1
        ";

        return Vss::dbTezos()->queryRow($sql, ['offset' => $offset]);
    }

    public function fixRecordsCountOnCheck(int $threadId, int $level, int $recordsCount): void
    {
        Vss::dbTezos()->update($this->tableName(),
            [
                'items_count_check'.$threadId => $recordsCount,
            ],
            ['level' => $level]);
    }
}
