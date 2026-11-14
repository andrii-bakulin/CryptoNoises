<?php

namespace CryptoNoises\Localize\NotificationDiscord\Wallets;

use CryptoNoises\Entities\User;
use CryptoNoises\Localize\NotificationDiscord\Modal;
use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\TextInput;

class RenameModal extends Modal
{
    protected string $originMessageUpdate = self::kORIGIN_MESSAGE_UPDATE_Done;

    public static function I(?User $user): self
    {
        return new self($user);
    }

    public function __construct(?User $user)
    {
        parent::__construct($user);

        $this->title = [
            "Rename wallet"
        ];

        $component = TextInput::new("New name", TextInput::STYLE_SHORT, 'ds.wallet.rename.new_name')
            ->setPlaceholder("Wallet name")
            ->setMinLength(1)
            ->setMaxLength(128)
            ->setRequired(true);

        $this->components[] = ActionRow::new()->addComponent($component);
    }
}
