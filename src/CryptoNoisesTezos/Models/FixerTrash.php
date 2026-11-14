<?php

namespace CryptoNoisesTezos\Models;

use CryptoCore\Vss\VssModel;

class FixerTrash extends VssModel
{
    public static function model($className = __CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'fixer_thrash';
    }
}
