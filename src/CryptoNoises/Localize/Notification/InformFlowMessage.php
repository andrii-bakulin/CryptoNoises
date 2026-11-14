<?php

namespace CryptoNoises\Localize\Notification;

use CryptoNoises\Config;
use CryptoNoises\Entities\User;
use CryptoNoises\Localize\Notification;
use CryptoNoises\Localize\Phrase;

class InformFlowMessage extends Notification
{
    public ?bool $discord_isAllowEphemeralResponse  = false;

    //------------------------------------------------------------------------------------------------------------------

    public static function I(?User $user): self
    {
        return new self($user);
    }

    public function __construct(?User $user)
    {
        parent::__construct($user);

        $this->appendLinkButtonsRow([
            Phrase::I()->actionJoinTwitter => Config::cfg()->getLink('twitter'),
            Phrase::I()->actionJoinDiscord => Config::cfg()->getLink('discord_invite'),
        ]);
    }

    /**
     * @param string $title
     * @return Notification
     */
    public function setTitle($title): Notification
    {
        if (self::IsTelegram())
        {
            $title = '<b>'.$title.'</b>';
        }
        else if(self::IsDiscord())
        {
            $title = strip_tags($title);
        }
        return parent::setTitle($title);
    }

    /**
     * @param string $description
     * @return Notification
     */
    public function setDescription($description): Notification
    {
        if (self::IsDiscord())
        {
            $description = strip_tags($description);
        }
        return parent::setDescription($description);
    }
}
