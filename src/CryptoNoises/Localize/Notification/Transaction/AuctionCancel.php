<?php

namespace CryptoNoises\Localize\Notification\Transaction;

use CryptoNoises\Entities\User;
use CryptoNoises\Localize\NotificationButton;

class AuctionCancel extends AbstractTransaction
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
                '🟤🔴 The auction was canceled for NFT "'.$this->presetNftName().'"',
                '- Collection: '.$this->presetCollectionInfo(),
                '- Marketplace: '.$this->presetMarketplace(),
            ];
        }
        else if(self::IsDiscord())
        {
            $this->title = ['The auction was canceled for NFT "'.$this->presetNftName().'"'];
            $this->url   = '{###TOKEN_STORE_URL###}';

            $this->description = [
                '- Collection: '.$this->presetCollectionInfo(),
                '- Marketplace: '.$this->presetMarketplace(),
            ];

            $this->buttons[] = [
                NotificationButton::Link('Collection',          '{###TOKENS_COLLECTION_URL###}'),
            ];

            $this->color = self::COLOR_RED;
        }
    }
}
