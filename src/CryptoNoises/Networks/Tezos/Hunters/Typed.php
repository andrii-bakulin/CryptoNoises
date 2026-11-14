<?php

namespace CryptoNoises\Networks\Tezos\Hunters;

use CryptoNoises\Core\Log;
use CryptoNoises\Entities\TokenActions;
use CryptoNoises\Networks\Tezos\TezosNetwork;

class Typed extends AbstractHunter
{
    public const kTokensKTID                        = 'KT1J6NY5AU61GzUX51n59wwiZcGJ9DrNTwbK';   # FA2

    public const kMinterKTID                        = 'KT1CK9RnWZGnejBeT6gJfgvf4p7f1NwhP9wS';   # Minter
    public const kMarketplaceKTID                   = 'KT1VoZeuBMJF6vxtLqEFMoc4no5VDG789D7z';   # Market
    public const kRegisterKTID                      = 'KT1QSERFwHZcQfUf6ZruTSLMBfzitRs6ixmP';   # Register

    public const kMarketplaceFeeWallet              = 'tz1aqMiWgnFddGZSTsEMSe8qbXkVGn7C4cg5';

    //------------------------------------------------------------------------------------------------------------------

    public static function GetAllMarketContracts(): array
    {
        return [self::kMarketplaceKTID];
    }

    public static function GetMarketplaceContractByTokenContract(string $tokensContract): ?string
    {
        if ($tokensContract == self::kTokensKTID)
            return self::kMarketplaceKTID;

        return null;
    }

    //------------------------------------------------------------------------------------------------------------------

    public function parseTransaction(array $recInit, array $subRecords): bool
    {
        if ($recInit['target'] != self::kMarketplaceKTID)
            return false;

        $entrypoint = $recInit['entrypoint'];

        // List
        if ($entrypoint == 'swap')
            return $this->analyzeListEvent($recInit, $subRecords);

        // Unlist
        if ($entrypoint == 'cancel_swap')
            return $this->fixEventAsSkipped($recInit, $subRecords);

        // Direct Purchase
        if ($entrypoint == 'collect')
            return $this->analyzeCollect($recInit, $subRecords);

        return false;
    }

    private function analyzeListEvent(array $recInit, array $subRecords): bool
    {
        return $this->analyzeStdTokenListEvent($recInit, $subRecords, function(array $recInit, TokenActions\Transfer $transfer)
        {
            $recValue = $recInit['parameter']['value'];

            if ($recValue['fa2'] != self::kTokensKTID)
            {
                Log::Warning(get_class($this), __FUNCTION__, "Wrong fa2 contract value", $recInit);
                return null;
            }

            if ($recValue['royalties'] != 0)
            {
                Log::Warning(get_class($this), __FUNCTION__, "Non-zero royalties are not supported yet", $recInit);
                return null;
            }

            $listing = new TokenActions\Listing();
            $listing->tokenAddress      = $transfer->tokenAddress;  # self::kTokensKTID;
            $listing->tokenId           = $transfer->tokenId;       # $recValue['objkt_id'];

            $listing->price             = $recValue['xtz_per_objkt'];
            $listing->currency          = TezosNetwork::kCURRENCY_ID;
            $listing->quantity          = $recValue['objkt_amount'];

            $listing->creator           = $recValue['creator'];
            $listing->royalty_d4        = null; // royalties are not supported yet

            return $listing;
        });
    }

    private function analyzeCollect(array $recInit, array $subRecords): bool
    {
        return $this->analyzeStdTokenPurchaseEvent($recInit, $subRecords, self::kMarketplaceFeeWallet);
    }
}
