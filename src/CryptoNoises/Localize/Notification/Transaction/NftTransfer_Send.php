<?php

namespace CryptoNoises\Localize\Notification\Transaction;

use CryptoNoises\Entities\User;
use CryptoNoises\Localize\NotificationButton;

class NftTransfer_Send extends AbstractTransaction
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
                '🔴 You sent NFT "'.$this->presetNftName().'"',
                '- Quantity: {###QUANTITY###}',
                '- Receiver: '.$this->presetTargetProfile(),
                '- Collection: '.$this->presetCollectionInfo(),
                '- Marketplace: '.$this->presetMarketplace(),
            ];
        }
        else if(self::IsDiscord())
        {
            $this->title = ['You sent NFT "'.$this->presetNftName().'"'];
            $this->url   = '{###TOKEN_STORE_URL###}';

            $this->description = [
                '- Quantity: {###QUANTITY###}',
                self::EMPTY_LINE,
                '- Receiver: '.$this->presetTargetProfile(),
                '- Collection: '.$this->presetCollectionInfo(),
                '- Marketplace: '.$this->presetMarketplace(),
            ];

            $this->buttons[] = [
                NotificationButton::Link('Receiver',            '{###TARGET_PROFILE_URL###}'),
                NotificationButton::Link('Collection',          '{###TOKENS_COLLECTION_URL###}'),
            ];

            $this->color = self::COLOR_RED;
        }
    }
}
