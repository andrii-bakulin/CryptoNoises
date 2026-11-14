<?php

namespace CryptoNoises\Models\Log;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class DiscordEvents extends VssModel
{
    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'log_discord_events';
    }

    public function save($item)
    {
        if (is_object($item['raw_data']) || is_array($item['raw_data']))
        {
            ksort($item['raw_data']);
            $item['raw_data'] = json_encode($item['raw_data']);
        }

        if (is_object($item['raw_data_prev']) || is_array($item['raw_data_prev']))
        {
            ksort($item['raw_data_prev']);
            $item['raw_data_prev'] = json_encode($item['raw_data_prev']);
        }

        return Vss::dbLog()->insertAndGetId($this->tableName(), $item);
    }

    public function removeItemsByGuildId(int $guildId): void
    {
        Vss::dbLog()->delete($this->tableName(), ['guild_id' => $guildId]);
    }
}
