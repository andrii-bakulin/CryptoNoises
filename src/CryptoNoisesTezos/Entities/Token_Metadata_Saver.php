<?php

namespace CryptoNoisesTezos\Entities;

use CryptoNoisesTezos\Core\Log;
use CryptoNoisesTezos\Core\TezosAddress;
use CryptoNoisesTezos\Models;

trait Token_Metadata_Saver
{
    private function packTokenMainDataToSave(): array
    {
        $data = [];

        if ($this->minterAddress)
        {
            $data['minter_address'] = $this->minterAddress;
            $data['minter_adid']    = TezosAddress::GetOrCreateAdid($this->minterAddress);
        }

        if ($this->creatorAddress)
        {
            $data['creator_address'] = $this->creatorAddress;
            $data['creator_adid']    = TezosAddress::GetOrCreateAdid($this->creatorAddress);
        }

        $this->packSaveKey($data, 'tokenType',          'token_type');

        $this->packSaveKey($data, 'name',               'name');
        $this->packSaveKey($data, 'description',        'description');
        $this->packSaveKey($data, 'symbol',             'symbol');
        $this->packSaveKey($data, 'decimals',           'decimals');
        $this->packSaveKey($data, 'shouldPreferSymbol', 'should_prefer_symbol');
        $this->packSaveKey($data, 'isBooleanAmount',    'is_boolean_amount');
        $this->packSaveKey($data, 'isTransferable',     'is_transferable');

        $this->packSaveKey($data, 'thumbnailUri',       'thumbnail_uri');
        $this->packSaveKey($data, 'displayUri',         'display_uri');
        $this->packSaveKey($data, 'artifactUri',        'artifact_uri');

        $this->packSaveKey($data, 'editionIndex',       'edition_index');
        $this->packSaveKey($data, 'editions',           'editions');
        $this->packSaveKey($data, 'royaltiesFixed_d4',  'royalties_fixed');
        $this->packSaveKey($data, 'rights',             'rights');
        $this->packSaveKey($data, 'version',            'version');

        return $data;
    }

    public function saveTokenOnly(): bool
    {
        $data = $this->packTokenMainDataToSave();

        $this->update($data);

        return true;
    }

    public function saveTokenAndRelatedData(?array $forcedSaveValues = null): bool
    {
        $data = $this->packTokenMainDataToSave();

        //--------------------------------------------------------------------------------------------------------------

        if ($this->creators !== null)
            $data['has_creators'] = !empty($this->creators) ? count($this->creators) : 0;

        if ($this->tags !== null)
            $data['has_tags'] = !empty($this->tags) ? count($this->tags) : 0;

        if ($this->attributes !== null)
            $data['has_attributes'] = !empty($this->attributes) ? count($this->attributes) : 0;

        if ($forcedSaveValues)
            $data = array_merge($data, $forcedSaveValues);

        if (empty($data))
            return false;

        $this->update($data);

        //--------------------------------------------------------------------------------------------------------------

        if ($this->attributes !== null)
            Models\TokensAttributes::model()->saveRecords($this->contractAdid, $this->tokenUnid, $this->attributes);

        if ($this->creators !== null)
            Models\TokensCreators::model()->saveRecords($this->contractAdid, $this->tokenUnid, $this->creators);

        if ($this->formats !== null)
            Models\TokensFormats::model()->saveRecords($this->contractAdid, $this->tokenUnid, $this->formats);

        if ($this->tags !== null)
            Models\TokensRefTags::model()->saveRecords($this->contractAdid, $this->tokenUnid, $this->tags);

        if ($this->royalties !== null)
            Models\TokensRoyalties::model()->saveRecords($this->contractAdid, $this->tokenUnid, $this->royalties);

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        if ($this->artifactMimetype)
        {
            $mimetypeItem = Models\Mimetypes::model()->getItemByMimetype($this->artifactMimetype);

            if ($mimetypeItem == null)
            {
                Models\Mimetypes::model()->insert($this->artifactMimetype, 'AutoInserted');
                $mimetypeItem = Models\Mimetypes::model()->getItemByMimetype($this->artifactMimetype);
            }

            if ($mimetypeItem)
            {
                Models\TokensRefMimetype::model()->save($this->contractAdid, $this->tokenUnid, $mimetypeItem['mimetype_id']);
            }
            else
            {
                Log::Warning('Token_Metadata_Saver', 'saveTokenAndRelatedData', "Cannot find mimetype item", [
                    'contractAddress'   => $this->contractAddress,
                    'contractAdid'      => $this->contractAdid,
                    'tokenId'           => $this->tokenId,
                    'mimetype'          => $this->artifactMimetype,
                ]);
            }
        }

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        if ($this->jsonUndefinedData !== null)
        {
            Models\TokensUndefinedKeys::model()->cleanUp($this->contractAdid, $this->tokenUnid);

            foreach ($this->jsonUndefinedData as $key => $value)
            {
                // Skip saving of some values...
                if ($key === 'token_id' && $value == $this->tokenId)
                    continue;

                Models\TokensUndefinedKeys::model()->saveRecord($this->contractAdid, $this->tokenUnid, $key, $value);
            }
        }

        //--------------------------------------------------------------------------------------------------------------

        return true;
    }

    private function packSaveKey(array &$data, $keyObject, $keyStorage): void
    {
        $value = $this->$keyObject;

        if ($value === null)
            return;

        if (is_bool($value))
            $data[$keyStorage] = (int) $value;
        else
            $data[$keyStorage] = $value;
    }
}
