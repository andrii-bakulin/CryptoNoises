<?php

namespace CryptoNoises\Commands;

use CryptoNoises\Config;
use CryptoNoises\Core\Log;
use CryptoNoises\Models;
use CryptoNoises\Networks;

class CoinGecko extends AbstractCommand
{
    public function sync(): void
    {
        set_time_limit(Config::kJOBS_TIMEOUT_DEFAULT);

        if (!$this->checkLockAndWaitUntilNoOtherInstanceRuns('command-lock.coin-gecko.sync', Config::kJOBS_TIMEOUT_DEFAULT))
            return;

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $coins = Models\CurrencyIn::model()->selectEnabled();

        foreach ($coins as $coinItem)
        {
            $this->logMsg("Sync: {$coinItem['currency_in_id']} / {$coinItem['coin_gecko_id']}");

            $data = Networks\Globals\CoinGecko::GetCoinDetails($coinItem['coin_gecko_id']);

            if ($data == null)
                continue; // CoinGecko sometimes fails to respond; skip this coin

            if (!isset($data['market_data']['current_price']))
            {
                $this->logErr("{$coinItem['coin_gecko_id']}: no current_price data");
                continue;
            }

            foreach ($data['market_data']['current_price'] as $currencyId => $value)
            {
                $currencyInId   = $coinItem['currency_in_id'];
                $currencyOutId  = strtoupper($currencyId);

                $currencyItem = Models\CurrencyOut::model()->getItemById($currencyOutId);
                if ($currencyItem == null)
                {
                    Log::Error(get_class($this), __FUNCTION__, "CurrencyOut record does not exist [$currencyOutId] = [$value]");
                    continue;
                }

                Models\CurrencyValues::model()->save($currencyInId, $currencyOutId, $value);

                $this->logMsg("- $currencyInId > $currencyOutId > $value");
            }
        }
    }
}
