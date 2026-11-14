<?php

namespace CryptoNoises\Localize\Notification\Transaction;

use CryptoNoises\Entities\User;
use CryptoNoises\Localize\NotificationButton;

// Currently, it's used only for fx(hash)

class NftMint_Expense extends AbstractTransaction
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
                '⚪️🟣 You minted NFT "'.$this->presetNftName().'" for <b>{###AMOUNT_TOTAL###}</b>',
                '{###AMOUNT_TOTAL_CONVERTED###}',
                '- Quantity: {###QUANTITY###}',
                '- Iteration: <a href="{###ITERATION_URL###}">#{###ITERATION_IDX###}</a>',                              # fx(hash) iteration index
                '- Creator: '.$this->presetTokenCreatorProfile(),
                '- Marketplace: '.$this->presetMarketplace(),
            ];
        }
        else if(self::IsDiscord())
        {
            $this->title = ['You minted NFT "'.$this->presetNftName().'"'];
            $this->url   = '{###TOKEN_STORE_URL###}';

            $this->description = [
                '- Price: {###AMOUNT_TOTAL###} {###AMOUNT_TOTAL_CONVERTED###}',
                '- Quantity: {###QUANTITY###}',
                '- Iteration: #{###ITERATION_IDX###}',                                  # fx(hash) iteration index
                self::EMPTY_LINE,
                '- Creator: '.$this->presetTokenCreatorProfile(),
                '- Marketplace: '.$this->presetMarketplace(),
            ];

            $this->buttons[] = [
                NotificationButton::Link('Creator',             '{###TOKEN_CREATOR_PROFILE_URL###}'),
            ];

            $this->color = self::COLOR_VIOLET;
        }
    }
}
