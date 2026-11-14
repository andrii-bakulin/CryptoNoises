<?php

namespace CryptoNoises\Localize\Notification\Redirect;

use CryptoNoises\Entities\CallbackAction;
use CryptoNoises\Entities\User;
use CryptoNoises\Localize\Notification;
use CryptoNoises\Localize\NotificationButton;
use CryptoNoises\Localize\Phrase;

class RedirectIsActive extends Notification
{
    // callback id of the disabled status button (it is never clicked)
    private const kDISABLED_BUTTON_ID = '?NONE?';

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
            "✅ Redirection for DM notifications is enabled",
        ];

        $this->description = [
            'All notifications from the bot will be redirected to the channel <#{###ACTIVE_CHANNEL_ID###}>.',
            'Users who have access to this channel will be able to see these notifications.',
        ];

        $this->footer[] = "⚠️ DM notifications are disabled until you turn redirection off.";
        $this->footer[] = "⚠️ Deactivate redirection to start receiving notifications in DM again.";

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $caBuilder = CallbackAction::Builder(CallbackAction::Scope_RedirectDM);

        $this->appendButtonsRow([
            NotificationButton::CallbackAction(Phrase::I()->redirectIsActive, self::kDISABLED_BUTTON_ID)->setStyleDisabled(),
            NotificationButton::CallbackAction(Phrase::I()->redirectDeactivate,
                $caBuilder->newEventAuid('redirect-dm.deactive')),
        ]);
    }
}
