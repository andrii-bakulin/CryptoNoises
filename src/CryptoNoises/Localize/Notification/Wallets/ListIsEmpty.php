<?php

namespace CryptoNoises\Localize\Notification\Wallets;

use CryptoNoises\Bots\Core\SlashCommand;
use CryptoNoises\Entities\CallbackAction;
use CryptoNoises\Entities\User;
use CryptoNoises\Localize\Notification;
use CryptoNoises\Localize\NotificationButton;
use CryptoNoises\Localize\Phrase;

class ListIsEmpty extends Notification
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
            "ℹ️ You haven't added any wallets yet",
        ];

        if (self::IsTelegram())
        {
            $this->description = [
                "To add your first wallet click ➡️ ".$this->wrapCommand(SlashCommand::ADD_WALLET),
                "Or choose an action from the menu",
                "⬇️",
            ];
        }
        else if(self::IsDiscord())
        {
            $this->description = [
                "To add your first wallet, use the command",
                $this->wrapCommand(SlashCommand::ADD_WALLET),
            ];
        }

        $this->buttons[] = [
            NotificationButton::CallbackAction(Phrase::I()->actionAddWallet,
                CallbackAction::Builder(CallbackAction::Scope_Wallets_Add)->newEventAuid()),
        ];
    }
}
