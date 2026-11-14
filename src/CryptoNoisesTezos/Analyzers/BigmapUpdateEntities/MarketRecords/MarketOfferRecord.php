<?php

namespace CryptoNoisesTezos\Analyzers\BigmapUpdateEntities\MarketRecords;

class MarketOfferRecord
{
    const kActive_Yes   = 'yes';
    const kActive_No    = 'no';

    public ?string $is_active = null;

    public ?int $exkey_token_address = null;
    public ?int $exkey_token_id = null;
    public ?int $exkey_token_buyer = null;
    public ?int $objkt_price_xtz = null;
}
