<?php

namespace CryptoNoises\Localize\Notification;

use CryptoNoises\Entities\Token;
use CryptoNoises\Entities\User;
use CryptoNoises\Helpers\Helper;
use CryptoNoises\Localize\Notification;
use CryptoNoises\Networks\AbstractNetwork;

class AboutToken extends Notification
{
    public ?bool $discord_isAllowEphemeralResponse  = true;

    //------------------------------------------------------------------------------------------------------------------

    public static function I(?User $user): self
    {
        return new self($user);
    }

    public function __construct(?User $user)
    {
        parent::__construct($user);
    }

    public function setToken(Token $tokenEntity): self
    {
        $royaltyPercents = $tokenEntity->creatorRoyalty_d4
            ? Helper::TrimRightZerosInNumber($tokenEntity->creatorRoyalty_d4/100).'%'
            : null;

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $network = AbstractNetwork::GetNetworkById($tokenEntity->networkId);

        if (self::IsTelegram())
        {
            $this->title = ["🌅 <a href=\"".self::SafeText((string) $tokenEntity->tokenMarketUrl)."\">".self::SafeText((string) $tokenEntity->name)."</a>"];
        }
        else if (self::IsDiscord())
        {
            $this->title = ['🌅 '.$tokenEntity->name];
            $this->url   = $tokenEntity->tokenMarketUrl;
        }

        $this->description      = [self::SafeText((string) $tokenEntity->description)];
        $this->description[]    = self::EMPTY_LINE;

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $this->appendInfoAddress('Creator',         $tokenEntity->creatorAddress,   $network);
        $this->appendInfoAddress('Minter',          $tokenEntity->minterAddress,    $network);

        $this->description[]    = self::EMPTY_LINE;

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        if ($tokenEntity->isNftToken())             $this->appendInfoValue('Type', 'NFT');
        else if ($tokenEntity->isCoinToken())       $this->appendInfoValue('Type', 'Coin');
        else if ($tokenEntity->isDomainToken())     $this->appendInfoValue('Type', 'Domain');
        else                                        $this->appendInfoValue('Type', '?');

        $this->appendInfoValue('Symbol',            $tokenEntity->symbol);

        if (!$tokenEntity->isNftToken())
            $this->appendInfoValue('Decimals',      $tokenEntity->decimals);

        $this->appendInfoValue('Royalty',           $royaltyPercents);
        $this->appendInfoValue('Edition Index',     $tokenEntity->editionIndex);
        $this->appendInfoValue('Editions',          $tokenEntity->getCurrentEditions());
        $this->appendInfoValue('Collection',        $tokenEntity->collectionName);

        if ($tokenEntity->collectionId !== null)
            $this->appendInfoValue('Collection ID', $tokenEntity->collectionId);

        $this->appendInfoValue('Rights',            $tokenEntity->rights);

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $this->appendNftTags($tokenEntity);
        $this->appendNftAttributes($tokenEntity);

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $this->setPreviewThumbnailUrl($tokenEntity->thumbnailUrl);
        $this->setPreviewAnimationUrl($tokenEntity->animationUrl);
        $this->setPreviewMovieClipUrl($tokenEntity->movieClipUrl);

        return $this;
    }

    private function appendInfoValue(string $title, ?string $value): void
    {
        if ($value === null)
            return;

        if (self::IsTelegram())
        {
            $this->description[] = "- <b>$title</b>: ".self::SafeText($value);   // values come from token metadata
        }
        else if (self::IsDiscord())
        {
            $this->description[] = "- **$title**: $value";
        }
    }

    private function appendInfoAddress(string $title, ?string $address, AbstractNetwork $network): void
    {
        if ($address === null)
            return;

        $publicName = self::SafeText($network->getWalletPublicName($address));

        if (self::IsTelegram())
        {
            $this->description[] = "- <b>$title</b>: $publicName";
            $this->description[] = "<code>$address</code>";
        }
        else if (self::IsDiscord())
        {
            $this->description[] = "- **$title**: $publicName";
            $this->description[] = "`$address`";
        }
    }
}
