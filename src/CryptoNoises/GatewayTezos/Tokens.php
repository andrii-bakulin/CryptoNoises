<?php

namespace CryptoNoises\GatewayTezos;

use CryptoNoises\Networks\Tezos\TezosNetwork;
use CryptoNoisesTezos\Core;
use CryptoNoisesTezos\Entities;
use Exception;

class Tokens
{
    use Tokens_Extras_FxHash;

    public static function GetTokenDetails(?string $tokenAddress, ?string $tokenId, bool $fillFullData = false): ?\CryptoNoises\Entities\Token
    {
        if ($tokenAddress === null || $tokenAddress === '' || $tokenId === null || $tokenId === '')
            return null;

        $tokenAdid = Core\TezosAddress::GetAdidByAddress($tokenAddress);
        $tokenUnid = Entities\Token::PackTokenIdToUnid($tokenId, false);

        if ($tokenAdid === null || $tokenUnid === null)
            return null;

        return self::BuildTokenEntity($tokenAddress, $tokenAdid, $tokenId, $tokenUnid, $fillFullData);
    }

    public static function GetTokenDetailsByInnerParams(int $tokenAdid, int $tokenUnid, bool $fillFullData = false): ?\CryptoNoises\Entities\Token
    {
        $tokenAddress = Core\TezosAddress::GetAddressByAdid($tokenAdid);
        $tokenId = Entities\Token::UnpackUnidToTokenId($tokenUnid);

        if ($tokenAddress === null || $tokenId === null)
            return null;

        return self::BuildTokenEntity($tokenAddress, $tokenAdid, $tokenId, $tokenUnid, $fillFullData);
    }

    //------------------------------------------------------------------------------------------------------------------

    private static function BuildTokenEntity(string $tokenAddress, int $tokenAdid,
                                             string $tokenId, int $tokenUnid,
                                             bool $fillFullData = false): ?\CryptoNoises\Entities\Token
    {
        $networkId = \CryptoNoises\Config::kNETWORK_Tezos;

        try
        {
            $tokenContract = Entities\Contract::Get($tokenAddress);

            if ($tokenContract === null)
                $tokenContract = Entities\Contract::GetFake($tokenAddress);

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            $crynzToken = new \CryptoNoises\Entities\Token($networkId, $tokenAddress, $tokenId);

            $tezosToken = Entities\Token::GetByAdidAndTokenUnid($tokenAdid, $tokenUnid);

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            if ($tezosToken)
            {
                self::BuildTokenEntity_ExtraFix_FxHash($crynzToken, $tezosToken);

                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

                $crynzToken->name               = $tezosToken->name;

                if ($tezosToken->editionIndex > 0 && $tezosToken->editions > 1)
                    $crynzToken->name .= " ($tezosToken->editionIndex/$tezosToken->editions)";

                $crynzToken->description        = $tezosToken->description;

                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

                $crynzToken->collectionGroup    = $tezosToken->collectionGroup;
                $crynzToken->collectionId       = $tezosToken->collectionId;

                $crynzToken->minterAddress      = self::GetAddressValueIfValid($tezosToken->minterAddress);
                $crynzToken->creatorAddress     = self::GetAddressValueIfValid($tezosToken->creatorAddress);

                $crynzToken->tokenType          = $tezosToken->tokenType;

                $crynzToken->symbol             = $tezosToken->symbol;
                $crynzToken->decimals           = $tezosToken->decimals;

                $crynzToken->editionIndex       = $tezosToken->editionIndex;
                $crynzToken->editions           = $tezosToken->editions;

                $crynzToken->rights             = $tezosToken->rights;

                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                // creatorRoyalty is a percentage with 4 implied decimals

                if ($tezosToken->royaltiesFixed_d4)
                    $crynzToken->creatorRoyalty_d4 = $tezosToken->royaltiesFixed_d4;
                else
                    $crynzToken->creatorRoyalty_d4 = $tezosToken->getRoyalty(4);

                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

                $ipfsMedia = Entities\IpfsMedia::Get($tezosToken->previewUri);

                if ($ipfsMedia !== null)
                {
                    $crynzToken->thumbnailUrl   = $ipfsMedia->hasThumbnail ? $ipfsMedia->getPublicUrl('jpg') : null;
                    $crynzToken->animationUrl   = $ipfsMedia->hasAnimation ? $ipfsMedia->getPublicUrl('gif') : null;
                    $crynzToken->movieClipUrl   = $ipfsMedia->hasMovieClip ? $ipfsMedia->getPublicUrl('mp4') : null;
                }
                else
                {
                    $ipfsMediaTeaser = Entities\IpfsMediaTeaser::Get($tezosToken->previewUri);

                    if ($ipfsMediaTeaser !== null)
                    {
                        $crynzToken->thumbnailUrl   = $ipfsMediaTeaser->hasThumbnail ? $ipfsMediaTeaser->getPublicUrl('jpg') : null;
                        $crynzToken->animationUrl   = $ipfsMediaTeaser->hasAnimation ? $ipfsMediaTeaser->getPublicUrl('gif') : null;
                        $crynzToken->movieClipUrl   = $ipfsMediaTeaser->hasMovieClip ? $ipfsMediaTeaser->getPublicUrl('mp4') : null;
                    }
                }

                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

                if ($fillFullData)
                {
                    $crynzToken->tags               = $tezosToken->getTags();
                    $crynzToken->attributes         = $tezosToken->getAttributes();
                }
            }
            else
            {
                $crynzToken->name               = $tokenContract->getPublicName().' #'.$tokenId;
                $crynzToken->thumbnailUrl       = $tokenContract->getPublicImageUrl();
            }

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            if (empty($crynzToken->collectionName))
                $crynzToken->collectionName = $tokenContract->getPublicName();

            if (empty($crynzToken->creatorAddress))
                $crynzToken->creatorAddress = self::GetAddressValueIfValid($tokenContract->getPublicAuthorAddress());

            $crynzToken->tokenMarketUrl = $tokenContract->getPublicTokenLink($tokenId);

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
        }
        catch (Exception $e)
        {
            $crynzToken = null;
        }

        return $crynzToken;
    }

    private static function GetAddressValueIfValid(?string $address): ?string
    {
        return TezosNetwork::IsWalletAddressValid($address) ? $address : null;
    }
}
