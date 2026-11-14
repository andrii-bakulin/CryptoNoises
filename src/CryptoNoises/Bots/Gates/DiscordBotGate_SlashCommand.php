<?php

namespace CryptoNoises\Bots\Gates;

use CryptoNoises\Bots\Core\SlashCommand;
use CryptoNoises\Localize\Phrase;
use CryptoNoises\Models;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Interactions\Interaction;
use Exception;

trait DiscordBotGate_SlashCommand
{
    public static function OnSlashCommand(Discord $discord, Interaction $interaction, SlashCommand $slashCommand): bool
    {
        $logRecord = Models\Log\BotMessagesDiscord::model()->saveInteraction($interaction);

        $botGate = DiscordBotGate::NewByInteraction($discord, $interaction);

        if ($botGate === null)
        {
            $interaction->respondWithMessage(MessageBuilder::new()->setContent(Phrase::I()->systemError), true);

            $discord->getLogger()->error("Cannot initialize bot-gate", [
                'location'      => 'DiscordBotGate::OnSlashCommand',
                'attributes'    => $interaction->data->getRawAttributes(),
            ]);
            return false;
        }

        $result = $botGate->routeBySlashCommand($discord, $interaction, $slashCommand);
        return $logRecord->done($result);
    }

    public function routeBySlashCommand(Discord $discord, Interaction $interaction, SlashCommand $slashCommand): bool
    {
        try
        {
            switch ($slashCommand->command)
            {
                case SlashCommand::START:               return $this->start();
                case SlashCommand::HELP:                return $this->help();

                case SlashCommand::SUMMARY:             return $this->walletsSummary();
                case SlashCommand::WALLETS_LIST:        return $this->walletsList();
                case SlashCommand::ADD_WALLET:          return $this->walletAdd($slashCommand->vars['address'], null);
                case SlashCommand::DELETE_WALLET:       return $this->walletDelete($slashCommand->vars['address'], null);

                case SlashCommand::FOLLOWING:           return $this->following();
                case SlashCommand::FOLLOW:              return $this->follow($slashCommand->vars['address'], null);
                case SlashCommand::UNFOLLOW:            return $this->unfollow($slashCommand->vars['address'], null);

                case SlashCommand::DOMAINS:             return $this->domains();
                case SlashCommand::OFFERS:              return $this->offers();
                case SlashCommand::COINS:               return $this->coinsRates();

                case SlashCommand::SETTINGS:            return $this->settings();

                case SlashCommand::ABOUT_TOKEN:         return $this->aboutToken($slashCommand->vars['address'], $slashCommand->vars['token_id']);

                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

                case SlashCommand::REDIRECT_DM:         return $this->redirectDM($interaction);

            }

            throw new Exception("Unknown command [$slashCommand->command]");
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
