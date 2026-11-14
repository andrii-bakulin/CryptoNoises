<?php

namespace CryptoNoises\Localize\Notification\Redirect;

use CryptoNoises\Entities\CallbackAction;
use CryptoNoises\Entities\User;
use CryptoNoises\Entities\UserRedirect;
use CryptoNoises\Localize\Notification;
use CryptoNoises\Localize\NotificationButton;
use CryptoNoises\Localize\Phrase;

class HowToActivate extends Notification
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
            "Redirect all DM notifications to your own Discord channel",
        ];

        $this->description = [
            'By using this command you may redirect all DM (Direct Message) notifications from the bot to one of the channels of your Discord server.',
            self::EMPTY_LINE,
            'Requirements:',
            "{###STATUS__IS_MODERATOR###} You must be an Administrator or have the 'Manage Server' permission on this server",
            "{###STATUS__SELF_REDIRECT_DISABLED###} Your redirection should be disabled if it's active in another channel",
            "{###STATUS__NO_REDIRECT_BY_OTHER_USER###} Channel shouldn't have other (external) redirection enabled",
            self::EMPTY_LINE,
            "After enabling redirection all notifications will be published on that channel and you'll stop receiving notifications in DM.",
        ];

        $this->footer[] = "⚠️ You can cancel redirection at any time to start receiving notifications in DM again.";
    }

    public function setupStates(?bool $isModerator, ?string $redirectToChannelId): self
    {
        $buttons = [];
        $caBuilder = CallbackAction::Builder(CallbackAction::Scope_RedirectDM);

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        if ($isModerator !== null)
            $this->bind('{###STATUS__IS_MODERATOR###}', $isModerator ? '✅' : '🚫');
        else
            $this->bind('{###STATUS__IS_MODERATOR###}', '❓');

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $ownRedirect = $this->user->redirect();

        if (!$ownRedirect->isEnabled)
        {
            $this->bind('{###STATUS__SELF_REDIRECT_DISABLED###}', '✅');
        }
        else
        {
            $this->bind('{###STATUS__SELF_REDIRECT_DISABLED###}', '🚫');

            $buttons[] = NotificationButton::CallbackAction(Phrase::I()->redirectDeactivateSelf,
                $caBuilder->newEventAuid('redirect-dm.deactive'));
        }

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $otherRedirect = UserRedirect::GetByChannelId($redirectToChannelId);

        if ($otherRedirect == null)
        {
            $this->bind('{###STATUS__NO_REDIRECT_BY_OTHER_USER###}', '✅');
        }
        else
        {
            $this->bind('{###STATUS__NO_REDIRECT_BY_OTHER_USER###}', '🚫');

            if ($isModerator)
            {
                $buttons[] = NotificationButton::CallbackAction(Phrase::I()->redirectDeactivateOther,
                    $caBuilder->newEventAuid('redirect-dm.deactivate.external'));
            }
        }

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        if ($isModerator && count($buttons) == 0)
        {
            $buttons[] = NotificationButton::CallbackAction(Phrase::I()->redirectActivate,
                $caBuilder->newEventAuid('redirect-dm.activate'));
        }
        else
        {
            $buttons[] = NotificationButton::CallbackAction(Phrase::I()->redirectActivate,
                $caBuilder->newEventAuid('redirect-dm.activate'))->setStyleDisabled();
        }

        $this->appendButtonsRow($buttons);
        return $this;
    }
}
