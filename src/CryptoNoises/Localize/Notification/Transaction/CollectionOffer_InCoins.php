<?php

namespace CryptoNoises\Localize\Notification\Transaction;

use CryptoNoises\Entities\User;
use CryptoNoises\Localize\NotificationButton;

class CollectionOffer_InCoins extends AbstractTransaction
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
                '🟠 You received an offer for collection "'.$this->presetNftName().'" in the amount of <b>{###AMOUNT_TOTAL###}</b>',
                '{###AMOUNT_TOTAL_CONVERTED###}',
                '- Sender: '.$this->presetSenderProfile(),
                '- Marketplace: '.$this->presetMarketplace(),
            ];
        }
        else if(self::IsDiscord())
        {
            $this->title = ['You received an offer for collection "'.$this->presetNftName().'"'];
            $this->url   = '{###TOKEN_STORE_URL###}';

            $this->description = [
                '- Offer price: {###AMOUNT_TOTAL###} {###AMOUNT_TOTAL_CONVERTED###}',
                self::EMPTY_LINE,
                '- Sender: '.$this->presetSenderProfile(),
                '- Marketplace: '.$this->presetMarketplace(),
            ];

            $this->buttons[] = [
                NotificationButton::Link('Sender',              '{###SENDER_PROFILE_URL###}'),
            #   NotificationButton::Link('Collection',          '{###TOKENS_COLLECTION_URL###}'),
            ];

            $this->color = self::COLOR_ORANGE;
        }
    }
}
