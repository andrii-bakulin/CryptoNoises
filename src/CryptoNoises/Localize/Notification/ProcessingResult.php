<?php

namespace CryptoNoises\Localize\Notification;

use CryptoNoises\Entities\User;
use CryptoNoises\Localize\Notification;

class ProcessingResult extends Notification
{
    public ?bool $discord_isAllowEphemeralResponse  = true;

    //------------------------------------------------------------------------------------------------------------------

    public static function Success(?User $user, string $processType): self
    {
        $instance = new self($user);

        switch ($processType)
        {
            case 'wallet-delete':   $instance->title = ['✅ Wallets were successfully deleted']; break;
            case 'follow':          $instance->title = ['✅ You are now following']; break;
            case 'unfollow':        $instance->title = ['✅ You unfollowed']; break;
            default:                $instance->title = ['✅ Done']; break;
        }

        return $instance;
    }

    public static function Failed(?User $user, string $processType): self
    {
        $instance = new self($user);

        switch ($processType)
        {
            case 'wallet-add':      $instance->title = ['❌ Failed to add the wallet(s)']; break;
            case 'wallet-delete':   $instance->title = ['❌ Failed to delete the wallet(s)']; break;
            case 'follow':          $instance->title = ['❌ Failed to follow']; break;
            case 'unfollow':        $instance->title = ['❌ Failed to unfollow']; break;
            default:                $instance->title = ['❌ Failed']; break;
        }

        return $instance;
    }

    public function addWalletNames(array $walletNames): void
    {
        $walletNames = array_unique($walletNames);

        if(self::IsDiscord())
            $this->description[] = '```';

        foreach ($walletNames as $walletName)
        {
            $walletName = trim($walletName);

            if ($walletName == '')
                continue;

            $this->addWalletName($walletName);
        }

        if(self::IsDiscord())
            $this->description[] = '```';
    }

    public function addWalletName(string $walletName): void
    {
        if (self::IsTelegram())
        {
            $this->description[] = $walletName;
        }
        else if(self::IsDiscord())
        {
            $this->description[] = '- '.$walletName;
        }
    }
}
