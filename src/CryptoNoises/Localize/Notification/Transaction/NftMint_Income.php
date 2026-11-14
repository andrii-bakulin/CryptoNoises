<?php

namespace CryptoNoises\Localize\Notification\Transaction;

use CryptoNoises\Entities\User;

// Currently, it's used only for fx(hash)

class NftMint_Income extends AbstractTransaction
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
                '⚪️🟢 NFT "'.$this->presetNftName().'" has been minted. Your revenue is <b>{###AMOUNT_TOTAL###}</b>',
                '{###AMOUNT_TOTAL_CONVERTED###}',
                '- Quantity: {###QUANTITY###}',
                '- Marketplace: '.$this->presetMarketplace(),
            ];
        }
        else if(self::IsDiscord())
        {
            $this->title = ['NFT "'.$this->presetNftName().'" has been minted'];
            $this->url   = '{###TOKEN_STORE_URL###}';

            $this->description = [
                '- Revenue: {###AMOUNT_TOTAL###} {###AMOUNT_TOTAL_CONVERTED###}',
                '- Quantity: {###QUANTITY###}',
                self::EMPTY_LINE,
                '- Marketplace: '.$this->presetMarketplace(),
            ];

            $this->color = self::COLOR_GREEN;
        }
    }
}
