<?php

namespace CryptoCore\Setapp;

class FxHash_v1 extends FxHash
{
    public const IssuerKTID                         = 'KT1BJC12dG17CVvPKJ1VYaNnaT5mzfnUTwXv';   # TokensRange=[0..26047]
    public const TokensKTID                         = 'KT1U6EHmNxJTkvaWJ4ThczG4FSDaHC21ssvi';   # TokensRange=[589146..ALL_NEW]

    public const MarketplaceKTID                    = 'KT1GbyoDi7H1sfXmimXpptZJuCdHMh66WS9u';
    public const MarketplaceFeeWallet               = 'tz1dtzgLYUHMhP6sWeFtFsHkHqyPezBBPLsZ';

    public const Bigmap_Issuers                     = 149776;
    public const Bigmap_IssuerPrices                = 149813;
    public const Bigmap_Swaps                       = 149787;       # KT1GbyoDi7H1sfXmimXpptZJuCdHMh66WS9u / listings
    public const Bigmap_Offers                      = 149789;       # KT1GbyoDi7H1sfXmimXpptZJuCdHMh66WS9u / offers
}
