<?php

namespace CryptoNoises\Localize\Notification\Transaction;

use CryptoNoises\Entities\User;
use CryptoNoises\Localize\NotificationButton;

class AuctionOutBid extends AbstractTransaction
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
                '🟤🔴 Your bid of <b>{###AMOUNT_TOTAL###}</b> was outbid on auction "'.$this->presetNftName().'"',
                '{###AMOUNT_TOTAL_CONVERTED###}',
                '- New top bid: <b>{###AMOUNT_TOP_BID###}</b>',
                '{###AMOUNT_TOP_BID_CONVERTED###}',
                '- Bidder: <a href="{###TOP_OWNER_PROFILE_URL###}">{###TOP_OWNER_ADDRESS###}</a>',
                '- Collection: '.$this->presetCollectionInfo(),
                '- Marketplace: '.$this->presetMarketplace(),
            ];
        }
        else if(self::IsDiscord())
        {
            $this->title = ['Your bid was outbid on auction "'.$this->presetNftName().'"'];
            $this->url   = '{###TOKEN_STORE_URL###}';

            $this->description = [
                '- Your bid: {###AMOUNT_TOTAL###} {###AMOUNT_TOTAL_CONVERTED###}',
                '- New top bid: {###AMOUNT_TOP_BID###} {###AMOUNT_TOP_BID_CONVERTED###}',
                self::EMPTY_LINE,
                '- Bidder: {###TOP_OWNER_ADDRESS###}',
                '- Collection: '.$this->presetCollectionInfo(),
                '- Marketplace: '.$this->presetMarketplace(),
            ];

            $this->buttons[] = [
                NotificationButton::Link('Bidder',              '{###TOP_OWNER_PROFILE_URL###}'),
                NotificationButton::Link('Collection',          '{###TOKENS_COLLECTION_URL###}'),
            ];

            $this->color = self::COLOR_RED;
        }
    }
}
