<?php

namespace CryptoNoises\Localize\Notification;

use CryptoNoises\Config;
use CryptoNoises\Entities\User;
use CryptoNoises\Localize\Notification;
use CryptoNoises\Localize\Phrase;

class AboutToken_NotExists extends Notification
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

        $this->title = ["Sorry, I don't have information about this token."];

        if (self::IsTelegram())
        {
            $this->description[] = '- <b>Contract</b>: {###TOKEN_CONTRACT###}';
            $this->description[] = '- <b>TokenID</b>: {###TOKEN_ID###}';
            $this->description[] = self::EMPTY_LINE;
        }
        else if (self::IsDiscord())
        {
            $this->appendFieldValue('Contract', '{###TOKEN_CONTRACT###}');
            $this->appendFieldValue('Token ID', '{###TOKEN_ID###}');
        }

        $openTicketUrl = Config::cfg()->getLink('discord_open_ticket_url');

        if ($openTicketUrl !== null)
        {
            $this->description[] = 'If you are sure this token exists, please open a ticket on our Discord server and give us the details.';

            $this->appendLinkButtonsRow([
                Phrase::I()->actionRedirectToOpenTicket => $openTicketUrl,
            ]);
        }
    }
}
