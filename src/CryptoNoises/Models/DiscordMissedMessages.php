<?php

namespace CryptoNoises\Models;

use CryptoCore\Time;
use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModelRecord;

class DiscordMissedMessages extends VssModelRecord
{
    const kTYPE_User                            = 'user';
    const kTYPE_Channel                         = 'channel';

    const kSTATE_Notified                       = 'notified';
    const kSTATE_Ignored                        = 'ignored';

    //------------------------------------------------------------------------------------------------------------------

    public ?int $idx = null;
    public ?string $tsEvent = null;
    public ?string $type = null;
    public ?string $state = null;
    public ?int $userId = null;
    public ?int $channelId = null;
    public ?int $guildId = null;
    public ?string $message = null;
    public ?string $notice = null;

    //------------------------------------------------------------------------------------------------------------------

    public function tableName(): string
    {
        return 'discord_missed_messages';
    }

    protected function pack(): ?array
    {
        return [
            'idx'               => $this->idx,
            'ts_event'          => $this->tsEvent ?: Time::Now(),
            'type'              => $this->type,
            'state'             => $this->state,
            'platform_user_id'  => $this->userId,
            'channel_id'        => $this->channelId,
            'guild_id'          => $this->guildId,
            'message'           => $this->message,
            'notice'            => $this->notice,
        ];
    }

    protected function unpack(?array $item): void
    {
        $this->idx              = $item['idx'];
        $this->tsEvent          = $item['ts_event'];
        $this->type             = $item['type'];
        $this->state            = $item['state'];
        $this->userId           = $item['platform_user_id'];
        $this->channelId        = $item['channel_id'];
        $this->guildId          = $item['guild_id'];
        $this->message          = $item['message'];
        $this->notice           = $item['notice'];
    }

    //------------------------------------------------------------------------------------------------------------------

    public function insert(): ?int
    {
        $item = $this->pack();
        unset($item['idx']);

        return Vss::db()->insertAndGetId($this->tableName(), $item);
    }
}
