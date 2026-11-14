<?php

namespace CryptoNoises\Localize\Notification\Following;

use CryptoNoises\Entities\User;
use CryptoNoises\Helpers\Helper;
use CryptoNoises\Localize\Notification;

class UsersList extends Notification
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
            "You follow",
        ];
    }

    public function appendAddress(int $index, string $addressPublicName, ?string $address): void
    {
        if (self::IsTelegram())
        {
            $this->description[] = Helper::NumberToKeycapEmoji($index)." <b>".self::SafeText($addressPublicName)."</b>";

            if ($address != null)
                $this->description[] = "<i>$address</i>";
        }
        else if(self::IsDiscord())
        {
            if ($address != null)
                $this->appendFieldValue(Helper::NumberToKeycapEmoji($index)." $addressPublicName", "`$address`");
            else
                $this->description[] = Helper::NumberToKeycapEmoji($index)." $addressPublicName";
        }
    }
}
