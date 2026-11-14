<?php

namespace CryptoNoisesTezos\Entities;

class IpfsMediaStorageAttrs
{
    public string $hash;
    public string $pathName;
    public string $fileName;
    public string $fullpath;

    public function __construct(string $uri, ?string $storageFolder = null)
    {
        $this->hash = sha1($uri);

        $this->pathName = ($storageFolder ? $storageFolder.'/' : '').substr($this->hash, 0, 2).'/'.substr($this->hash, 2, 2);
        $this->fileName = substr($this->hash, 4);

        $this->fullpath = $this->pathName.'/'.$this->fileName;
    }
}
