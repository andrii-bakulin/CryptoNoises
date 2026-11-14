<?php

namespace CryptoCore\Setapp;

class FxHash_v2 extends FxHash
{
    public const IssuerKTID                         = 'KT1Xpmp15KfqoePNW9HczFmqaGNHwadV2a3b';   # TokensRange=[26048..ALL_NEW]
    public const TokensKTID                         = 'KT1EfsNuqwLAWDd3o4pvfUx1CAh5GMdTrRvr';   # TokensRange=[0..ALL_NEW]
    public const TicketsKTID                        = 'KT19etLCjCCzTLFFAxsxLFsVYMRPetr2bTD5';

    public const MarketplaceKTID                    = 'KT1GbyoDi7H1sfXmimXpptZJuCdHMh66WS9u';   # Same as @ FxHash_v1
    public const MarketplaceFeeWallet               = 'tz1dtzgLYUHMhP6sWeFtFsHkHqyPezBBPLsZ';

    public const Bigmap_Issuers                     = 411393;
    public const Bigmap_Swaps                       = 149787;       # KT1GbyoDi7H1sfXmimXpptZJuCdHMh66WS9u / listings
    public const Bigmap_Offers                      = 149789;       # KT1GbyoDi7H1sfXmimXpptZJuCdHMh66WS9u / offers
}
