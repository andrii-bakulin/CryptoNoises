<?php

namespace CryptoNoises\Models;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class CallbackActions extends VssModel
{
    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'callback_actions';
    }

    public function get(string $callbackAuid): ?array
    {
        $item = Vss::db()->queryRow(
            "SELECT * FROM {$this->tableName()} WHERE callback_auid = :callback_auid",
            ['callback_auid' => $callbackAuid]);

        if ($item === null)
            return null;

        return [$item['scope'], $item['event'], json_decode($item['data'], true)];
    }

    public function set(string $callbackAuid, string $scope, string $event, array $data): void
    {
        $item = [
            'callback_auid' => $callbackAuid,
            'scope'         => $scope,
            'event'         => $event,
            'data'          => json_encode($data),
        ];

        Vss::db()->insert($this->tableName(), $item);
    }

    public function cleanUpExpired()
    {
        return Vss::db()->query("DELETE FROM {$this->tableName()} WHERE ts_created <= NOW() - INTERVAL 7 DAY");
    }
}
