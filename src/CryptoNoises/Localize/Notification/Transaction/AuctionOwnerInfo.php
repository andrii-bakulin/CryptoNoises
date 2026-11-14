<?php

namespace CryptoNoises\Localize\Notification\Transaction;

use CryptoNoises\Entities\User;
use CryptoNoises\Localize\NotificationButton;

class AuctionOwnerInfo extends AbstractTransaction
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
                '🟤🟠 You received a bid of <b>{###AMOUNT_TOTAL###}</b> on auction "'.$this->presetNftName().'"',
                '{###AMOUNT_TOTAL_CONVERTED###}',
                '- Bidder: '.$this->presetSenderProfile(),
                '- Collection: '.$this->presetCollectionInfo(),
                '- Marketplace: '.$this->presetMarketplace(),
            ];
        }
        else if(self::IsDiscord())
        {
            $this->title = ['You received a bid on auction "'.$this->presetNftName().'"'];
            $this->url   = '{###TOKEN_STORE_URL###}';

            $this->description = [
                '- Bid: {###AMOUNT_TOTAL###} {###AMOUNT_TOTAL_CONVERTED###}',
                self::EMPTY_LINE,
                '- Bidder: '.$this->presetSenderProfile(),
                '- Collection: '.$this->presetCollectionInfo(),
                '- Marketplace: '.$this->presetMarketplace(),
            ];

            $this->buttons[] = [
                NotificationButton::Link('Bidder',              '{###SENDER_PROFILE_URL###}'),
                NotificationButton::Link('Collection',          '{###TOKENS_COLLECTION_URL###}'),
            ];

            $this->color = self::COLOR_ORANGE;
        }
    }
}
