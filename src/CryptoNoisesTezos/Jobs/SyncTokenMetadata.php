<?php

namespace CryptoNoisesTezos\Jobs;

use CryptoNoisesTezos\Analyzers;
use CryptoNoisesTezos\Core\Bigmap;
use CryptoNoisesTezos\Entities;
use CryptoNoisesTezos\Models;
use CryptoCore\Enum\JobResult;
use Exception;

class SyncTokenMetadata extends AbstractJob
{
    public function execute(array $taskItem): JobResult
    {
        $this->initJob();

        $contractAdid = $taskItem['contract_adid'];
        $tokenUnid    = $taskItem['token_unid'];

        $token = Entities\Token::GetByAdidAndTokenUnid($contractAdid, $tokenUnid);

        if ($token === null)
        {
            $this->lastActionNotice = 'Cannot find token-item';
            return JobResult::FailedAndClose();
        }

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        try
        {
            if ($token->metadataUri == '')
            {
                $this->lastActionNotice = 'skip: metadata_uri is empty';

                $token->update([
                    'metadata_state' => Models\Tokens::kMETADATA_STATE_Empty,
                    'error_msg'      => $this->lastActionNotice,
                ]);
                return JobResult::Completed();
            }

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            $token->update([
                'metadata_state' => Models\Tokens::kMETADATA_STATE_Syncing,
            ]);

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            if (preg_match('/^tezos-bigmaps:direct:(.+)$/iu', $token->metadataUri, $m) > 0)
            {
                $json = self::ExtractMetadataJsonFromTokenMetadata($token, $m[1]);
            }
            else if (preg_match('/^tezos-storage:(.+)$/iu', $token->metadataUri, $m) > 0)
            {
                $json = self::ExtractMetadataJsonFromTezosStorage($token->bigmap, $m[1]);
            }
            else
            {
                switch ($token->metadataUri)
                {
                    case Analyzers\Tokens\EightBidou::kCRYPTONOISES_MARKER_METADATA:
                        $json = Analyzers\Tokens\EightBidou::GetJsonMetadata($token);
                        break;

                    case Analyzers\Tokens\TezosDomains::kCRYPTONOISES_MARKER_METADATA:
                        $json = Analyzers\Tokens\TezosDomains::GetJsonMetadata($token);
                        break;

                    default:
                        $json = self::DownloadJson($token->metadataUri, $taskItem['queue_task_id']);
                        break;
                }
            }

            if (!is_array($json))
                throw new Exception('Metadata JSON is not an object', JobResult::kFailedAndPostpone);

            $token->analyzeJson($json);
            $token->saveTokenAndRelatedData([
                'metadata_state' => Models\Tokens::kMETADATA_STATE_Success,
                'preview_state'  => Models\Tokens::kPREVIEW_STATE_Idle,
                'error_msg'      => null,
            ]);

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            Models\Queues\QueueIpfsPrimary::model()->push([
                'contract_adid' => $token->contractAdid,
                'token_unid'    => $token->tokenUnid,
            ], $taskItem['sync_mode']);

            return JobResult::Completed();
        }
        catch (Exception $e)
        {
            $this->lastActionNotice = $e->getMessage();

            $token->update([
                'metadata_state'    => Models\Tokens::kMETADATA_STATE_Error,
                'error_msg'         => $this->getJobId().': '.$e->getMessage(),
            ]);

            return JobResult::FailedWithCode($e->getCode());
        }
    }

    /**
     * @param Entities\Token $token
     * @param string|null $tokenInfoKey
     * @return array|null
     * @throws Exception
     */
    private static function ExtractMetadataJsonFromTokenMetadata(Entities\Token $token, ?string $tokenInfoKey): ?array
    {
        $value = Bigmap::GetValueByBigmapAndExkey($token->bigmap, $token->bigmapExkey);

        if ($value === null)
            throw new Exception('Cannot find bigmap record', JobResult::kFailedAndClose);

        if ($tokenInfoKey !== '~')
        {
            $value = $value[$tokenInfoKey];

            // nested values are hex-encoded: decode them
            foreach (Entities\Token::$TOKEN_FIELDS as $key)
            {
                if (isset($value[$key]) == false)
                    continue;

                $valOrigin = $value[$key];
                $valParsed = $token->unpackHex2Bin($valOrigin);

                if ($valParsed)
                {
                    $valDecoded = json_decode($valParsed, true); // arrays: Token_Metadata::analyzeJson() expects them

                    $value[$key] = json_last_error() === JSON_ERROR_NONE ? $valDecoded : $valParsed;
                }
                else
                {
                    $value[$key] = $valParsed;
                }
            }
        }

        return is_array($value) ? $value : null;
    }
}
