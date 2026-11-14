<?php

namespace CryptoNoisesTezos\Analyzers\BigmapUpdateEntities\MarketRecords;

class MarketDealRecord
{
    const kActive_Yes   = 'yes';
    const kActive_No    = 'no';

    public ?string $is_active = null;
    public ?string $ts_active_until = null;

    public ?int $exkey_token_address = null;
    public ?int $exkey_token_id = null;
    public ?int $exkey_token_artist = null;
    public ?int $exkey_token_owner = null;
    public ?int $objkt_amount = null;
    public ?int $objkt_price_xtz = null;
    public ?int $objkt_royalties = null;
    public ?string $ts_deal_expired = null;
}
