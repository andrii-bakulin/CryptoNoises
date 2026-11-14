<?php

namespace CryptoNoises\Localize\Notification\Wallets;

use CryptoNoises\Entities\User;
use CryptoNoises\Localize\Notification;
use CryptoNoises\Localize\Phrase;

class HowToAdd extends Notification
{
    public ?bool $discord_isAllowEphemeralResponse  = true;

    //------------------------------------------------------------------------------------------------------------------

    public static function I(?User $user): self
    {
        return new self($user);
    }

    public function __construct(?User $user)
    {
        parent::__construct($user);

        if (self::IsTelegram())
        {
            $this->title = ["ℹ️ Send your Tezos domain or wallet address"];

            $this->description[] = "You can set a title for the wallet. Just write it after the wallet address.";
            $this->description[] = self::EMPTY_LINE;
            $this->description[] = "Also, you can add multiple domains/wallets by writing each address on a new line.";
            $this->description[] = self::EMPTY_LINE;

            $this->description[] = "For example:";
            $this->description[] = "<i>".Phrase::I()->demoWalletAddress."</i>";
            $this->description[] = "<i>".Phrase::I()->demoWalletAddress." <u>".Phrase::I()->demoWalletName."</u></i>";
            $this->description[] = "<i>".Phrase::I()->demoDomainName."</i>";
        }
        else if(self::IsDiscord())
        {
            $this->title = ["ℹ️ Add wallets"];

            $this->description[] = "Click the `".Phrase::I()->actionAddWallet."` button below and enter the Tezos domain or wallet address you want to add. You can add multiple domains/wallets by writing each address on a new line.";
            $this->description[] = self::EMPTY_LINE;
            $this->description[] = "Also, you can set a title for each wallet. Just write the title after the wallet address.";
            $this->description[] = self::EMPTY_LINE;

            $this->description[] = "For example:";
            $this->description[] = "__".Phrase::I()->demoWalletAddress."__";
            $this->description[] = "__".Phrase::I()->demoWalletAddress." **".Phrase::I()->demoWalletName."**__";
            $this->description[] = "__".Phrase::I()->demoDomainName."__";
        }
    }
}
