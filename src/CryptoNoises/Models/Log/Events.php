<?php

namespace CryptoNoises\Models\Log;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class Events extends VssModel
{
    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'log_events';
    }

    public function fix(string $eventId, ?string $platform, ?int $userId, ?string $message=null)
    {
        return Vss::dbLog()->insert($this->tableName(), [
            'event_id'  => $eventId,
            'platform'  => $platform,
            'user_id'   => $userId,
            'message'   => $message,
        ]);
    }
}
