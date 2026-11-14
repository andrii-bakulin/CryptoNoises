<?php

namespace CryptoNoisesTezos\Jobs;

use CryptoNoisesTezos\Core\TezosAddress;
use CryptoNoisesTezos\Models;
use CryptoNoisesTezos\Vendors\Ipfs;
use CryptoCore\Enum\JobResult;
use CryptoCore\Vss\VssDbExp;
use Exception;

class SyncContractMetadata extends AbstractJob
{
    public function execute(array $taskItem): JobResult
    {
        $this->initJob();

        $bigmap       = $taskItem['bigmap'];
        $contractAdid = $taskItem['contract_adid'];
        $metadataUri  = $taskItem['metadata_uri'];

        try
        {
            if ($metadataUri == '')
                throw new Exception("metadataUri is empty", JobResult::kFailedAndClose);

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            Models\Contracts::model()->update($contractAdid, [
                'metadata_state' => Models\Contracts::kMETADATA_STATE_Syncing,
                'error_msg'      => null,
            ]);

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            if (preg_match('/^tezos-storage:(.+)$/iu', $metadataUri, $m) > 0)
            {
                $subValue = $m[1];

                // Here may be 2 variants:
                // 1) tezos-storage://KT1XvKNGHnAVTFzxoiXtQKcmqWuiEEVT1Eyz/metadataKey  -> use 'metadataKey' from bigmap 'KT1XvKNGHnAVTFzxoiXtQKcmqWuiEEVT1Eyz'
                // 2) tezos-storage:metadataKey                                         -> use 'metadataKey' in the same bigmap

                if (preg_match('#^//(KT1[a-z0-9]+)/(.+)#iu', $subValue, $sub_m) > 0)
                {   # 1)
                    $newContract = $sub_m[1];
                    $newKey      = $sub_m[2];

                    $newBigmapItem = Models\Bigmaps::model()->getItemByType($newContract, Models\Bigmaps::kTYPE_Metadata);

                    if ($newBigmapItem === null)
                        throw new Exception('Cannot find sub-bigmap-record with metadata-type', JobResult::kFailedAndClose);

                    $json = self::ExtractMetadataJsonFromTezosStorage($newBigmapItem['bigmap'], $newKey);
                }
                else
                {   # 2)
                    $json = self::ExtractMetadataJsonFromTezosStorage($bigmap, $subValue);
                }
            }
            else if(preg_match('#^(ipfs|https|http)://#iu', $metadataUri, $m) > 0)
            {
                $json = self::DownloadJson($metadataUri, $taskItem['queue_task_id']);
            }
            else
            {
                throw new Exception('Unknown metadataUri structure');
            }

            // Missing storage key or non-object JSON: retrying won't help, don't mark empty metadata as success
            if (!is_array($json))
                throw new Exception('Metadata JSON is empty or not an object', JobResult::kFailedAndClose);

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            $item = [];
            $item['name']               = self::GetSafeString($json['name'] ?? null, 255);
            $item['description']        = self::GetSafeString($json['description'] ?? null, 8192);
            $item['homepage']           = self::GetSafeString($json['homepage'] ?? null, 255);

            $imageUri                   = is_string($json['imageUri'] ?? null) ? $json['imageUri'] : null;

            $item['image_uri']          = self::GetSafeString($imageUri, 255);
            $item['image_url']          = self::GetSafeString(Ipfs::ConvertUriToUrl($imageUri), 255);

            $item['interfaces']         = self::GetSafeString(!empty($json['interfaces']) ? json_encode($json['interfaces']) : null, 1024);

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            $authors = $json['authors'] ?? null;

            if (is_array($authors) && count($authors) == 1)
            {
                $author = reset($authors);

                if (TezosAddress::IsWalletAddressValid($author) || TezosAddress::IsContractAddressValid($author))
                {
                    $item['author_address'] = $author;
                    $item['authors']        = null;
                }
            }
            else if (is_string($authors))
            {
                $author = $authors;

                if (TezosAddress::IsWalletAddressValid($author) || TezosAddress::IsContractAddressValid($author))
                {
                    $item['author_address'] = $author;
                    $item['authors']        = null;
                }
            }
            else if ($authors)
            {
                $item['authors'] = json_encode($authors);
            }

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
            // Post logic!

            if ($item['homepage'] == 'https://objkt.com/collections')
            {
                // NULL -> 0, otherwise keep the current tokens_count
                $item['tokens_count'] = VssDbExp::query('COALESCE(tokens_count,0)');
            }

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            $item['metadata_state'] = Models\Contracts::kMETADATA_STATE_Success;

            Models\Contracts::model()->update($contractAdid, $item);

            return JobResult::Completed();
        }
        catch (Exception $e)
        {
            $this->lastActionNotice = $e->getMessage();

            Models\Contracts::model()->update($contractAdid, [
                'metadata_state' => Models\Contracts::kMETADATA_STATE_Error,
                'error_msg'      => $this->getJobId().': '.self::GetSafeString($this->lastActionNotice, 255),
            ]);

            return JobResult::FailedWithCode($e->getCode());
        }
    }
}
