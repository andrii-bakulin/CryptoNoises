<?php

namespace CryptoNoisesTezos\Commands;

class Commands extends AbstractCommand
{
    public function run(): void
    {
        $runCmd = realpath($_SERVER['_'] ?? $_SERVER['argv'][0] ?? '') ?: 'commands/run-tezos';

        echo "- - - - - - - - - - - - - - - - - - - - - - - - - - - - - -" . "\n\n";

        echo $runCmd . " /blockchain/transaction/sync-new         verbose=0 [limit=1000000]" . "\n";
        echo $runCmd . " /blockchain/transaction/sync-range       verbose=0 level={value | value-value}" . "\n";
        echo $runCmd . " /blockchain/transaction/cleanup          offset=50000" . "\n";
        echo "\n";

        echo $runCmd . " /blockchain/bigmap-updates/sync-new      verbose=0 [limit=1000000]" . "\n";
        echo $runCmd . " /blockchain/bigmap-updates/sync-range    verbose=0 level={ number | number-number }" . "\n";
        echo $runCmd . " /blockchain/bigmap-updates/sync-check    verbose=0 thread-id= offset=". "\n";
        echo $runCmd . " /blockchain/bigmap-updates/cleanup       offset=50000". "\n";
        echo "\n";

        echo $runCmd . " /analyze/transactions/new                verbose=0 " . "\n";
        echo $runCmd . " /analyze/transactions/range              verbose=0 [limit=0] level={value | value-value | value-max} [hunterOnly=__]" . "\n";
        echo "\n";

        echo $runCmd . " /analyze/bigmap-updates/new              verbose=0 [limit=1000]" . "\n";
        echo "\n";

        echo "- - - - - - - - - - - - - - - - - - - - - - - - - - - - - -" . "\n\n";

        echo $runCmd . " /jobs/exec                 verbose=0 [threadIdx=0] [threadCnt=1] jobId=[...]" . "\n";
        echo "\n";
        echo $runCmd . " /jobs/clean/file-cache" . "\n";
        echo $runCmd . " /jobs/clean/media-convert-zombies" . "\n";
        echo $runCmd . " /jobs/recover-dead-jobs" . "\n";
        echo "\n";

        echo "- - - - - - - - - - - - - - - - - - - - - - - - - - - - - -" . "\n\n";

        echo $runCmd . " /recovery/bigmaps/fix-null-deal-records            bigmap=.. limit=1000" . "\n";
        echo $runCmd . " /recovery/bigmaps/fix-null-offer-records           bigmap=.. limit=1000" . "\n";
        echo "\n";

        echo "- - - - - - - - - - - - - - - - - - - - - - - - - - - - - -" . "\n\n";
    }
}
