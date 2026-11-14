<?php

namespace CryptoNoises\Localize\Notification\Transaction;

use CryptoNoises\Entities\User;
use CryptoNoises\Localize\NotificationButton;

class NftList extends AbstractTransaction
{
    public static function I(?User $user): self
    {
        return new self($user);
    }

    public function __construct(?User $user)
    {
        parent::__construct($user);

        if (self::IsTelegram())
        {
            $this->description = [
                '🟠 NFT "'.$this->presetNftName().'" has been listed',
                '- Price: <b>{###AMOUNT_TOTAL###}</b>',
                '{###AMOUNT_TOTAL_CONVERTED###}',
                '- Duration: <b>{###DURATION###}</b>',
                '- Burn On End: <b>{###BURN_ON_END###}</b>',
                '- Quantity: {###QUANTITY###}',
                '- Editions: {###EDITIONS###}',
                '- Royalty: {###ROYALTY_PERCENT###}',
                self::EMPTY_LINE,
                '- Seller: '.$this->presetSenderProfile(),
                '- Creator: '.$this->presetTokenCreatorProfile(),
                '- Collection: '.$this->presetCollectionInfo(),
                '- Marketplace: '.$this->presetMarketplace(),
            ];
        }
        else if(self::IsDiscord())
        {
            $this->title = ['NFT "'.$this->presetNftName().'" has been listed'];
            $this->url   = '{###TOKEN_STORE_URL###}';

            $this->description = [
                '- Price: {###AMOUNT_TOTAL###} {###AMOUNT_TOTAL_CONVERTED###}',
                '- Duration: {###DURATION###}',
                '- Burn On End: {###BURN_ON_END###}',
                '- Quantity: {###QUANTITY###}',
                '- Editions: {###EDITIONS###}',
                '- Royalty: {###ROYALTY_PERCENT###}',
                self::EMPTY_LINE,
                '- Seller: '.$this->presetSenderProfile(),
                '- Creator: '.$this->presetTokenCreatorProfile(),
                '- Collection: '.$this->presetCollectionInfo(),
                '- Marketplace: '.$this->presetMarketplace(),
            ];

            $this->buttons[] = [
                NotificationButton::Link('Seller',              '{###SENDER_PROFILE_URL###}'),
                NotificationButton::Link('Creator',             '{###TOKEN_CREATOR_PROFILE_URL###}'),
                NotificationButton::Link('Collection',          '{###TOKENS_COLLECTION_URL###}'),
            ];

            $this->color = self::COLOR_ORANGE;
        }

        $this->footer = []; // reset!
    }
}
