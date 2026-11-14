<?php

namespace CryptoNoises\Localize\Notification\Transaction;

use CryptoNoises\Entities\User;
use CryptoNoises\Localize\NotificationButton;

class NftMint extends AbstractTransaction
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
                '⚪️️ NFT "'.$this->presetNftName().'" has been minted',
                '- Quantity: {###QUANTITY###}',
                '- Royalty: {###ROYALTY_PERCENT###}',
                '- Creator: '.$this->presetTokenCreatorProfile(),
                '- Collection: '.$this->presetCollectionInfo(),
                '- Marketplace: '.$this->presetMarketplace(),
            ];
        }
        else if(self::IsDiscord())
        {
            $this->title = ['NFT "'.$this->presetNftName().'" has been minted'];
            $this->url   = '{###TOKEN_STORE_URL###}';

            $this->description = [
                '- Quantity: {###QUANTITY###}',
                '- Royalty: {###ROYALTY_PERCENT###}',
                self::EMPTY_LINE,
                '- Creator: '.$this->presetTokenCreatorProfile(),
                '- Collection: '.$this->presetCollectionInfo(),
                '- Marketplace: '.$this->presetMarketplace(),
            ];

            $this->buttons[] = [
                NotificationButton::Link('Creator',             '{###TOKEN_CREATOR_PROFILE_URL###}'),
                NotificationButton::Link('Collection',          '{###TOKENS_COLLECTION_URL###}'),
            ];

            $this->color = self::COLOR_WHITE;
        }

        $this->footer = []; // reset!
    }
}
