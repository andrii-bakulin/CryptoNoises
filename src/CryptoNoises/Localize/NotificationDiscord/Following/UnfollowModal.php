<?php

namespace CryptoNoises\Localize\NotificationDiscord\Following;

use CryptoNoises\Entities\User;
use CryptoNoises\Localize\NotificationDiscord\Modal;
use CryptoNoises\Localize\Phrase;
use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\TextInput;

class UnfollowModal extends Modal
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
            "Unfollow"
        ];

        $component = TextInput::new("Wallet address(es)", TextInput::STYLE_PARAGRAPH, 'ds.following.unfollow.address')
            ->setPlaceholder(Phrase::I()->demoWalletAddress."\n".Phrase::I()->demoDomainName)
            ->setRequired(true);

        $this->components[] = ActionRow::new()->addComponent($component);
    }
}
