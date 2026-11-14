<?php

namespace CryptoNoises\Entities;

use CryptoNoises\Core\URL;
use CryptoNoises\Localize\Phrase;
use CryptoNoises\Models;

class Marketplace
{
    public string $address;
    public ?string $name = null;

    public ?string $marketUrlId_token = null;
    public ?string $marketUrlId_collection = null;
    public ?string $marketUrlId_user = null;

    public ?string $marketUrlId_tokenExtra1 = null;
    public ?string $marketUrlId_tokenExtra2 = null;
    public ?string $marketUrlId_tokenExtra3 = null;
    public ?string $marketUrlId_tokenExtra4 = null;

    public static function InitByItem(array $item): self
    {
        $instance = new self();
        $instance->address                  = $item['marketplace_address'];
        $instance->name                     = $item['marketplace_name'];

        $instance->marketUrlId_token        = $item['token_market_url_id'];
        $instance->marketUrlId_collection   = $item['collection_market_url_id'];
        $instance->marketUrlId_user         = $item['user_market_url_id'];

        $instance->marketUrlId_tokenExtra1  = $item['token_extra1_market_url_id'];
        $instance->marketUrlId_tokenExtra2  = $item['token_extra2_market_url_id'];
        $instance->marketUrlId_tokenExtra3  = $item['token_extra3_market_url_id'];
        $instance->marketUrlId_tokenExtra4  = $item['token_extra4_market_url_id'];
        return $instance;
    }

    public function getTokenUrl(Token $token): ?string
    {
        if ($this->marketUrlId_token == null)
            return null;

        $marketUrlItem = Models\Shared\MarketUrls::model()->getItemById($this->marketUrlId_token);

        return $marketUrlItem ? URL::InnerRedirectToToken($marketUrlItem, $token->tokenAddress, $token->tokenId) : null;
    }

    public function getTokenUrlAndButtonCaption(int $extraIndex, string $tokenAddress, string $tokenId): array
    {
        $marketUrlId_key = $extraIndex == 0 ? 'marketUrlId_token' : 'marketUrlId_tokenExtra'.$extraIndex;

        if ($this->$marketUrlId_key == null)
            return [null, null];

        $marketUrlItem = Models\Shared\MarketUrls::model()->getItemById($this->$marketUrlId_key);

        if ($marketUrlItem == null)
            return [null, null];

        $tokenUrl = URL::InnerRedirectToToken($marketUrlItem, $tokenAddress, $tokenId);
        $caption  = $marketUrlItem['button_caption'] ?: Phrase::I()->marketplace;

        return [$tokenUrl, $caption];
    }

    public function getCollectionUrl(?string $tokenAddress, ?string $collectionGroup = null, ?string $collectionId = null): ?string
    {
        if ($tokenAddress == null || $this->marketUrlId_collection == null)
            return null;

        $marketUrlItem = Models\Shared\MarketUrls::model()->getItemById($this->marketUrlId_collection);

        return $marketUrlItem ? URL::InnerRedirectToCollection($marketUrlItem, $tokenAddress, $collectionGroup, $collectionId) : null;
    }

    public function getUserProfileUrl(?string $address): ?string
    {
        if ($address == null || $this->marketUrlId_user == null)
            return null;

        $marketUrlItem = Models\Shared\MarketUrls::model()->getItemById($this->marketUrlId_user);

        return $marketUrlItem ? URL::InnerRedirectToUser($marketUrlItem, $address) : null;
    }
}
