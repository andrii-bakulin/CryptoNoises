<?php

namespace CryptoNoises\Localize\Notification\Wallets;

use CryptoNoises\Entities\User;
use CryptoNoises\Entities\UserWallet;
use CryptoNoises\Helpers\Helper;
use CryptoNoises\Localize\Notification;
use CryptoNoises\Localize\Phrase;

class HowToDelete extends Notification
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
            $this->title = ["ℹ️ Send the wallet address you want to delete"];

            $this->description[] = "You can send several wallet addresses in one message. Just write each address on a new line.";
            $this->description[] = self::EMPTY_LINE;
            $this->description[] = "Also, you can write the index of the wallet you want to delete, or a range of indexes, like: <i>1, 3, 5-8</i>";
        }
        else
        {
            $this->title = ["ℹ️ Delete wallets"];

            $this->description[] = "Click the `".Phrase::I()->actionDeleteWallet."` button below and enter the wallet address(es) you want to delete. You can list multiple wallets by writing each address on a new line.";
            $this->description[] = self::EMPTY_LINE;
            $this->description[] = "Also, you can write the index of the wallet you want to delete, or a range of indexes, like: 1, 3, 5-8";
        }
    }

    public function appendWalletsOpen(): void
    {
        $this->description[] = self::EMPTY_LINE;
        $this->description[] = "Here is your wallet list:";

        if (self::IsDiscord())
            $this->description[] = '```';
    }

    public function appendWalletAddress(int $index, UserWallet $wallet): void
    {
        $address = $wallet->getWalletAddress();
        $name    = $wallet->getWalletName();

        $this->description[] = Helper::NumberToKeycapEmoji($index).' '.$address.($name ? ' '.$name : '');
    }

    public function appendWalletsClose(): void
    {
        if (self::IsDiscord())
            $this->description[] = '```';
    }
}
