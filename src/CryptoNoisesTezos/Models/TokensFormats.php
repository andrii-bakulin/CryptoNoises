<?php

namespace CryptoNoisesTezos\Models;

use CryptoNoisesTezos\Entities\TokenFormat;
use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class TokensFormats extends VssModel
{
    use Tokens_Cleanup;

    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'tokens_formats';
    }

    /**
     * @param int $contractAdid
     * @param int $tokenUnid
     * @param TokenFormat[] $records
     * @return void
     */
    public function saveRecords(int $contractAdid, int $tokenUnid, array $records): void
    {
        $this->cleanUp($contractAdid, $tokenUnid);

        foreach ($records as $record)
        {
            Vss::dbTezos()->insert($this->tableName(), [
                'contract_adid'     => $contractAdid,
                'token_unid'        => $tokenUnid,
                'uri'               => $record->uri,
                'filename'          => $record->fileName,
                'filesize'          => $record->fileSize,
                'mimetype'          => $record->mimetype,
                'duration'          => $record->duration,
                'dimension_value'   => $record->dimensionValue,
                'dimension_unit'    => $record->dimensionUnit,
                'data_rate_value'   => $record->dataRateValue,
                'data_rate_unit'    => $record->dataRateUnit,
            ], 'IGNORE');
        }
    }
}
