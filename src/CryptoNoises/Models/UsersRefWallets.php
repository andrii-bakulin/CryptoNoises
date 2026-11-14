<?php

namespace CryptoNoises\Models;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class UsersRefWallets extends VssModel
{
    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'users_ref_wallets';
    }

    public function getWalletAdidsByUserId(int $userId): array
    {
        $sql = "
            SELECT wallet_adid
            FROM {$this->tableName()} 
            WHERE user_id = :user_id
            ORDER BY ts_created
        ";

        return Vss::db()->queryColumn($sql, [
            'user_id' => $userId,
        ]);
    }

    public function getUserIdsByWalletAdid(int $walletAdid): array
    {
        $sql = "
            SELECT user_id
            FROM {$this->tableName()} 
            WHERE wallet_adid = :wallet_adid
            ORDER BY ts_created
        ";

        return Vss::db()->queryColumn($sql, [
            'wallet_adid' => $walletAdid,
        ]);
    }

    //------------------------------------------------------------------------------------------------------------------

    public function getRefsCountByWalletAdid(int $walletAdid): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->tableName()} WHERE wallet_adid = :wallet_adid";

        return Vss::db()->queryScalar($sql, ['wallet_adid'=>$walletAdid]);
    }

    public function insertRef(int $userId, int $walletAdid): void
    {
        Vss::db()->insert($this->tableName(), [
            'user_id'     => $userId,
            'wallet_adid' => $walletAdid,
        ], 'IGNORE');
    }

    public function getRefItem(int $userId, int $walletAdid): ?array
    {
        $sql = "SELECT * FROM {$this->tableName()} WHERE user_id = :user_id AND wallet_adid = :wallet_adid";

        return Vss::db()->queryRow($sql, [
            'user_id'     => $userId,
            'wallet_adid' => $walletAdid,
        ]);
    }

    public function getWalletsCount(int $userId): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->tableName()} WHERE user_id = :user_id";

        return (int) Vss::db()->queryScalar($sql, ['user_id' => $userId]);
    }

    public function updateValues(int $userId, int $walletAdid, array $data): void
    {
        Vss::db()->update($this->tableName(), $data, [
            'user_id'     => $userId,
            'wallet_adid' => $walletAdid,
        ]);
    }

    public function deleteRef(int $userId, int $walletAdid): void
    {
        Vss::db()->delete($this->tableName(), [
            'user_id'     => $userId,
            'wallet_adid' => $walletAdid,
        ]);
    }
}
