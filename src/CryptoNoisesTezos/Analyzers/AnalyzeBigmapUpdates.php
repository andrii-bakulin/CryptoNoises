<?php

namespace CryptoNoisesTezos\Analyzers;

use CryptoNoisesTezos\Analyzers\BigmapUpdateEntities;
use CryptoNoisesTezos\Core\Bigmap;
use CryptoNoisesTezos\Models;
use CryptoNoisesTezos\Vendors\Tzkt;
use CryptoCore\Vss\Vss;
use Exception;

class AnalyzeBigmapUpdates
{
    private ?string $lastErrorMessage = null;

    public function analyze(int $level): ?int
    {
        Vss::dbTezos()->transactionBegin();
        Vss::dbTezosLog()->transactionBegin();

        try
        {
            $items = Models\Log\BigmapUpdates::model()->selectByLevel($level);

            foreach ($items as $item)
            {
                switch ($item['action'])
                {
                    case Models\Log\BigmapUpdates::kACTION_Allocate:
                        $this->allocate($item);
                        break;

                    case Models\Log\BigmapUpdates::kACTION_Remove:
                        $this->remove($item);
                        break;

                    case Models\Log\BigmapUpdates::kACTION_AddKey:
                    case Models\Log\BigmapUpdates::kACTION_UpdateKey:
                    case Models\Log\BigmapUpdates::kACTION_RemoveKey:
                        $this->processKey($item['action'], $item);
                        break;

                    default:
                        throw new Exception('Unknown action ['.$item['action'].']');
                }
            }

            Vss::dbTezos()->transactionCommit();
            Vss::dbTezosLog()->transactionCommit();

            return count($items);
        }
        catch (Exception $e)
        {
            Vss::dbTezos()->transactionRollback();
            Vss::dbTezosLog()->transactionRollback();

            $this->lastErrorMessage = $e->getMessage();
            return null;
        }
    }

    public function getLastErrorMessage(): ?string
    {
        return $this->lastErrorMessage;
    }

    //------------------------------------------------------------------------------------------------------------------

    /**
     * @param array $item
     * @return void
     * @throws Exception
     */
    private function allocate(array $item): void
    {
        $bigmap = $item['bigmap'];

        $bigmapItem = Models\Bigmaps::model()->getItem($bigmap);

        if ($bigmapItem !== null)
            return;

        $bigmapData = Tzkt::GetBigmapDetails($bigmap);

        if ($bigmapData === null)
            throw new Exception('Cannot get bigmap data from tzkt');

        $item = [
            'bigmap'            => $bigmap,
            'contract'          => $bigmapData['contract']['address'],
            'alias'             => $bigmapData['contract']['alias'],
            'path'              => $bigmapData['path'],
            'state'             => Models\Bigmaps::kSTATE_Active,
            'first_level'       => $item['level'],
            'tags'              => $bigmapData['tags'] ? json_encode($bigmapData['tags']) : null,
            'key_type'          => json_encode($bigmapData['keyType']),
            'value_type'        => json_encode($bigmapData['valueType']),
        ];

        //--------------------------------------------------------------------------------------------------------------

        if ($item['key_type']   == '{"schema:nat":"nat"}' &&
            $item['value_type'] == '{"schema:address":"address"}')
        {
            $item['type'] = Models\Bigmaps::kTYPE_LedgerLight;
        }
        else if($item['path']   == 'metadata' &&
            $item['key_type']   == '{"schema:string":"string"}' &&
            $item['value_type'] == '{"schema:bytes":"bytes"}')
        {
            $item['type'] = Models\Bigmaps::kTYPE_Metadata;
        }
        else if(
            ($item['path'] == 'token_metadata'        && $item['key_type'] == '{"schema:nat":"nat"}') ||
            ($item['path'] == 'assets.token_metadata' && $item['key_type'] == '{"schema:nat":"nat"}'))
        {
            $item['type'] = Models\Bigmaps::kTYPE_TokenMetadata;
        }
        else if(
            strpos($item['path'], 'supply') !== false &&
            ($item['key_type']   == '{"schema:nat":"nat"}' || $item['key_type']   == '{"schema:int":"int"}') &&
            ($item['value_type'] == '{"schema:nat":"nat"}' || $item['value_type'] == '{"schema:int":"int"}'))
        {
            $item['type'] = Models\Bigmaps::kTYPE_Supply;
        }
        else switch ($item['key_type'])
        {
            case '{"schema:object":{"address:address":"address","nat:nat":"nat"}}':
            case '{"schema:object":{"address:address":"address","nat:?nat":"nat"}}':
            case '{"schema:object":{"nat:nat":"nat","address:address":"address"}}':
                $item['type']               = Models\Bigmaps::kTYPE_Ledger;
                $item['fieldkey_token_id']  = 'nat';
                $item['fieldkey_address']   = 'address';
                break;

            case '{"schema:object":{"owner:address":"address","token_id:nat":"nat"}}':
            case '{"schema:object":{"token_id:nat":"nat","owner:address":"address"}}':
                $item['type']               = Models\Bigmaps::kTYPE_Ledger;
                $item['fieldkey_token_id']  = 'token_id';
                $item['fieldkey_address']   = 'owner';
                break;

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            case '{"schema:object":{"owner:address":"address","operator:address":"address","token_id:nat":"nat"}}':
            case '{"schema:object":{"token_id:nat":"nat","owner:address":"address","operator:address":"address"}}':
                $item['type']               = Models\Bigmaps::kTYPE_Operators;
                $item['fieldkey_token_id']  = 'token_id';
                $item['fieldkey_address']   = 'operator';
                $item['fieldkey_address2']  = 'owner';
                break;

            case '{"schema:object":{"address_0:address":"address","address_1:address":"address","nat:nat":"nat"}}':
            case '{"schema:object":{"address_0:address":"address","nat:nat":"nat","address_1:address":"address"}}':
            case '{"schema:object":{"nat:nat":"nat","address_0:address":"address","address_1:address":"address"}}':
                $item['type']               = Models\Bigmaps::kTYPE_Operators;
                $item['fieldkey_token_id']  = 'nat';
                $item['fieldkey_address']   = 'address_0';
                $item['fieldkey_address2']  = 'address_1';
                break;

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            default:
                $item['type']               = Models\Bigmaps::kTYPE_Basic;
                break;
        }

        //--------------------------------------------------------------------------------------------------------------

        Models\Bigmaps::model()->insert($item);
    }

    private function remove(array $item): void
    {
        $bigmap = $item['bigmap'];
        $level  = $item['level'];

        $entity = new BigmapUpdateEntities\Basic();
        $entity->init($bigmap, $level);
        $entity->removeAllInBigmap();

        $entity = new BigmapUpdateEntities\Ledger('*','*');
        $entity->init($bigmap, $level);
        $entity->removeAllInBigmap();

        $entity = new BigmapUpdateEntities\LedgerLight();
        $entity->init($bigmap, $level);
        $entity->removeAllInBigmap();

        $entity = new BigmapUpdateEntities\MarketDeals();
        $entity->init($bigmap, $level);
        $entity->removeAllInBigmap();

        $entity = new BigmapUpdateEntities\MarketOffers();
        $entity->init($bigmap, $level);
        $entity->removeAllInBigmap();

        $entity = new BigmapUpdateEntities\Metadata();
        $entity->init($bigmap, $level);
        $entity->removeAllInBigmap();

        $entity = new BigmapUpdateEntities\Operator('*','*','*');
        $entity->init($bigmap, $level);
        $entity->removeAllInBigmap();

        $entity = new BigmapUpdateEntities\TokenMetadata();
        $entity->init($bigmap, $level);
        $entity->removeAllInBigmap();

        $entity = new BigmapUpdateEntities\Supply();
        $entity->init($bigmap, $level);
        $entity->removeAllInBigmap();

        Models\Bigmaps::model()->remove($bigmap, $level);
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    /**
     * @throws Exception
     */
    private function processKey(string $action, array $item): void
    {
        $bigmap = $item['bigmap'];

        $bigmapItem = Models\Bigmaps::model()->getItem($bigmap);

        if ($bigmapItem === null)
            throw new Exception("Bigmap record #$bigmap does not exist");

        if ($bigmapItem['state'] == Models\Bigmaps::kSTATE_Removed)
        {

            // Allowed: a re-scan may start from a mid state
            Models\Bigmaps::model()->update($bigmap, ['state' => Models\Bigmaps::kSTATE_Active]);
        }

        $level      = $item['level'];
        $key        = json_decode($item['content_key'], true);
        $cValString = $item['content_value'];

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $entity = Bigmap::FactoryEntity($bigmapItem);

        if ($entity === null)
            throw new Exception("Unknown bigmap type [{$bigmapItem['type']}]");

        $entity->init($bigmap, $level);

        switch ($action)
        {
            case Models\Log\BigmapUpdates::kACTION_AddKey:
            case Models\Log\BigmapUpdates::kACTION_UpdateKey:
                $entity->save($key, $cValString);
                break;

            case Models\Log\BigmapUpdates::kACTION_RemoveKey:
                $entity->remove($key);
                break;
        }
    }
}
