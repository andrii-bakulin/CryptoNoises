<?php

namespace CryptoNoises\Jobs;

use CryptoCore\Enum\JobResult;
use CryptoCore\Time;
use CryptoNoises\Config;
use CryptoNoises\Models;
use Exception;

class DomainsExpiryFindNew extends AbstractJob
{
    public function execute(array $taskItem): JobResult
    {
        $this->initJob();

        try
        {
            $items = \CryptoNoises\GatewayTezos\Models\DomainsExpiry::model()->getDomainsExpiringBetween(-3600, 30*86400);

            $newDomains = 0;
            $totalDomains = count($items);

            foreach ($items as $item)
            {
                $domain         = $item['domain'];
                $secondsLeft    = strtotime($item['ts_expired']) - Time::Time();
                $hoursScope     = $this->getHoursScope($secondsLeft);

                if ($hoursScope === null)
                    continue;

                if (Models\FixerDomainsExpiry::model()->fix($domain, $hoursScope) == false)
                    continue;

                Models\Queues\QueueDomainsExpiry::model()->push([
                    'network_id' => Config::kNETWORK_Tezos,
                    'domain'     => $domain,
                    'ts_expired' => $item['ts_expired'],
                ]);
                $newDomains++;
            }

            $this->logMsg("Found $newDomains new domains out of $totalDomains");

            return JobResult::Completed();
        }
        catch (Exception $e)
        {
            $this->lastActionNotice = $e->getMessage();

            return JobResult::FailedWithCode($e->getCode());
        }
    }

    private function getHoursScope(int $secondsLeft): ?int
    {
        if ($secondsLeft <=         0)      return 0;

        if ($secondsLeft <=  3 * 3600)      return 3;
        if ($secondsLeft <=  6 * 3600)      return 6;
        if ($secondsLeft <= 12 * 3600)      return 12;
        if ($secondsLeft <= 24 * 3600)      return 24;

        if ($secondsLeft <=  2 * 86400)     return 48;
        if ($secondsLeft <=  3 * 86400)     return 72;
        if ($secondsLeft <=  5 * 86400)     return 120;
        if ($secondsLeft <= 10 * 86400)     return 240;
        if ($secondsLeft <= 15 * 86400)     return 360;
        if ($secondsLeft <= 30 * 86400)     return 720;

        return null;
    }
}
