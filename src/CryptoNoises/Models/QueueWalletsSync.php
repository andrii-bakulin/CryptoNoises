<?php

namespace CryptoNoises\Models;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;
use CryptoCore\Vss\VssDbExp;

class QueueWalletsSync extends VssModel
{
    const kPRIORITY_High                        = 1;
    const kPRIORITY_Normal                      = 3;

    const kPRIORITY_ANY                         = 99;

    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'queue_wallets_sync';
    }

    public function popNextTaskToSync()
    {
        $sql = "
            SELECT *
            FROM {$this->tableName()}
            ORDER BY priority ASC, ts_created ASC
            LIMIT 1
        ";

        $item = Vss::db()->queryRow($sql);

        if ($item)
            $this->pop($item['wallet_address']);

        return $item;
    }

    public function isExists(string $address, int $upToPriority): bool
    {
        $sql = "
            SELECT COUNT(*) 
            FROM {$this->tableName()} 
            WHERE wallet_address = :wallet_address
                AND priority <= :up_to_priority
        ";

        $rules = [
            'wallet_address' => $address,
            'up_to_priority' => $upToPriority,
        ];

        return Vss::db()->queryScalar($sql, $rules) > 0;
    }

    public function pop($address)
    {
        $sql = "
            DELETE FROM {$this->tableName()}
            WHERE wallet_address = :wallet_address
        ";
        return Vss::db()->query($sql, ['wallet_address'=>$address]);
    }

    public function push(int $adid, string $address, int $priority): void
    {
        $insert = [
            'wallet_adid'    => $adid,
            'wallet_address' => $address,
            'priority'       => $priority,
        ];

        $update = [
            'priority'       => VssDbExp::query("LEAST(priority, $priority)")
        ];

        Vss::db()->insertOrUpdate($this->tableName(), $insert, $update);
    }

    public function generateTasksForOutdatedWalletsByUserId(int $userId): void
    {
        $sql = "
            INSERT IGNORE INTO ".$this->tableName()." (wallet_adid, wallet_address, priority)
            SELECT w.wallet_adid, w.wallet_address, :priority_high
              FROM ".Wallets::model()->tableName()." AS w
              JOIN ".UsersRefWallets::model()->tableName()." AS ref
                ON w.wallet_adid = ref.wallet_adid
               AND ref.user_id = :user_id
             WHERE w.status = 'active'
               AND w.ts_updated < NOW() - INTERVAL 1 HOUR
        ";

        Vss::db()->query($sql, ['user_id'=>$userId, 'priority_high' => self::kPRIORITY_High]);
    }
}
