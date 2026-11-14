<?php

namespace CryptoNoises\Jobs;

use CryptoCore\Enum\JobResult;
use CryptoCore\Time;
use CryptoNoises\Config;
use CryptoNoises\Core\CustomData;
use CryptoNoises\Entities\EventFlow;
use CryptoNoises\Entities\UserDomains;
use CryptoNoises\Enum;
use CryptoNoises\Networks\Tezos\TezosNetworkParser;
use Exception;

class DomainsExpiry extends AbstractJob
{
    public static ?int $uniqueId = null;
    public static ?int $uniqueSubId = 0;

    public function execute(array $taskItem): JobResult
    {
        if (self::$uniqueId === null)
            self::$uniqueId = Time::Time();

        $this->initJob();

        try
        {
            $networkId     = $taskItem['network_id'];

            $domain         = $taskItem['domain'];
            $tsExpired      = $taskItem['ts_expired'];
            $secondsLeft    = strtotime($tsExpired) - Time::Time();

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            if ($networkId == Config::kNETWORK_Tezos)
            {
                $userIds = UserDomains::GetUserIdsByTezosDomain($domain);

                if (count($userIds) == 0)
                    throw new Exception("No users for this domain, ignoring", JobResult::kFailedAndClose);

                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

                $event = new EventFlow();
                $event->initInTezosNetwork(EventFlow::UNDEFINED_NETWORK_BLOCK, self::$uniqueId, ++self::$uniqueSubId);
                $event->transaction_event       = Time::Now();

                $event->event_type              = $secondsLeft > 0
                    ? Enum\EventFlowType::DomainExpires
                    : Enum\EventFlowType::DomainExpired;

                $event->userIds                 = $userIds;
                $event->custom_data             = [
                    CustomData::kDOMAIN             => $domain,
                    CustomData::kEXPIRED            => $tsExpired,
                    CustomData::kDOMAIN_RENEW_URL   => 'https://app.tezos.domains/domain/'.$domain,
                ];
            }
            else throw new Exception("Unknown networkId=[$networkId]");

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            $parser = new TezosNetworkParser();
            $parser->verbose = $this->verbose;
            $parser->pushEvent($event);
            $parser->save();

            return JobResult::Completed();
        }
        catch (Exception $e)
        {
            $this->lastActionNotice = $e->getMessage();

            return JobResult::FailedWithCode($e->getCode());
        }
    }
}
