<?php

namespace CryptoNoises\Entities;

use CryptoNoises\Models;

class UserRedirect
{
    public bool $isEnabled = false;

    public ?int $userId = null;
    public ?string $redirectChannelId = null;

    //------------------------------------------------------------------------------------------------------------------

    private function __construct(?int $userId)
    {
        $this->userId = $userId;
    }

    public static function Get(int $userId): self
    {
        $item = Models\UsersRedirects::model()->getItemByUserId($userId);

        $instance = new self($userId);
        $instance->initialize($item);
        return $instance;
    }

    public static function GetByChannelId(int $channelId): ?self
    {
        $item = Models\UsersRedirects::model()->getItemByChannelId($channelId);

        if ($item === null)
            return null;

        $instance = new self($item['user_id']);
        $instance->initialize($item);
        return $instance;
    }

    //------------------------------------------------------------------------------------------------------------------

    private function initialize(?array $item): void
    {
        if ($item == null)
        {
            $this->isEnabled = false;
            return;

        }

        $this->isEnabled         = true;
        $this->redirectChannelId = $item['redirect_channel_id'];
    }

    //------------------------------------------------------------------------------------------------------------------

    public function activate(int $channelId, int $guildId): void
    {
        Models\UsersRedirects::model()->save($this->userId, $channelId, $guildId);

        $item = Models\UsersRedirects::model()->getItemByUserId($this->userId);

        $this->initialize($item);
    }

    public function deactivate(): void
    {
        Models\UsersRedirects::model()->removeItemByUserId($this->userId);

        $this->initialize(null);
    }

    public function deactivateExternal(int $channelId): void
    {
        Models\UsersRedirects::model()->removeItemByChannelId($channelId);
    }
}
