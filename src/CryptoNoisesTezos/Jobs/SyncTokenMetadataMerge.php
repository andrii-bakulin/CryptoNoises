<?php

namespace CryptoNoisesTezos\Jobs;

use CryptoNoisesTezos\Models;
use CryptoCore\Enum\JobResult;
use Exception;

class SyncTokenMetadataMerge extends AbstractJob
{
    public function execute(array $taskItem): JobResult
    {
        $this->initJob();

        $contractAdid = $taskItem['contract_adid'];
        $tokenUnid    = $taskItem['token_unid'];

        try
        {
            $updateToken         = [];
            $updateRefCollection = [];

            $appendData = Models\TokensAppend::model()->getItem($contractAdid, $tokenUnid);

            if ($appendData == null)
                return JobResult::Completed(); // Nothing to sync

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
            // Token not stored yet: postpone the merge

            if (Models\Tokens::model()->getItem($contractAdid, $tokenUnid) == null)
                throw new Exception('Token record not found yet', JobResult::kFailedAndPostpone);

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            foreach ($appendData as $key => $value)
            {
                // @checkpoint:token-append:new-field
                // All field will be copy automatically here!
                // So names should be the same!

                if (in_array($key, ['contract_adid', 'token_unid']))
                    continue;

                if ($value === null)
                    continue;

                if (in_array($key, ['collection_group', 'collection_id']))
                    $updateRefCollection[$key] = $value; // but also save in token data! continue...

                $updateToken[$key] = $value;
            }

            if ($updateToken)
                Models\Tokens::model()->update($contractAdid, $tokenUnid, $updateToken);

            if ($updateRefCollection)
                Models\TokensRefCollection::model()->save($contractAdid, $tokenUnid, $updateRefCollection);

            Models\TokensAppend::model()->delete($contractAdid, $tokenUnid);

            return JobResult::Completed();
        }
        catch (Exception $e)
        {
            $this->lastActionNotice = $e->getMessage();

            return JobResult::FailedWithCode($e->getCode());
        }
    }
}
