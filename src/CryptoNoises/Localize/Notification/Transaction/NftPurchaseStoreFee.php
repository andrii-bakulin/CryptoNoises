<?php

namespace CryptoNoises\Localize\Notification\Transaction;

use CryptoNoises\Entities\User;
use CryptoNoises\Localize\NotificationButton;

class NftPurchaseStoreFee extends AbstractTransaction
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
                '⚫️ NFT "'.$this->presetNftName().'" has been sold for <b>{###FULL_SELL_PRICE###}</b>. Store fee is <b>{###AMOUNT_TOTAL###}</b>',
                '{###AMOUNT_TOTAL_CONVERTED###}',
                '- Quantity: {###QUANTITY###}',
                '- Seller: '.$this->presetSenderProfile(),
                '- Buyer: '.$this->presetTargetProfile(),
                '- Collection: '.$this->presetCollectionInfo(),
                '- Marketplace: '.$this->presetMarketplace(),
            ];
        }
        else if(self::IsDiscord())
        {
            $this->title = ['NFT "'.$this->presetNftName().'" has been sold'];
            $this->url   = '{###TOKEN_STORE_URL###}';

            $this->description = [
                '- Store fee: {###AMOUNT_TOTAL###} {###AMOUNT_TOTAL_CONVERTED###}',
                '- Selling price: {###FULL_SELL_PRICE###} {###FULL_SELL_PRICE_CONVERTED###}',
                '- Quantity: {###QUANTITY###}',
                self::EMPTY_LINE,
                '- Seller: '.$this->presetSenderProfile(),
                '- Buyer: '.$this->presetTargetProfile(),
                '- Collection: '.$this->presetCollectionInfo(),
                '- Marketplace: '.$this->presetMarketplace(),
            ];

            $this->buttons[] = [
                NotificationButton::Link('Seller',              '{###SENDER_PROFILE_URL###}'),
                NotificationButton::Link('Buyer',               '{###TARGET_PROFILE_URL###}'),
                NotificationButton::Link('Collection',          '{###TOKENS_COLLECTION_URL###}'),
            ];

            $this->color = self::COLOR_BLACK;
        }
    }
}
