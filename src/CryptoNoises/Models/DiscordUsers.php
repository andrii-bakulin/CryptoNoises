<?php

namespace CryptoNoises\Models;

use CryptoNoises\Config;
use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;
use CryptoCore\Vss\VssDbExp;

/**
 * Members of the project Discord server, synced by the Assistant thread (DiscordAssistantThread_ServerMembers).
 * ts_deactivated IS NULL = the user is on the server now.
 */
class DiscordUsers extends VssModel
{
    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'discord_users';
    }

    public function save(int $discordUserId, ?string $username, ?int $discriminator): void
    {
        $insertItem = [
            'discord_user_id'   => $discordUserId,
            'username'          => $username,
            'discriminator'     => $discriminator,
        ];

        $updateItem = [
            'username'          => $username,
            'discriminator'     => $discriminator,
            'ts_last_fixed'     => VssDbExp::now(),
            'ts_deactivated'    => null,
        ];

        Vss::db()->insertOrUpdate($this->tableName(), $insertItem, $updateItem);
    }

    public function isUserConnectedToServer(int $discordUserId): bool
    {
        return Vss::db()->queryScalar("
            SELECT COUNT(*)
            FROM {$this->tableName()}
            WHERE discord_user_id = :discord_user_id
                AND ts_deactivated IS NULL
        ", ['discord_user_id' => $discordUserId]) > 0;
    }

    public function fixDisconnectedUsers(): void
    {
        $sql = "
            UPDATE {$this->tableName()}
               SET ts_deactivated = NOW()
             WHERE ts_last_fixed < NOW() - INTERVAL :timeout SECOND
               AND ts_deactivated IS NULL
        ";

        Vss::db()->query($sql, ['timeout' => Config::kDiscord_AutoDisconnectFromServerTimeout]);
    }
}
