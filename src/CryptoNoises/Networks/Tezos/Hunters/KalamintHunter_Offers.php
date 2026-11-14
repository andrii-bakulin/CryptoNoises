<?php

namespace CryptoNoises\Networks\Tezos\Hunters;

use CryptoNoises\Core\Log;
use CryptoNoises\Entities\EventFlow;
use CryptoNoises\Enum;
use CryptoNoises\Networks\Tezos\TezosHelper;
use CryptoNoises\Networks\Tezos\TezosNetwork;
use Exception;

class KalamintHunter_Offers extends KalamintHunter
{
    public function parseTransaction(array $recInit, array $subRecords): bool
    {
        if ($recInit['target'] != self::kOffersKTID)
            return false;

        if ($recInit['entrypoint'] == 'make_offer')
            return $this->analyzeMakeOfferEvent($recInit, $subRecords);

        if ($recInit['entrypoint'] == 'accept_offer')
            return $this->analyzeStdTokenPurchaseEvent($recInit, $subRecords, self::kMarketplaceFeeWallet, Enum\EventFlowDetails::OfferAccepted);

        if ($recInit['entrypoint'] == 'withdraw_offer')
            return $this->fixEventAsSkipped($recInit, $subRecords);

        return false;
    }

    protected function analyzeMakeOfferEvent(array $recInit, array $subRecords): bool
    {
        try
        {
            $paramsValue = $recInit['parameter']['value'];

            $tokenAddress = $paramsValue['nft_contract'];
            $tokenId      = $paramsValue['nft_token_id'];

            //----------------------------------------------------------------------------------------------------------

            $event = new EventFlow();
            $event->initInTezosNetworkByRecord($recInit);

            $event->event_type              = Enum\EventFlowType::TokenOffer;
            $event->sender_address          = $recInit['sender'];
            $event->offer_owners_addresses  = TezosHelper::GetTokenHolders($tokenAddress, $tokenId);
            $event->royalty_address         = $paramsValue['creator'];

            switch ($paramsValue['offered_token_contract'])
            {
                case 'KT1VVNTAxmYjSbxvduQu4k4V2AeNn4y4EzZK': # XTZ (Kalamint Art House | Offers catcher)
                    $event->price           = $paramsValue['offer_amount'];
                    $event->currency        = TezosNetwork::kCURRENCY_ID;
                    break;

                case 'KT1A5P4ejnLix13jtadsfV9GCnXLMNnab8UT': # KALAM
                case 'KT1GRSvLoikDsXujKgZPsGLX8k8VvR2Tq95b': # PLENTY
                case 'KT1BHCumksALJQJ8q8to2EPigPW6qpyTr7Ng': # CRUNCH
                case 'KT1K9gCRgaLRFKTErYt1wVxA3Frb9FjasjTV': # kUSD
                    $event->price            = $paramsValue['offer_amount'];
                    $event->currency_address = $paramsValue['offered_token_contract'].':'.$paramsValue['offered_token_id'];
                    break;

                default:
                    throw new Exception('Unknown currency for kalamint.io offer: '.$paramsValue['offered_token_contract']);
            }

            $event->marketplace_address = self::kMarketplaceKTID;
            $event->token_address       = $tokenAddress;
            $event->token_id            = $tokenId;

            $this->parser->pushEvent($event);

            //----------------------------------------------------------------------------------------------------------

            return true;
        }
        catch (Exception $e)
        {
            Log::Warning(get_class($this), __FUNCTION__, $e->getMessage()." #{$recInit['id']}");
            return false;
        }
    }
}
