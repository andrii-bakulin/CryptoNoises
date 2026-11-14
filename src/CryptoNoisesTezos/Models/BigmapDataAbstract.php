<?php

namespace CryptoNoisesTezos\Models;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;
use CryptoCore\Vss\VssDbExp;

abstract class BigmapDataAbstract extends VssModel
{
    const kSTATE_Active     = 'active';
    const kSTATE_Removed    = 'removed';

    abstract public function tableName(): string;

    public function getItemByPk(array $primaryKey): ?array
    {
        return Vss::dbTezos()->selectRow('*', $this->tableName(), $primaryKey);
    }

    public function addOrUpdateByPk(array $primaryKey, int $level, array $saveData)
    {
        $dataInsert                 = array_merge($primaryKey, $saveData);
        $dataInsert['state']        = self::kSTATE_Active;
        $dataInsert['first_level']  = $level;
        $dataInsert['last_level']   = $level;
        $dataInsert['updates']      = 1;

        $dataUpdate                 = $saveData;
        $dataUpdate['state']        = self::kSTATE_Active;
        $dataUpdate['last_level']   = $level;
        $dataUpdate['updates']      = VssDbExp::query('updates+1');

        Vss::dbTezos()->insertOrUpdate($this->tableName(), $dataInsert, $dataUpdate);
    }

    public function removeByPk(array $primaryKey, int $level, array $saveData = []): void
    {
        $dataUpdate                 = $saveData;
        $dataUpdate['state']        = self::kSTATE_Removed;
        $dataUpdate['last_level']   = $level;
        $dataUpdate['updates']      = VssDbExp::query('updates+1');

        Vss::dbTezos()->update($this->tableName(), $dataUpdate, $primaryKey);
    }
}
