<?php

namespace CryptoNoisesTezos\Entities;

use CryptoNoisesTezos\Core\Helper;
use CryptoNoisesTezos\Core\Log;
use CryptoNoisesTezos\Core\TezosAddress;
use CryptoNoisesTezos\Models;
/**
 * https://tzip.tezosagora.org/proposal/tzip-21/
 */
class Token
{
    use Token_Events;
    use Token_Metadata;
    use Token_Metadata_Saver;
    use Token_Sync;

    //------------------------------------------------------------------------------------------------------------------

    const kTOKEN_TYPE_Domain                    = 'domain';

    //------------------------------------------------------------------------------------------------------------------

    public int      $contractAdid;
    public string   $contractAddress;
    public int      $tokenUnid;
    public string   $tokenId;

    //------------------------------------------------------------------------------------------------------------------

    public ?int     $levelLast = null;

    public ?string  $minterAddress = null;
    public ?string  $creatorAddress = null;

    public ?string  $tokenType = null;

    public ?string  $name = null;
    public ?string  $description = null;
    public ?string  $symbol = null;
    public ?int     $decimals = null;
    public ?bool    $shouldPreferSymbol = null;
    public ?bool    $isBooleanAmount = null;
    public ?bool    $isTransferable = null;
    public ?string  $thumbnailUri = null;
    public ?string  $displayUri = null;
    public ?string  $artifactUri = null;
    public ?string  $rights = null;

    public ?int     $editionIndex = null;
    public ?int     $editions = null;

    public ?int     $royaltiesFixed_d4 = null; // require convert to decimals=4, ex: 0.25 => 25% => should be 2500
    public ?string  $version = null;

    //------------------------------------------------------------------------------------------------------------------
    // Support data

    public ?int    $bigmap = null;
    public ?int    $bigmapExkey = null;

    public ?string $metadataState = null;
    public ?string $metadataUri   = null;

    public ?string $previewState  = null;
    public ?string $previewUri    = null;

    public ?string $collectionGroup = null;
    public ?string $collectionId = null;

    //------------------------------------------------------------------------------------------------------------------

    private function __construct()
    {
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    public static function GetByAdidAndTokenUnid(int $contractAdid, int $tokenUnid): ?self
    {
        $item = Models\Tokens::model()->getItem($contractAdid, $tokenUnid);

        if ($item === null)
            return null;

        $instance = self::DummyByAdidAndTokenUnid($contractAdid, $tokenUnid);

        $instance->minterAddress        = $item['minter_address'];
        $instance->creatorAddress       = $item['creator_address'];

        $instance->tokenType            = $item['token_type'];

        $instance->name                 = $item['name'];
        $instance->description          = $item['description'];
        $instance->symbol               = $item['symbol'];
        $instance->decimals             = $item['decimals'];
        $instance->shouldPreferSymbol   = $item['should_prefer_symbol'];
        $instance->isBooleanAmount      = $item['is_boolean_amount'];
        $instance->isTransferable       = $item['is_transferable'];

        $instance->thumbnailUri         = $item['thumbnail_uri'];
        $instance->displayUri           = $item['display_uri'];
        $instance->artifactUri          = $item['artifact_uri'];

        $instance->editionIndex         = $item['edition_index'];
        $instance->editions             = $item['editions'];
        $instance->royaltiesFixed_d4    = $item['royalties_fixed'];
        $instance->rights               = $item['rights'];
        $instance->version              = $item['version'];

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $instance->bigmap               = $item['bigmap'];
        $instance->bigmapExkey          = $item['bigmap_exkey'];

        $instance->levelLast            = $item['level_last'];

        $instance->metadataState        = $item['metadata_state'];
        $instance->metadataUri          = $item['metadata_uri'];

        $instance->previewState         = $item['preview_state'];
        $instance->previewUri           = $item['preview_uri'];

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $instance->collectionGroup      = $item['collection_group'];
        $instance->collectionId         = $item['collection_id'];

        // tokens_ref_collection holds the same values, no need to read it here

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        TokenAppend::AppendData($instance);

        return $instance;
    }

    public static function GetByAddressAndTokenId(string $contractAddress, string $tokenId): ?self
    {
        $contractAdid = TezosAddress::GetOrCreateAdid($contractAddress);
        $tokenUnid    = self::PackTokenIdToUnid($tokenId);

        return self::GetByAdidAndTokenUnid($contractAdid, $tokenUnid);
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    public static function DummyByAddressAndTokenId(string $contractAddress, string $tokenId): self
    {
        $instance = new self();
        $instance->contractAddress  = $contractAddress;
        $instance->tokenId          = $tokenId;

        $instance->contractAdid     = TezosAddress::GetOrCreateAdid($contractAddress);
        $instance->tokenUnid        = self::PackTokenIdToUnid($tokenId);
        return $instance;
    }

    public static function DummyByAdidAndTokenUnid(int $contractAdid, int $tokenUnid): self
    {
        $instance = new self();
        $instance->contractAdid     = $contractAdid;
        $instance->tokenUnid        = $tokenUnid;

        $instance->contractAddress  = TezosAddress::GetAddressByAdid($contractAdid);
        $instance->tokenId          = self::UnpackUnidToTokenId($tokenUnid);
        return $instance;
    }

    //------------------------------------------------------------------------------------------------------------------

    public static function PackTokenIdToUnid(string $tokenId, $autoCreate=true): ?int
    {
        if (is_numeric($tokenId) && preg_match('/^([0-9]{1,18})$/',$tokenId))
            return $tokenId;

        $positiveUnid = Models\TokenUnids::model()->getTokenUnidByTokenValue($tokenId, $autoCreate);

        if ($positiveUnid === null)
            return null;

        return -$positiveUnid;
    }

    public static function UnpackUnidToTokenId(int $tokenUnid): ?string
    {
        return $tokenUnid >= 0
            ? $tokenUnid
            : Models\TokenUnids::model()->getTokenValueByTokenUnid($tokenUnid);
    }

    //------------------------------------------------------------------------------------------------------------------

    public function update(array $data): bool
    {
        if (empty($data))
            return false;

        return Models\Tokens::model()->update($this->contractAdid, $this->tokenUnid, $data);
    }

    //------------------------------------------------------------------------------------------------------------------

    public function getRoyalty(int $decimals = 2): ?int
    {
        return Models\TokensRoyalties::model()->getRoyalty($this->contractAdid, $this->tokenUnid, $decimals);
    }

    //------------------------------------------------------------------------------------------------------------------

    public function unpackHex2Bin(?string $value): ?string
    {
        if ($value === null || $value === '')
            return $value;

        $result = Helper::UnpackHex2Bin($value);

        if ($result === null)
        {
            Log::Error('Token', 'unpackHex2Bin', 'Cannot unpack hex value (linked with token)', [
                'value'             => $value,
                'contractAddress'   => $this->contractAddress,
                'tokenId'           => $this->tokenId,
            ]);
        }

        return $result;
    }

    //------------------------------------------------------------------------------------------------------------------

    public function getTags(): ?array
    {
        return Models\TokenTags::model()->selectTagsForToken($this->contractAdid, $this->tokenUnid);
    }

    public function getAttributes(): ?array
    {
        $items = Models\TokensAttributes::model()->selectAttributesForToken($this->contractAdid, $this->tokenUnid);

        $result = [];
        foreach ($items as $i)
        {
            $result[$i['name']] = $i['value'];
        }

        return $result;
    }
}
