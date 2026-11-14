<?php

namespace CryptoNoises\Bots;

use CryptoNoises\Bots\Connections\DiscordBotConnection;
use CryptoNoises\Core\Log;
use CryptoNoises\Entities\CallbackAction;
use CryptoNoises\Entities\User;
use CryptoNoises\Localize\Notification;
use CryptoNoises\Localize\NotificationButton;
use CryptoNoises\Localize\Phrase;

trait AbstractBot_Redirect
{
    protected function doRedirectDM(User $user, ?CallbackAction $callbackAction, ?bool $isModerator, ?string $redirectToChannelId, ?string $redirectInGuildId): ?Notification
    {
        $isUserInDMChannel = $redirectToChannelId === null;

        // Buttons are only a UI hint: callback actions can be replayed, so permissions are re-checked here
        $canManageChannel = $isModerator === true && !$isUserInDMChannel;

        $redirect = $user->redirect();

        //--------------------------------------------------------------------------------------------------------------
        // Actions

        switch ($callbackAction ? $callbackAction->event : null)
        {
            case 'redirect-dm.activate':
                if (!$canManageChannel)
                    break;

                $user->redirect()->activate($redirectToChannelId, $redirectInGuildId);
                Log::Event_RedirectDM_Activate($user->userId, "$redirectInGuildId:$redirectToChannelId");
                break;

            case 'redirect-dm.deactive':
                $user->redirect()->deactivate();
                Log::Event_RedirectDM_Deactivate($user->userId);
                break;

            case 'redirect-dm.deactivate.external':
                if (!$canManageChannel)
                    break;

                $user->redirect()->deactivateExternal($redirectToChannelId);
                break;
        }

        //--------------------------------------------------------------------------------------------------------------

        if ($redirect->isEnabled)
            return Notification\Redirect\RedirectIsActive::I($user)
                ->bind('{###ACTIVE_CHANNEL_ID###}', $redirect->redirectChannelId);

        //--------------------------------------------------------------------------------------------------------------

        if ($isUserInDMChannel)
        {
            $notification = Notification\Redirect\HowToActivateFromDM::I($user);

            if (defined('kDISCORD_CLIENT_ID_MAINBOT'))
            {
                $notification->appendButtonsRow([
                    NotificationButton::Link(Phrase::I()->redirectInviteBot,
                        DiscordBotConnection::GetInviteBotToServerUrl(kDISCORD_CLIENT_ID_MAINBOT)),
                ]);
            }
            else
            {
                Log::Fatal('AbstractBot_Redirect', 'doRedirectDM', 'Const kDISCORD_CLIENT_ID_MAINBOT not defined');
            }

            return $notification;
        }

        //--------------------------------------------------------------------------------------------------------------
        // User in Channel

        $notification = Notification\Redirect\HowToActivate::I($user);
        $notification->setupStates($isModerator, $redirectToChannelId);
        return $notification;
    }
}
