<?php

namespace CryptoNoises\Localize\NotificationDiscord;

use CryptoNoises\Entities\CallbackAction;
use CryptoNoises\Localize\Notification;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Interaction;

abstract class Modal extends Notification
{
    const kORIGIN_MESSAGE_UPDATE_Skip       = 'skip';
    const kORIGIN_MESSAGE_UPDATE_Done       = 'done';

    public ?bool $discord_isAllowEphemeralResponse  = true;

    //------------------------------------------------------------------------------------------------------------------

    protected string $originMessageUpdate = self::kORIGIN_MESSAGE_UPDATE_Skip;

    protected array $components = [];
    protected string $callbackAuid = '';

    public function setCallbackAuid(string $callbackAuid): self
    {
        $this->callbackAuid = $callbackAuid;
        return $this;
    }

    public function showModal(Interaction $interaction): void
    {
        $title    = join(" ", $this->title);
        $customId = CallbackAction::PackFullCustomId($this->callbackAuid);

        $interaction->showModal($title, $customId, $this->components, function (Interaction $interaction, $components)
        {
            switch ($this->originMessageUpdate)
            {
                case self::kORIGIN_MESSAGE_UPDATE_Done:
                    $emptyMessage = MessageBuilder::new()
                        ->setContent('✅ Done')
                        ->setEmbeds([])         // Remove current "Embeds"
                        ->setComponents([]);    // Remove current "Buttons"

                    $interaction->updateOriginalResponse($emptyMessage);
                    break;

                case self::kORIGIN_MESSAGE_UPDATE_Skip:
                default:
                    break;
            }
        });
    }
}
