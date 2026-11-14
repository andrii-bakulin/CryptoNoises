<?php

namespace CryptoNoises\Models\Log;

use CryptoCore\Time;
use Throwable;

class BotMessagesDiscordRecord
{
    private int $logId;
    private string $timePoint;
    private float $tsStart;

    public function __construct(string $timePoint, int $logId)
    {
        $this->logId = $logId;
        $this->timePoint = $timePoint;
        $this->tsStart = Time::Microtime();
    }

    public function done(bool $isSuccess): bool
    {
        if ($isSuccess)
            $this->doneSuccess();
        else
            $this->doneWithError(null);

        return $isSuccess;
    }

    public function doneSuccess(): void
    {
        BotMessagesDiscord::model()->update($this->timePoint, $this->logId, [
            'status'    => BotMessagesDiscord::kSTATUS_Ok,
            'exec_time' => Time::Microtime() - $this->tsStart,
        ]);
    }

    public function doneWithError(?Throwable $exception): void
    {
        BotMessagesDiscord::model()->update($this->timePoint, $this->logId, [
            'status'        => BotMessagesDiscord::kSTATUS_Error,
            'exec_time'     => Time::Microtime() - $this->tsStart,
            'error_message' => $exception ? $exception->getMessage() : null,
        ]);
    }
}
