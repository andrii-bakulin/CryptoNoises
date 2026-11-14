<?php

namespace CryptoNoisesTezos\Entities;

use CryptoNoisesTezos\Models;

class IpfsMediaTeaser extends IpfsMedia
{
    public ?string $storageFolder = 'teasers';

    /**
     * @param string|null $mediaUri
     * @return static|null
     */
    public static function Get(?string $mediaUri): ?self
    {
        if ($mediaUri == null)
            return null;

        $item = Models\IpfsMediaTeasers::model()->getItem($mediaUri);

        if ($item == null)
            return null;

        $instance = new self($mediaUri);
        $instance->initialize($item);
        return $instance;
    }
}
