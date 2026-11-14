<?php

namespace CryptoNoises\Localize\Notification\Wallets;

use CryptoNoises\Entities\CallbackAction;
use CryptoNoises\Entities\User;
use CryptoNoises\Helpers\Helper;
use CryptoNoises\Localize\Notification;
use CryptoNoises\Localize\NotificationButton;
use CryptoNoises\Localize\Phrase;

class SummaryInfo extends Notification
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
    }

    public function initialize(array $walletsData, array $balancesData, int $totalWalletsCount, int $firstItemIndex): self
    {
        $index = 0;
        foreach ($walletsData as $message)
        {
            $msgIndex = Helper::NumberToKeycapEmoji($firstItemIndex+$index);

            if (self::IsTelegram())
            {
                $this->description[] = self::EMPTY_LINE;
                $this->description[] = "$msgIndex $message";
            }
            else if(self::IsDiscord())
            {
                list ($title, $value) = array_pad(explode(PHP_EOL, $message, 2), 2, null);

                $this->appendFieldValue("$msgIndex $title", $value);
            }
            $index++;
        }

        //--------------------------------------------------------------------------------------------------------------

        if ($totalWalletsCount > 1)
        {
            if (self::IsTelegram())
                $this->footer[] = "------------------------------------";

            $this->footer[] = "Total amount for $totalWalletsCount wallets:";

            $this->footer = array_merge($this->footer, $balancesData);
        }

        //--------------------------------------------------------------------------------------------------------------

        $buttonsControl = [];

        $buttonsControl[] =
            NotificationButton::CallbackAction(Phrase::I()->actionAddWallet,
                CallbackAction::Builder(CallbackAction::Scope_Wallets_Add)->newEventAuid());

        $buttonsControl[] =
            NotificationButton::CallbackAction(Phrase::I()->actionDelete,
                CallbackAction::Builder(CallbackAction::Scope_Wallets_Delete)->newEventAuid());

        $buttonsControl[] =
            NotificationButton::CallbackAction(Phrase::I()->actionRefresh,
                CallbackAction::Builder(CallbackAction::Scope_Summary)->newEventAuid());

        $this->buttons[] = $buttonsControl;

        //--------------------------------------------------------------------------------------------------------------

        return $this;
    }
}
