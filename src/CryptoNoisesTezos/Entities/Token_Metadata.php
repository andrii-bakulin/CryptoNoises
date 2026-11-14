<?php

namespace CryptoNoisesTezos\Entities;

use CryptoCore\Setapp;
use CryptoNoisesTezos\Analyzers;
use CryptoNoisesTezos\Core\Log;
use CryptoNoisesTezos\Core\TezosAddress;
use CryptoNoisesTezos\Models;

trait Token_Metadata
{
    public static array $TOKEN_FIELDS = [
        // Main                 // Alternatives:
        'name',
        'description',
        'symbol',
        'decimals',

        'shouldPreferSymbol',
        'isBooleanAmount',      'booleanAmount',
        'isTransferable',

        'thumbnailUri',
        'displayUri',
        'artifactUri',
        'editions',
        'minter',

        'rights',
        'version',

        'creators',
        'royalties',
        'formats',
        'attributes',
        'tags',
    ];

    //------------------------------------------------------------------------------------------------------------------

    /** @var TokenAttribute[] */
    public ?array   $attributes = null;

    /** @var TokenCreator[] */
    public ?array   $creators = null;

    /** @var TokenFormat[] */
    public ?array   $formats = null;

    /** @var TokenTag[] */
    public ?array   $tags = null;

    /** @var TokenRoyalty[]  */
    public ?array   $royalties = null;

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    public ?string $artifactMimetype = null;

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    public ?array $jsonUndefinedData = null;

    //------------------------------------------------------------------------------------------------------------------

    /**
     * @param array $json
     * @return bool
     */
    public function analyzeJson(array $json): bool
    {
        $this->name                 = $this->extractString($json, 'name', 1024);
        $this->description          = $this->extractString($json, 'description', 65535);

        $this->symbol               = $this->extractString($json, 'symbol', 64);

        $this->decimals             = $this->extractInt($json, 'decimals');

        $this->shouldPreferSymbol   = $this->extractBool($json, 'shouldPreferSymbol');
        $this->isBooleanAmount      = $this->extractBoolByKeys($json, ['isBooleanAmount', 'booleanAmount']);
        $this->isTransferable       = $this->extractBool($json, 'isTransferable');

        $this->rights               = $this->extractString($json, 'rights', 255);
        $this->version              = $this->extractString($json, 'version', 16);

        $this->thumbnailUri         = $this->extractString($json, 'thumbnailUri', 255);
        $this->displayUri           = $this->extractString($json, 'displayUri', 255);
        $this->artifactUri          = $this->extractString($json, 'artifactUri', 255);

        $this->editions             = $this->extractInt($json, 'editions');

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $this->minterAddress        = $this->extractTezosAddress($json, 'minter');
        $this->creatorAddress       = $this->extractTezosAddress($json, 'creator');

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $this->creators = [];

        if (isset($json['creators']) && is_array($json['creators']))
        {
            foreach ($json['creators'] as $idx => $address)
            {
                if ($address == '')
                {
                    unset($json['creators'][$idx]);
                    continue;
                }

                if (is_array($address))
                {
                    Log::Error('Token_Metadata', 'analyzeJson', "Creators address #[$idx] is array", $json);
                    continue;
                }

                if (!TezosAddress::IsWalletOrContractAddress($address))
                {
                    $address = Models\AddressByCreators::model()->getAddressByCreatorName($address);
                    if ($address == null)
                        continue;
                }

                $tokenCreator = new TokenCreator();
                $tokenCreator->creatorAddress = $address;
                $tokenCreator->creatorAdid    = TezosAddress::GetOrCreateAdid($address);
                $this->creators[] = $tokenCreator;

                unset($json['creators'][$idx]);
            }

            if (empty($json['creators']))
                unset($json['creators']);
        }

        if ($this->creatorAddress == null && $this->creators && count($this->creators) == 1)
        {
            $this->creatorAddress = $this->creators[0]->creatorAddress;
        }

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $this->royalties = [];

        if (isset($json['royalties']) && is_array($json['royalties']) && is_array($json['royalties']['shares']))
        {
            $decimals = $json['royalties']['decimals'];

            if ($decimals !== null)
            {
                foreach ($json['royalties']['shares'] as $address => $shares)
                {
                    if (!TezosAddress::IsWalletOrContractAddress($address) || !is_integer($shares))
                        continue;

                    $tokenRoyalty = new TokenRoyalty();
                    $tokenRoyalty->royaltyAddress = $address;
                    $tokenRoyalty->royaltyAdid    = TezosAddress::GetOrCreateAdid($address);
                    $tokenRoyalty->shares         = $shares;
                    $tokenRoyalty->decimals       = $decimals;
                    $this->royalties[] = $tokenRoyalty;
                }
                unset($json['royalties']['shares']);
                unset($json['royalties']['decimals']);

                if (empty($json['royalties']))
                    unset($json['royalties']);
            }
        }

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $this->formats = [];

        if (isset($json['formats']) && is_array($json['formats']))
        {
            foreach ($json['formats'] as $idx => $format)
            {
                if (!is_array($format) || !isset($format['uri']))
                    continue;

                $uri = $this->extractString($format, 'uri', 255);

                if (empty($uri))
                    continue;

                $tokenFormat = new TokenFormat();
                $tokenFormat->uri       = $uri;
                $tokenFormat->fileName  = $this->extractString($format, 'fileName', 255);
                $tokenFormat->mimetype  = $this->extractString($format, 'mimeType', 64);
                $tokenFormat->fileSize  = $this->extractInt($format, 'fileSize');
                $tokenFormat->duration  = $this->extractString($format, 'duration', 32);

                if (isset($format['dimensions']) && is_array($format['dimensions']))
                {
                    $tokenFormat->dimensionValue = $this->extractString($format['dimensions'], 'value', 32);
                    $tokenFormat->dimensionUnit  = $this->extractString($format['dimensions'], 'unit', 32);
                }

                if (isset($format['dataRate']) && is_array($format['dataRate']))
                {
                    $tokenFormat->dataRateValue = $this->extractString($format['dataRate'], 'value', 32);
                    $tokenFormat->dataRateUnit  = $this->extractString($format['dataRate'], 'unit', 32);
                }

                $this->formats[] = $tokenFormat;

                unset($json['formats'][$idx]);
            }

            if (empty($json['formats']))
                unset($json['formats']);
        }

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
        // Detect artifact mimetype

        if ($this->contractAddress == Setapp\FxHash_v2::IssuerKTID ||
            $this->contractAddress == Setapp\FxHash_v1::IssuerKTID ||
            $this->contractAddress == Setapp\FxHash_v0::IssuerKTID_v2 ||
            $this->contractAddress == Setapp\FxHash_v0::IssuerKTID_v1)
        {
            $this->artifactMimetype = Models\Mimetypes::kTYPE_fxhash_fxgen;
        }
        else if ($this->contractAddress == Setapp\FxHash_v2::TokensKTID ||
                 $this->contractAddress == Setapp\FxHash_v1::TokensKTID ||
                 $this->contractAddress == Setapp\FxHash_v0::TokensKTID)
        {
            $this->artifactMimetype = Models\Mimetypes::kTYPE_fxhash_gentk;
        }
        else
        {
            $artifactUri = $this->artifactUri ?: $this->displayUri ?: null;

            if ($artifactUri)
            {
                $this->artifactMimetype = Models\Mimetypes::kTYPE_undefined;

                if (strpos($artifactUri, Analyzers\Tokens\EightBidou::kCRYPTONOISES_MARKER_PREVIEW) === 0)
                {
                    // the artifact is on-chain pixel art (rendered as PNG); only the generated preview file is JPEG
                    $this->artifactMimetype = Models\Mimetypes::kTYPE_image_png;
                }
                else foreach ($this->formats as $tokenFormat)
                {
                    if ($tokenFormat->uri == $artifactUri)
                    {
                        $this->artifactMimetype = $tokenFormat->mimetype ?: null;
                        break;
                    }
                }
            }
            else
            {
                $this->artifactMimetype = Models\Mimetypes::kTYPE_empty;
            }

        }

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $this->attributes = [];

        if (isset($json['attributes']) && is_array($json['attributes']))
        {
            foreach ($json['attributes'] as $idx => $attribute)
            {
                if (!is_array($attribute))
                    continue;

                $tokenAttribute = new TokenAttribute();
                $tokenAttribute->name  = $this->extractStringByKeys($attribute, ['name', 'trait_type'], 64);
                $tokenAttribute->value = $this->extractStringByKeys($attribute, ['value'], 255);
                $tokenAttribute->type  = $this->extractStringByKeys($attribute, ['type', 'display_type'], 32);
                $this->attributes[] = $tokenAttribute;

                unset($json['attributes'][$idx]);
            }

            if (empty($json['attributes']))
                unset($json['attributes']);
        }

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $this->tags = [];

        if (isset($json['tags']) && $this->analyzeTags($json['tags']))
        {
            unset($json['tags']);
        }
        else if(isset($json['keywords']) && $this->analyzeTags($json['keywords']))
        {
            unset($json['keywords']);
        }

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $this->jsonUndefinedData = $json;

        return true;
    }

    //------------------------------------------------------------------------------------------------------------------

    /**
     * @param mixed $tags
     * @return bool
     */
    private function analyzeTags($tags): bool
    {
        if(is_string($tags))
        {
            $tagsDecode = json_decode($tags, true);

            if (json_last_error() === JSON_ERROR_NONE)
                $tags = $tagsDecode;
            else
                $tags = explode(',', $tags);
        }

        if (!is_array($tags))
            return false;

        foreach ($tags as $tag)
        {
            if (is_array($tag))
                continue;

            $tagId = Models\TokenTags::model()->getOrCreateTagIdByTag($tag);

            if ($tagId === null)
                continue;

            $tokenTag = new TokenTag();
            $tokenTag->tagId    = $tagId;
            $tokenTag->tagValue = $tag;
            $this->tags[] = $tokenTag;
        }
        return true;
    }

    //------------------------------------------------------------------------------------------------------------------

    private function extractString(array &$json, string $key, ?int $maxLength = null): ?string
    {
        if (!isset($json[$key]))
            return null;

        $value = (string) $json[$key];

        if ($maxLength > 0 && mb_strlen($value) > $maxLength)
            return null;

        unset($json[$key]);
        return $value;
    }

    private function extractStringByKeys(array &$json, array $keys, ?int $maxLength = null): ?string
    {
        foreach ($keys as $key)
        {
            if (($value = $this->extractString($json, $key, $maxLength)) !== null)
                return $value;
        }

        return null;
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    private function extractTezosAddress(array &$json, string $key): ?string
    {
        if (!isset($json[$key]))
            return null;

        $value = (string) $json[$key];

        if (!TezosAddress::IsWalletOrContractAddress($value))
            return null;

        unset($json[$key]);
        return $value;
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    private function extractInt(array &$json, string $key): ?int
    {
        if (!isset($json[$key]) || !is_numeric($json[$key]))
            return null;

        $value = $json[$key];

        unset($json[$key]);
        return $value;
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    private function extractBool(array &$json, string $key): ?bool
    {
        if (!isset($json[$key]))
            return null;

        $value = $json[$key];

        if (is_bool($value))
        {
            unset($json[$key]);
            return $value;
        }

        if (is_string($value))
        {
            $value = strtolower($value);

            if ($value === 'true')
            {
                unset($json[$key]);
                return true;
            }

            if ($value === 'false')
            {
                unset($json[$key]);
                return false;
            }
        }

        return null;
    }

    private function extractBoolByKeys(array &$json, array $keys): ?bool
    {
        foreach ($keys as $key)
        {
            if (($value = $this->extractBool($json, $key)) !== null)
                return $value;
        }

        return null;
    }
}
