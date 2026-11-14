<?php

namespace CryptoNoises\Localize\Notification;

use CryptoNoises\Config;
use CryptoNoises\Entities\User;
use CryptoNoises\Localize\Notification;
use CryptoNoises\Localize\Phrase;

class Welcome extends Notification
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
            $this->title = [
                "Welcome to the <b>CryptoNoises Bot</b>",
            ];
        }
        else if(self::IsDiscord())
        {
            $this->title = [
                "Welcome to the **CryptoNoises Bot**",
            ];
        }

        $this->description = [
            "Add your Tezos wallet and you'll receive notifications about:",
            "🟢 Incoming transaction (income)",
            "🔴 Outgoing transaction (expense)",
            "🔵 NFT sales",
            "🟡 NFT royalty",
            "🟣 NFT purchases",
            "🟠 NFT offers",
            "🟤 Auction events",
        ];

        $this->appendLinkButtonsRow([
            Phrase::I()->actionPrivacyPolicy => Config::cfg()->getLink('privacy_policy'),
        ]);
    }
}
