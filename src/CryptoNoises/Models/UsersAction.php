<?php

namespace CryptoNoises\Models;

use CryptoCore\Time;
use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class UsersAction extends VssModel
{
    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'users_action';
    }

    public function getAction($userId)
    {
        $sql = "
            SELECT *
            FROM {$this->tableName()}
            WHERE user_id     = :user_id
        ";

        $item = Vss::db()->queryRow($sql, ['user_id' => $userId]);

        if ($item == null)
            return null;

        $item['data'] = json_decode($item['data'], true);
        return $item;
    }

    public function setAction($userId, $actionId, $data, $ttl)
    {
        $item = [
            'user_id'    => $userId,
            'action_id'  => $actionId,
            'ts_expired' => Time::Now(+$ttl),
            'data'       => json_encode($data),
        ];
        return Vss::db()->insertOrUpdate($this->tableName(), $item, $item);
    }

    public function cleanUpByUserId($userId)
    {
        return Vss::db()->query("DELETE FROM {$this->tableName()} WHERE user_id = :user_id", [
            'user_id'=>$userId
        ]);
    }

    public function cleanUpExpired()
    {
        return Vss::db()->query("DELETE FROM {$this->tableName()} WHERE ts_expired <= NOW()");
    }
}
