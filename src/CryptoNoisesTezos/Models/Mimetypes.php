<?php

namespace CryptoNoisesTezos\Models;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class Mimetypes extends VssModel
{
    const kTYPE_empty                           = '<empty>';
    const kTYPE_undefined                       = '<undefined>';

    const kTYPE_fxhash_fxgen                    = 'application/x-directory';
    const kTYPE_fxhash_gentk                    = 'application/x-directory';

    const kTYPE_image_png                       = 'image/png';

    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'mimetypes';
    }

    public function getItemByMimetype(string $mimetype): ?array
    {
        return Vss::dbTezos()->selectRow('*', $this->tableName(), ['mimetype'=>$mimetype]);
    }

    public function insert(string $mimetype, ?string $notice=''): int
    {
        $item = [
            'mimetype' => $mimetype,
            'notice'   => $notice
        ];
        return (int) Vss::dbTezos()->insert($this->tableName(), $item, 'IGNORE');
    }
}
