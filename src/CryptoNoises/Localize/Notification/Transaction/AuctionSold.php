<?php

namespace CryptoNoises\Localize\Notification\Transaction;

use CryptoNoises\Entities\User;
use CryptoNoises\Localize\NotificationButton;

class AuctionSold extends AbstractTransaction
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
                '🟤🔵 NFT "'.$this->presetNftName().'" has been sold on auction. Your revenue is <b>{###AMOUNT_TOTAL###}</b>',
                '{###AMOUNT_TOTAL_CONVERTED###}',
                '- Quantity: {###QUANTITY###}',
                '- New owner: '.$this->presetTargetProfile(),
                '- Collection: '.$this->presetCollectionInfo(),
                '- Marketplace: '.$this->presetMarketplace(),
            ];
        }
        else if(self::IsDiscord())
        {
            $this->title = ['NFT "'.$this->presetNftName().'" has been sold on auction'];
            $this->url   = '{###TOKEN_STORE_URL###}';

            $this->description = [
                '- Revenue: {###AMOUNT_TOTAL###} {###AMOUNT_TOTAL_CONVERTED###}',
                '- Quantity: {###QUANTITY###}',
                self::EMPTY_LINE,
                '- New owner: '.$this->presetTargetProfile(),
                '- Collection: '.$this->presetCollectionInfo(),
                '- Marketplace: '.$this->presetMarketplace(),
            ];

            $this->buttons[] = [
                NotificationButton::Link('New owner',           '{###TARGET_PROFILE_URL###}'),
                NotificationButton::Link('Collection',          '{###TOKENS_COLLECTION_URL###}'),
            ];

            $this->color = self::COLOR_BLUE;
        }
    }
}
