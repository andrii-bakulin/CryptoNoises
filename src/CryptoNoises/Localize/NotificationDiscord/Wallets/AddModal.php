<?php

namespace CryptoNoises\Localize\NotificationDiscord\Wallets;

use CryptoNoises\Entities\User;
use CryptoNoises\Localize\NotificationDiscord\Modal;
use CryptoNoises\Localize\Phrase;
use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\TextInput;

class AddModal extends Modal
{
    protected string $originMessageUpdate = self::kORIGIN_MESSAGE_UPDATE_Skip;

    public static function I(?User $user): self
    {
        return new self($user);
    }

    public function __construct(?User $user)
    {
        parent::__construct($user);

        $this->title = [
            "Add wallets"
        ];

        $component = TextInput::new("Wallet address(es)", TextInput::STYLE_PARAGRAPH, 'ds.wallet.add.address')
            ->setPlaceholder(Phrase::I()->demoWalletAddress.' '.Phrase::I()->demoWalletName)
            ->setRequired(true);

        $this->components[] = ActionRow::new()->addComponent($component);
    }
}
