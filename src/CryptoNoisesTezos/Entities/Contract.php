<?php

namespace CryptoNoisesTezos\Entities;

use CryptoNoisesTezos\Models;

class Contract
{
    public string $address;

    public ?string $name = null;
    public ?string $nameCustom = null;

    public ?string $authorAddress = null;

    public ?string $imageUrl = null;
    public ?string $imageUrlCustom = null;

    public ?string $tokenLink = null;

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    public static function Get(string $address): ?self
    {
        $item = Models\Contracts::model()->getItem($address);

        if ($item === null)
            return null;

        $instance = new self();
        $instance->address = $address;
        $instance->initialize($item);
        return $instance;
    }

    public static function GetFake(string $address): self
    {
        $instance = new self();
        $instance->address = $address;
        return $instance;
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    private function initialize(array $data): void
    {
        $this->name             = $data['name'];
        $this->nameCustom       = $data['name_custom'];

        $this->authorAddress    = $data['author_address'];

        $this->imageUrl         = $data['image_url'];
        $this->imageUrlCustom   = $data['image_url_custom'];

        $this->tokenLink        = $data['token_link'];
    }

    public function getPublicName(): string
    {
        return $this->nameCustom ?:
               $this->name ?:
               (substr($this->address, 0, 5).'...'.substr($this->address, -5, 5));
    }

    public function getPublicAuthorAddress(): ?string
    {
        return $this->authorAddress;
    }

    public function getPublicImageUrl(): ?string
    {
        return $this->imageUrlCustom ?: $this->imageUrl ?: null;
    }

    public function getPublicTokenLink(string $tokenId): string
    {
        $tokenLink = $this->tokenLink ?: 'https://objkt.com/asset/:address/:id';
        $tokenLink = str_replace(':address', $this->address, $tokenLink);
        $tokenLink = str_replace(':id', $tokenId, $tokenLink);
        return $tokenLink;
    }
}
