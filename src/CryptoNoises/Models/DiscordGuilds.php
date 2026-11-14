<?php

namespace CryptoNoises\Models;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class DiscordGuilds extends VssModel
{
    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'discord_guilds';
    }

    public function fixJoin(int $guildId, ?string $guildName, ?int $memberCount): void
    {
        $data = [
            'guild_id'      => $guildId,
            'guild_name'    => $guildName,
            'member_count'  => $memberCount,
        ];

        Vss::db()->insert($this->tableName(), $data, 'IGNORE');
    }

    public function fixUpdate(int $guildId, ?string $guildName, ?int $memberCount): void
    {
        $data = [
            'guild_name'    => $guildName,
            'member_count'  => $memberCount,
        ];

        Vss::db()->update($this->tableName(), $data, ['guild_id' => $guildId]);
    }

    public function fixLeave(int $guildId): void
    {
        Vss::db()->delete($this->tableName(), ['guild_id' => $guildId]);
    }
}
