<?php

namespace CryptoNoises\Models;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class InformFlow extends VssModel
{
    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'inform_flow';
    }

    public function getInformById($informFlowId)
    {
        $sql = "SELECT * FROM {$this->tableName()} WHERE inform_flow_id = :inform_flow_id";

        return Vss::db()->queryRow($sql, ['inform_flow_id'=>$informFlowId]);
    }
}
