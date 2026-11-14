<?php

namespace CryptoNoisesTezos\Entities;

use CryptoNoisesTezos\Core\TezosAddress;
use CryptoNoisesTezos\Models;
use CryptoCore\Vss\VssDbExp;

class TokenAppend
{
    // To add a field, update every @checkpoint:token-append:new-field

    public int      $contractAdid;
    public int      $tokenUnid;

    public ?string  $minterAddress = null;
    public ?string  $creatorAddress = null;

    public ?string  $tokenType = null;

    public ?int     $editionIndex = null;
    public ?int     $editions = null;
    public ?int     $royaltiesFixed_d4 = null; // require convert to decimals=4, ex: 0.25 => 25% => should be 2500

    public ?string  $collectionGroup = null;
    public ?string  $collectionId = null;

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    public static function Dummy(string $contractAddress, string $tokenId): self
    {
        $instance = new self();
        $instance->contractAdid     = TezosAddress::GetOrCreateAdid($contractAddress);
        $instance->tokenUnid        = Token::PackTokenIdToUnid($tokenId);
        return $instance;
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    // @checkpoint:token-append:new-field

    public static function AppendData(Token $token): void
    {
        $dataAppend = Models\TokensAppend::model()->getItem($token->contractAdid, $token->tokenUnid);

        if ($dataAppend == null)
            return;

        $params = [
            'minter_address'    => 'minterAddress',
            'creator_address'   => 'creatorAddress',
            'token_type'        => 'tokenType',
            'edition_index'     => 'editionIndex',
            'editions'          => 'editions',
            'royalties_fixed'   => 'royaltiesFixed_d4',

            'collection_group'  => 'collectionGroup',
            'collection_id'     => 'collectionId',
        ];

        foreach ($params as $keyDb => $keyObject)
        {
            if ($dataAppend[$keyDb] === null)
                continue;

            $token->$keyObject = $dataAppend[$keyDb];
        }
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    // @checkpoint:token-append:new-field

    public function save(): bool
    {
        $data = [];

        if ($this->minterAddress)
        {
            $data['minter_address'] = $this->minterAddress;
            $data['minter_adid']    = TezosAddress::GetOrCreateAdid($this->minterAddress);
        }

        if ($this->creatorAddress)
        {
            $data['creator_address'] = $this->creatorAddress;
            $data['creator_adid']    = TezosAddress::GetOrCreateAdid($this->creatorAddress);
        }

        $this->packSaveKey($data, 'editions',           'editions');
        $this->packSaveKey($data, 'editionIndex',       'edition_index');
        $this->packSaveKey($data, 'royaltiesFixed_d4',  'royalties_fixed');

        $this->packSaveKey($data, 'collectionGroup',    'collection_group');
        $this->packSaveKey($data, 'collectionId',       'collection_id');

        if (empty($data))
            return false;

        Models\TokensAppend::model()->save($this->contractAdid, $this->tokenUnid, $data);

        Models\Queues\QueueTokensMetadataMerge::model()->push([
            'contract_adid' => $this->contractAdid,
            'token_unid'    => $this->tokenUnid,
            'ts_activate'   => VssDbExp::now(15),
        ]);
        return true;
    }

    private function packSaveKey(array &$data, $keyObject, $keyStorage): void
    {
        $value = $this->$keyObject;

        if ($value === null)
            return;

        if (is_bool($value))
            $data[$keyStorage] = (int) $value;
        else
            $data[$keyStorage] = $value;
    }
}
