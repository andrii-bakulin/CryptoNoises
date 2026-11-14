<?php

namespace CryptoNoisesTezos\Analyzers\BigmapUpdateEntities;

use CryptoNoisesTezos\Core\BigmapKeyCodec;
use CryptoNoisesTezos\Core\Helper;
use CryptoNoisesTezos\Models;

/*******************************************************************************************************************
 * Share of bigmap key layouts, measured once on mainnet (rationale for the entity types below)
 *      Content Key Stamp                               Hits %
 * -----------------------------------------------------------------------------------------------------------------
 *      *                                               53.94%      basic
 *      {"address":*,"nat":*}                           36.58%      ledger
 *      {"operator":*,"owner":*,"token_id":*}            6.24%      operator
 *      {"address_0":*,"address_1":*,"nat":*}            1.54%      operator
 *      {"owner":*,"token_id":*}                         1.19%      ledger
 *
 *      {"owner":*,"spender":*}                          0.17%
 *      {"operator":*,"owner":*}                         0.06%
 *      {"address_0":*,"address_1":*}                    0.06%
 *      {"addr":*,"token_id":*}                          0.04%
 *      {"fa2_address":*,"token_id":*}                   0.03%
 */

abstract class AbstractEntity
{
    protected Models\BigmapDataAbstract $dbDataModel;

    protected int $bigmap;
    protected int $level;

    /** @var PostActions\PostActionInterface[] */
    protected array $postActions = [];

    public function init($bigmap, $level)
    {
        $this->bigmap = $bigmap;
        $this->level = $level;
    }

    public function model(): Models\BigmapDataAbstract
    {
        return $this->dbDataModel;
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    /**
     * @param mixed $key
     * @return array
     */
    abstract public function primaryKey($key): array;

    /**
     * @param string $cValString (string-as-json)
     * @return array
     */
    abstract public function packSavingValues(string $cValString): array;

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    public function save($key, string $cValString): void
    {
        $primaryKey = $this->primaryKey($key);
        $savingValues = $this->packSavingValues($cValString);

        $this->dbDataModel->addOrUpdateByPk($primaryKey, $this->level, $savingValues);

        foreach ($this->postActions as $postAction)
        {
            $postAction->save($this->bigmap, $this->level, $key, $cValString);
        }
    }

    public function remove($key): void
    {
        $primaryKey = $this->primaryKey($key);

        $this->dbDataModel->removeByPk($primaryKey, $this->level);

        foreach ($this->postActions as $postAction)
        {
            $postAction->remove($this->bigmap, $this->level, $key);
        }
    }

    public function removeAllInBigmap(): void
    {
        $this->dbDataModel->removeByPk(['bigmap' => $this->bigmap], $this->level);

        foreach ($this->postActions as $postAction)
        {
            $postAction->removeAllInBigmap($this->bigmap, $this->level);
        }
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    /**
     * @param mixed $keyValue
     */
    public static function KeyToExkey($keyValue): int
    {
        return BigmapKeyCodec::KeyToExkey($keyValue);
    }

    /**
     * @return mixed|null
     */
    public static function ExkeyToKey(int $exkey)
    {
        return BigmapKeyCodec::ExkeyToKey($exkey);
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    protected static function NormalizeXtzPrice(?string $price): ?int
    {
        return Helper::IsNat($price) ? $price : null;
    }
}
