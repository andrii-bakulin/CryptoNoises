<?php

namespace CryptoNoises\Models\Log;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class Redirects extends VssModel
{
    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'log_redirects';
    }

    public function fix(array $item)
    {
        return Vss::dbLog()->insert($this->tableName(), $item);
    }
}
