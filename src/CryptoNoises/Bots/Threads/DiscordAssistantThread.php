<?php

namespace CryptoNoises\Bots\Threads;

use CryptoNoises\Config;
use CryptoCore\Time;
use CryptoCore\Vss\Vss;
use Discord\Discord;

class DiscordAssistantThread extends DiscordAbstractThread
{
    use DiscordAssistantThread_ServerMembers;
    use DiscordAssistantThread_Stats;

    const kWORKER_ID_ServerMembers             = 'ServerMembers';
    const kWORKER_ID_Stats                     = 'Stats';

    static ?array $ActiveWorkers = null;

    public function run(): bool
    {
        if ($this->createDiscordInstance() == false)
            return false;

        //--------------------------------------------------------------------------------------------------------------

        $this->discord->on('ready', function (Discord $discord)
        {
            $this->discord->getLogger()->notice("ASSISTANT READY");

            $this->stats_sync();
            $this->serverMembers_sync();

            $this->discord->getLoop()->addPeriodicTimer(Config::kDiscord_Assistant_ResyncStats, function ($timer)
            {
                $this->stats_sync();
            });

            $this->discord->getLoop()->addPeriodicTimer(Config::kDiscord_Assistant_ResyncTimeout, function ($timer)
            {
                $this->serverMembers_sync();
            });
        });

        $this->discord->run();
        return true;
    }

    //------------------------------------------------------------------------------------------------------------------

    private function isWorkerActive(string $workerId): bool
    {
        if (self::$ActiveWorkers === null || !isset(self::$ActiveWorkers[$workerId]))
        {
            $this->discord->getLogger()->debug("IsWorkerActive: $workerId? > NO");
            return false;
        }

        $this->discord->getLogger()->debug("IsWorkerActive: $workerId? > YES");
        return true;
    }

    private function activateWorker(string $workerId): void
    {
        if (self::$ActiveWorkers === null)
            self::$ActiveWorkers = [];

        self::$ActiveWorkers[$workerId] = Time::Microtime();

        $this->discord->getLogger()->debug("ActivateWorker: $workerId");
    }

    private function closeWorker(string $workerId): void
    {
        if (self::$ActiveWorkers === null)
            return; // should not happen

        $execTime = Time::Microtime() - self::$ActiveWorkers[$workerId];
        unset(self::$ActiveWorkers[$workerId]);

        $this->discord->getLogger()->notice("CloseWorker: $workerId (".number_format($execTime, 3)." sec)");

        if (!empty(self::$ActiveWorkers))
            return;

        self::$ActiveWorkers = null;
        Vss::dbCloseAll();

        $this->discord->getLogger()->notice("CloseAllDB");
    }
}
