<?php

namespace CryptoNoises\Commands;

use CryptoNoises\Bots\Threads\TelegramCommandsThread;

class Commands extends AbstractCommand
{
    public function run(): void
    {
        $runCmd = realpath($_SERVER['_'] ?? $_SERVER['argv'][0] ?? '') ?: 'commands/run';

        echo "- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -"."\n\n";

        echo $runCmd." /tezos-network/analyze-transactions/new          verbose=1"."\n";
        echo $runCmd." /tezos-network/analyze-transactions/range        verbose=1 skip_exists=1 level={xxx|xxx-xxx|non-synced} "."\n";
        echo $runCmd." /tezos-network/analyze-transactions/non-synced"."\n";
        echo "\n";

        echo $runCmd." /tezos-network/wallets/sync                      verbose=1"."\n";
        echo "\n";

        echo $runCmd." /events/nfts-mint/tezos                          verbose=1"."\n";
        echo $runCmd." /events/nfts-updates/tezos                       verbose=1"."\n";
        echo "\n";

        echo "- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -"."\n\n";

        echo $runCmd." /jobs/exec                                       verbose=1 jobId=..."."\n";
        echo $runCmd." /jobs/recover-dead-jobs"."\n";
        echo "\n";

        echo $runCmd." /lifecycle/check/error-logs"."\n";
        echo $runCmd." /lifecycle/clean-up/callback-actions"."\n";
        echo $runCmd." /lifecycle/clean-up/events-flow"."\n";
        echo $runCmd." /lifecycle/clean-up/user-actions"."\n";
        echo $runCmd." /lifecycle/clean-up/old-data"."\n";
        echo $runCmd." /lifecycle/prepare/tables"."\n";
        echo "\n";

        echo "- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -"."\n\n";

        echo $runCmd." /telegram/notifier/events-flow                   verbose=1"."\n";
        echo $runCmd." /telegram/notifier/inform-flow                   verbose=1"."\n";
        echo "\n";
        echo $runCmd." /telegram/webhook/set"."\n";
        echo $runCmd." /telegram/webhook/info"."\n";
        echo "\n";

        echo "- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -"."\n\n";

        echo $runCmd." /discord/bot/stop/main-loop"."\n";
        echo $runCmd." /discord/bot/stop/notifier"."\n";
        echo $runCmd." /discord/bot/stop/assistant"."\n";
        echo $runCmd." /discord/bot/stop/all"."\n";
        echo "\n";
        echo $runCmd." /discord/bot/start/main-loop &"."\n";
        echo $runCmd." /discord/bot/start/notifier  &"."\n";
        echo $runCmd." /discord/bot/start/assistant &"."\n";
        echo "\n";
        echo $runCmd." /discord/bot/commands/show"."\n";
        echo $runCmd." /discord/bot/commands/create [only-commands=command,command,...]"."\n";
        echo $runCmd." /discord/bot/commands/delete [only-commands=command,command,...]"."\n";
        echo "\n";

        echo "- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -"."\n\n";

        echo $runCmd." /coingecko/sync"."\n";
        echo "\n";

        echo "- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -"."\n\n";

        echo $runCmd." /utils/generate-telegram-bot-commands"."\n";
        echo "\n";


        echo "- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -"."\n\n";
    }

    public function generateTelegramBotCommands(): void
    {
        $this->verbose = 1;

        $this->logMsg('Open Telegram -> BotFather -> choose-bot -> Edit Bot -> Edit Commands');
        $this->logMsg('');

        $commands = TelegramCommandsThread::GenerateCommandsList();

        foreach ($commands as $cmd => $msg)
        {
            $cmd = ltrim($cmd, '/');
            $this->logMsg("$cmd - $msg");
        }
    }
}
