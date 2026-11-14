<?php

namespace CryptoNoises\Localize\Notification\Transaction;

use CryptoNoises\Entities\User;
use CryptoNoises\Localize\NotificationButton;

class AuctionStart extends AbstractTransaction
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
                '🟤🟢 The auction has started for NFT "'.$this->presetNftName().'"',
                '- Start price: <b>{###AMOUNT_TOTAL###}</b>',
                '- Duration: <b>{###DURATION###}</b>',
                '- Quantity: {###QUANTITY###}',             // Notice: for ObjktCom/Dutch may be more than 1 edition to sell
                '- Min bid increment: <b>{###AUCTION_BID_INC_PCT###}</b>',
                '- Direct Sale Price: <b>{###AUCTION_DIRECT_PRICE###}</b>',
                '- Seller: '.$this->presetSenderProfile(),
                '- Creator: '.$this->presetTokenCreatorProfile(),
                '- Collection: '.$this->presetCollectionInfo(),
                '- Marketplace: '.$this->presetMarketplace(),
            ];
        }
        else if(self::IsDiscord())
        {
            $this->title = ['The auction has started for NFT "'.$this->presetNftName().'"'];
            $this->url   = '{###TOKEN_STORE_URL###}';

            $this->description = [
                '- Start price: {###AMOUNT_TOTAL###} {###AMOUNT_TOTAL_CONVERTED###}',
                '- Direct Sale Price: {###AUCTION_DIRECT_PRICE###}',
                '- Min bid increment: {###AUCTION_BID_INC_PCT###}',
                '- Duration: {###DURATION###}',
                '- Quantity: {###QUANTITY###}',             // Notice: for ObjktCom/Dutch may be more than 1 edition to sell
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

            $this->color = self::COLOR_GREEN;
        }
    }
}
