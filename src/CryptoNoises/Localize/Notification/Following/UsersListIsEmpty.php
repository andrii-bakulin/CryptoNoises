<?php

namespace CryptoNoises\Localize\Notification\Following;

use CryptoNoises\Bots\Core\SlashCommand;
use CryptoNoises\Entities\User;
use CryptoNoises\Localize\Notification;

class UsersListIsEmpty extends Notification
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

        $this->title = [
            "You don't follow anyone yet",
        ];

        $this->description = [
            "To start following users, use the command ".$this->wrapCommand(SlashCommand::FOLLOW)." or the button below.",
            self::EMPTY_LINE,
            "After you add users to follow (their wallets) you'll receive notifications about NFTs they mint and/or list.",
        ];
    }
}
