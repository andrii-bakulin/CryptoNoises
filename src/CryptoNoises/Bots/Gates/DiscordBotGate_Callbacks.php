<?php

namespace CryptoNoises\Bots\Gates;

use CryptoNoises\Entities\CallbackAction;
use CryptoNoises\Localize\Phrase;
use CryptoNoises\Models;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Interactions\Interaction;
use Exception;

trait DiscordBotGate_Callbacks
{
    public static function OnCallback(Discord $discord, Interaction $interaction, CallbackAction $callbackAction): bool
    {
        $logRecord = Models\Log\BotMessagesDiscord::model()->saveInteraction($interaction);

        $botGate = DiscordBotGate::NewByInteraction($discord, $interaction);

        if ($botGate === null)
        {
            $interaction->respondWithMessage(MessageBuilder::new()->setContent(Phrase::I()->systemError), true);

            $discord->getLogger()->error("Cannot initialize bot-gate", [
                'location'      => 'DiscordBotGate::OnCallback',
                'attributes'    => $interaction->data->getRawAttributes(),
            ]);
            return false;
        }

        $result = $botGate->routeByCallback($discord, $interaction, $callbackAction);
        return $logRecord->done($result);
    }

    public function routeByCallback(Discord $discord, Interaction $interaction, CallbackAction $callbackAction): bool
    {
        try
        {
            switch ($callbackAction->scope)
            {
                case CallbackAction::Scope_Summary:             return $this->walletsSummary($callbackAction);
                case CallbackAction::Scope_Wallets_Details:     return $this->walletDetails($callbackAction);
                case CallbackAction::Scope_Wallets_Add:         return $this->walletAdd(null, $callbackAction);
                case CallbackAction::Scope_Wallets_Delete:      return $this->walletDelete(null, $callbackAction);
                case CallbackAction::Scope_Following:           return $this->following($callbackAction);
                case CallbackAction::Scope_Follow:              return $this->follow(null, $callbackAction);
                case CallbackAction::Scope_Unfollow:            return $this->unfollow(null, $callbackAction);
                case CallbackAction::Scope_Domains:             return $this->domains($callbackAction);
                case CallbackAction::Scope_Offers:              return $this->offers($callbackAction);
                case CallbackAction::Scope_CoinsRates:          return $this->coinsRates($callbackAction);
                case CallbackAction::Scope_Settings:            return $this->settings($callbackAction);
                case CallbackAction::Scope_Help:                return $this->help();

                case CallbackAction::Scope_RedirectDM:          return $this->redirectDM($interaction, $callbackAction);

                case CallbackAction::Scope_System_CancelLastAction: return $this->cancelLastUserAction();
            }

            throw new Exception("Unknown scope [$callbackAction->scope]");
        }
        catch (Exception $e)
        {
            $discord->getLogger()->error("ERR: ".$e->getMessage(), [
                'location'      => 'DiscordBotGate->'.__FUNCTION__,
                'attributes'    => $interaction->getRawAttributes(),
            ]);
        }

        $result = $this->doCallbackIsUndefined($this->dcBotConnection->user);
        $this->sendNotifications($result);
        return false;
    }
}
