<?php

namespace CryptoNoisesTezos\Syncs;

use CryptoNoisesTezos\Core\TezosAddress;
use CryptoNoisesTezos\Models;
use CryptoNoisesTezos\Vendors;
use CryptoCore\Vss\Vss;
use Exception;

class SyncTransactions implements LevelSyncInterface
{
    private ?string $lastErrorMessage = null;

    public bool $verbose = false;

    public function tzktRangePath(): string
    {
        return 'operations/transactions';
    }

    public function sync(int $level, ?array $items = null): ?int
    {
        try
        {
            if ($items === null)
            {
                $data = Vendors\Tzkt::GetBlockDetails($level);

                if ($data === null)
                    throw new Exception(self::kERROR_NoData);
            }
            else
            {
                // Transactions of this level loaded by LevelRangeLoader (the same objects as in the block response):
                // the block row is built from them, it is stored only for levels with transactions anyway
                $data = [
                    'level'         => $level,
                    'timestamp'     => $items[0]['timestamp'] ?? null,
                    'hash'          => $items[0]['block'] ?? null,
                    'transactions'  => $items,
                ];
            }

            $inItemsCount = count($data['transactions']);

            if ($inItemsCount == 0)
                return 0;

            $this->verbose("- items in: $inItemsCount");

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            Models\Log\Transactions::model()->prepareTable($level);
            Models\Log\Transactions::model()->prepareTable($level+10000);       // Create the next table in advance

            Vss::dbTezos()->transactionBegin();
            Vss::dbTezosLog()->transactionBegin();

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            $blItem = [];
            $blItem['level']        = $data['level'];
            $blItem['timestamp']    = $data['timestamp'];
            $blItem['hash']         = $data['hash'];

            Models\Blocks::model()->insert($blItem);

            $this->verbose("- block: #{$data['level']} : {$data['timestamp']} : {$data['hash']}");

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            foreach ($data['transactions'] as $item)
            {
                $trItem = [];
                $trItem['id']               = $item['id'];                          unset($item['id']);
                $trItem['level']            = $item['level'];                       unset($item['level']);
                $trItem['counter']          = $item['counter'];                     unset($item['counter']);
                $trItem['timestamp']        = $item['timestamp'];                   unset($item['timestamp']);
                unset($item['type']);
                $trItem['hash']             = $item['hash'];                        unset($item['hash']);
                $trItem['gasLimit']         = $item['gasLimit'];                    unset($item['gasLimit']);
                $trItem['gasUsed']          = $item['gasUsed'];                     unset($item['gasUsed']);
                $trItem['storageLimit']     = $item['storageLimit'];                unset($item['storageLimit']);
                $trItem['storageUsed']      = $item['storageUsed'];                 unset($item['storageUsed']);
                $trItem['bakerFee']         = $item['bakerFee'];                    unset($item['bakerFee']);
                $trItem['storageFee']       = $item['storageFee'];                  unset($item['storageFee']);
                $trItem['allocationFee']    = $item['allocationFee'];               unset($item['allocationFee']);
                $trItem['amount']           = $item['amount'];                      unset($item['amount']);
                $trItem['status']           = $item['status'];                      unset($item['status']);
                $trItem['hasInternals']     = (int) $item['hasInternals'];          unset($item['hasInternals']);
                $trItem['nonce']            = $item['nonce'];                       unset($item['nonce']);

                $trItem['token_transfers_count'] = $item['tokenTransfersCount'];
                unset($item['tokenTransfersCount']);

                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

                $trItem['parameter'] = json_encode($item['parameter']);

                if (!empty($item['parameter']) && isset($item['parameter']['entrypoint']))
                    $trItem['entrypoint'] = $item['parameter']['entrypoint'];

                unset($item['parameter']);
                unset($item['parameters']);

                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

                $trItem['initiator_address']    = $item['initiator']['address'];
                $trItem['initiator_adid']       = $this->getAddressAdid($item['initiator']['address']);

                $trItem['sender_address']       = $item['sender']['address'];
                $trItem['sender_adid']          = $this->getAddressAdid($item['sender']['address']);

                $trItem['target_address']       = $item['target']['address'];
                $trItem['target_adid']          = $this->getAddressAdid($item['target']['address']);

                unset($item['initiator']);
                unset($item['sender']);
                unset($item['target']);

                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

                if (isset($item['errors']))
                {
                    $trItem['errors']       = json_encode($item['errors']);         unset($item['errors']);
                }

                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

                // Remove unused data
                unset($item['block']);

                if (!empty($item))
                    $trItem['undefined_fields'] = json_encode($item);

                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

                $this->verbose("- transaction: #{$trItem['id']} : {$trItem['hash']}");

                Models\Log\Transactions::model()->insert($level, $trItem);

                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

                if ($trItem['initiator_address'] == null && $trItem['target_address'] != null && ($trItem['entrypoint'] ?? null) != null)
                {
                    Models\TransactionsEntrypoints::model()->fix($trItem['target_address'], $trItem['entrypoint'], $level);
                }
            }

            $outItemsCount = Models\Log\Transactions::model()->getItemsCountInBlock($level);

            if ($inItemsCount != $outItemsCount)
                throw new Exception("In/Out items count mismatch $inItemsCount <> $outItemsCount");

            Vss::dbTezos()->transactionCommit();
            Vss::dbTezosLog()->transactionCommit();

            $this->verbose("- found $outItemsCount transaction(s)");
            return $outItemsCount;
        }
        catch (Exception $e)
        {
            Vss::dbTezos()->transactionRollback();
            Vss::dbTezosLog()->transactionRollback();

            $this->lastErrorMessage = $e->getMessage();
            return null;
        }
    }

    //==================================================================================================================

    protected array $adidsCache = [];

    protected function getAddressAdid(?string $address)
    {
        if ($address === null)
            return null;

        if (!isset($this->adidsCache[$address]))
            $this->adidsCache[$address] = TezosAddress::GetOrCreateAdid($address);

        return $this->adidsCache[$address];
    }

    //==================================================================================================================

    public function getLastErrorMessage(): ?string
    {
        return $this->lastErrorMessage;
    }

    protected function verbose(string $message): void
    {
        if (!$this->verbose)
            return;

        echo get_class($this).": ".$message."\n";
        ob_flush();
    }
}
