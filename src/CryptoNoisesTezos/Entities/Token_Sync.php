<?php

namespace CryptoNoisesTezos\Entities;

use CryptoNoisesTezos\Entities;
use CryptoNoisesTezos\Models;

trait Token_Sync
{
    /**
     * This method called when [new] metadata-uri detected for token
     *      At this moment token be new (mint event) OR may be existed in DB (update metadata event)
     *
     * @param string|null $metadataUri
     * @param int $bigmap
     * @param int $bigmapExkey
     * @param int $level
     * @param string|null $notice
     * @return bool
     */
    public function open(?string $metadataUri, int $bigmap, int $bigmapExkey, int $level, ?string $notice = null): bool
    {
        $requireOpenQueueTokensMetadata = false;
        $syncMode = null;

        $token = Entities\Token::GetByAdidAndTokenUnid($this->contractAdid, $this->tokenUnid);

        if ($token === null)
        {
            $openData = [
                'bigmap'            => $bigmap,
                'bigmap_exkey'      => $bigmapExkey,
                'level_last'        => $level,
            ];

            if (empty($metadataUri))
            {
                $openData['metadata_state'] = Models\Tokens::kMETADATA_STATE_Empty;
                $openData['metadata_uri']   = null;
            }
            else
            {
                $openData['metadata_state'] = Models\Tokens::kMETADATA_STATE_Idle;
                $openData['metadata_uri']   = $metadataUri;

                $requireOpenQueueTokensMetadata = true;
                $syncMode = Models\Queues\QueueAbstract::kSyncMode_New;
            }

            if ($notice !== null)
                $openData['error_msg'] = $notice;

            $didOpen = Models\Tokens::model()->open($this->contractAdid, $this->contractAddress, $this->tokenUnid, $openData);

            if ($didOpen)
                Models\Contracts::model()->incTokensCounter($this->contractAdid, $this->contractAddress);
        }
        else
        {
            $updateData = [];

            if (empty($metadataUri))
            {
                $updateData['metadata_state'] = Models\Tokens::kMETADATA_STATE_Empty;
                $updateData['metadata_uri']   = null;
            }
            else
            {
                if ($token->metadataUri != $metadataUri)
                {
                    $updateData['metadata_state'] = Models\Tokens::kMETADATA_STATE_Idle;
                    $updateData['metadata_uri']   = $metadataUri;

                    $syncMode = Models\Queues\QueueAbstract::kSyncMode_Update;
                    $requireOpenQueueTokensMetadata = true;
                }
                else
                {
                    $syncMode = null;
                    $requireOpenQueueTokensMetadata = true; // same URI: rescan anyway
                }
            }

            if ($token->levelLast < $level)
                $updateData['level_last'] =  $level;

            if ($notice !== null)
                $updateData['error_msg'] = $notice;

            if (!empty($updateData))
                $token->update($updateData);
        }

        if ($requireOpenQueueTokensMetadata)
        {
            Models\Queues\QueueTokensMetadata::model()->push([
                'contract_adid'     => $this->contractAdid,
                'token_unid'        => $this->tokenUnid,
            ], $syncMode);
        }

        return true;
    }

    public function closeSyncProcess(?string $syncMode): void
    {
        switch ($syncMode)
        {
            case Models\Queues\QueueAbstract::kSyncMode_New:
                $this->fixEvent_tokenDidMint();
                break;

            case Models\Queues\QueueAbstract::kSyncMode_Update:
                $this->fixEvent_tokenDidUpdate();
                break;
        }
    }
}