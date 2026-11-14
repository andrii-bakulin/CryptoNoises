<?php

namespace CryptoNoises\Models;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class OptionsNotifier extends VssModel
{
    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'options_notifier';
    }

    public function getItem(int $userId, int $walletAdid): ?array
    {
        return Vss::db()->selectRow('*', $this->tableName(), [
            'user_id'           => $userId,
            'wallet_adid'       => $walletAdid,
        ]);
    }

    public function save(int $userId, int $walletAdid, array $data): void
    {
        $data['user_id']           = $userId;
        $data['wallet_adid']       = $walletAdid;

        Vss::db()->insertOrUpdate($this->tableName(), $data, $data);
    }
}
