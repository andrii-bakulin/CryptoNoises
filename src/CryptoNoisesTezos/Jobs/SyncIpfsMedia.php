<?php

namespace CryptoNoisesTezos\Jobs;

use CryptoNoisesTezos\Analyzers;
use CryptoNoisesTezos\Entities;
use CryptoNoisesTezos\Entities\IpfsMedia;
use CryptoNoisesTezos\Models;
use CryptoCore\Enum\JobResult;
use Exception;

class SyncIpfsMedia extends AbstractJob
{
    public function execute(array $taskItem): JobResult
    {
        $this->initJob();

        if ($taskItem['contract_adid'] !== null && $taskItem['token_unid'] !== null)
            return $this->syncMediaForToken($taskItem);

        if ($taskItem['media_uri'] !== null)
            return $this->syncMediaByDirectUri($taskItem);

        // Neither token nor media_uri: nothing to sync. Close the task instead of killing the worker.
        $this->lastActionNotice = 'Task has neither token nor media_uri';
        return JobResult::FailedAndClose();
    }

    //==================================================================================================================

    private function syncMediaForToken(array $taskItem): JobResult
    {
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
            if ($token->displayUri)
                $previewUri = $token->displayUri;
            elseif ($token->artifactUri)
                $previewUri = $token->artifactUri;
            else if ($token->thumbnailUri)
                $previewUri = $token->thumbnailUri;
            else
            {
                $token->update([
                    'preview_state' => Models\Tokens::kPREVIEW_STATE_Empty,
                    'error_msg'     => null,
                ]);

                $this->lastActionNotice = 'Media-uri(s) are empty';

                return $this->jobWillFinishWithResult($taskItem, $token, JobResult::FailedAndClose());
            }

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            $token->update([
                'preview_state' => Models\Tokens::kPREVIEW_STATE_Syncing,
            ]);

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            /** @var IpfsMedia $ipfsMedia */
            $ipfsMedia = IpfsMedia::Get($previewUri);

            if ($ipfsMedia === null ||
                $ipfsMedia->mediaType == IpfsMedia::kTYPE_NotSupported) // resync!
            {
                if (strpos($previewUri, Analyzers\Tokens\EightBidou::kCRYPTONOISES_MARKER_PREVIEW) === 0)
                    $ipfsMedia = Analyzers\Tokens\EightBidou::CreatePreview($token, $previewUri);

                if ($ipfsMedia === null)
                {
                    $ipfsMedia = IpfsMedia::Dummy($previewUri);
                    $ipfsMedia->download($taskItem['queue_task_id']);
                }

                if ($ipfsMedia->lastResult === false)
                    throw new Exception("Failed to download data: ".$ipfsMedia->lastActionNotice, JobResult::kFailedAndPostpone);

                if ($ipfsMedia->lastActionNotice)
                    $this->lastActionNotice = "$previewUri: ".$ipfsMedia->lastActionNotice;
                else
                    $this->lastActionNotice = "$previewUri: "."Done for {$token->contractAddress}:{$token->tokenId}";
            }

            $token->update([
                'preview_state' => $ipfsMedia instanceof Entities\IpfsMediaTeaser
                    ? Models\Tokens::kPREVIEW_STATE_Teaser
                    : Models\Tokens::kPREVIEW_STATE_Success,
                'preview_uri'   => $ipfsMedia->ipfsUri,
                'error_msg'     => null,
            ]);

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            return $this->jobWillFinishWithResult($taskItem, $token, JobResult::Completed());
        }
        catch (Exception $e)
        {
            $this->lastActionNotice = $e->getMessage();

            $token->update([
                'preview_state' => Models\Tokens::kPREVIEW_STATE_Error,
                'error_msg'     => $this->getJobId().': '.$e->getMessage(),
            ]);

            return $this->jobWillFinishWithResult($taskItem, $token, JobResult::FailedWithCode($e->getCode()));
        }
    }

    private function jobWillFinishWithResult(array $taskItem, Entities\Token $token, JobResult $jobResult): JobResult
    {
        $iteration = $taskItem['iteration'];
        $maxIterationIndex = 3;

        // The event is published once per token (events_token_mint / events_token_updates), so calling
        // closeSyncProcess() again is safe. This also covers a success after the failure threshold and an iteration
        // moved forward by the zombie recovery.
        if ($jobResult->code() == JobResult::kCompleted)
        {
            $token->closeSyncProcess($taskItem['sync_mode']);
        }
        else if ($iteration >= $maxIterationIndex)
        {
            // Publish the mint / metadata-update event after $maxIterationIndex tries, even without a preview
            $token->closeSyncProcess($taskItem['sync_mode']);
        }

        return $jobResult;
    }

    //==================================================================================================================

    private function syncMediaByDirectUri(array $taskItem): JobResult
    {
        $mediaUri = $taskItem['media_uri'];

        try
        {
            /** @var IpfsMedia $ipfsMedia */
            $ipfsMedia = IpfsMedia::Dummy($mediaUri);
            $ipfsMedia->download($taskItem['queue_task_id']);

            if ($ipfsMedia->lastResult === false)
                throw new Exception("Failed to download data: ".$ipfsMedia->lastActionNotice, JobResult::kFailedAndPostpone);

            if ($ipfsMedia->lastActionNotice)
                $this->lastActionNotice = "$mediaUri: ".$ipfsMedia->lastActionNotice;
            else
                $this->lastActionNotice = "$mediaUri: "."Done";

            return JobResult::Completed();
        }
        catch (Exception $e)
        {
            $this->lastActionNotice = $e->getMessage();

            return JobResult::FailedWithCode($e->getCode());
        }
    }
}
