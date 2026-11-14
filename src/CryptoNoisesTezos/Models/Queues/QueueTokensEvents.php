<?php

namespace CryptoNoisesTezos\Models\Queues;

use CryptoCore\Vss\Vss;

class QueueTokensEvents extends QueueAbstract
{
    const kScope_API                            = 'api';
    const kScope_DEV                            = 'dev';

    const kEventType_Mint                       = 'mint';
    const kEventType_Update                     = 'update';

    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'queue_tokens_events';
    }

    public function pop($scope, $eventType): ?array
    {
        $sql = "
            SELECT *
            FROM {$this->tableName()}
            WHERE scope = :scope AND event_type = :event_type
            ORDER BY ts_activate, queue_task_id 
            LIMIT 1
        ";

        $item = Vss::dbTezos()->queryRow($sql, [
            'scope'      => $scope,
            'event_type' => $eventType,
        ]);

        if ($item === null)
            return null;

        $this->moveTaskToExtendedTable($item['queue_task_id'], '__done');

        return $item;
    }
}
