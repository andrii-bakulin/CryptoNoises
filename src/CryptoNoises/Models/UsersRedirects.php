<?php

namespace CryptoNoises\Models;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class UsersRedirects extends VssModel
{
    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'users_redirects';
    }

    public function getItemByUserId(int $userId): ?array
    {
        return Vss::db()->selectRow('*', $this->tableName(), ['user_id' => $userId]);
    }

    public function getItemByChannelId(int $channelId): ?array
    {
        return Vss::db()->selectRow('*', $this->tableName(), ['redirect_channel_id' => $channelId]);
    }

    //------------------------------------------------------------------------------------------------------------------

    public function save(int $userId, int $channelId, int $guildId): void
    {
        Vss::db()->insert($this->tableName(), [
            'user_id'             => $userId,
            'redirect_channel_id' => $channelId,
            'guild_id'            => $guildId,
        ], 'IGNORE');
    }

    public function removeItemByUserId(int $userId): void
    {
        Vss::db()->delete($this->tableName(), ['user_id' => $userId]);
    }

    public function removeItemByChannelId(int $channelId): void
    {
        Vss::db()->delete($this->tableName(), ['redirect_channel_id' => $channelId]);
    }

    public function removeItemsByGuildId(int $guildId): void
    {
        Vss::db()->delete($this->tableName(), ['guild_id' => $guildId]);
    }
}
