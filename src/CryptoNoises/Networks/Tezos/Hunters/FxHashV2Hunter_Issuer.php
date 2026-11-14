<?php

namespace CryptoNoises\Networks\Tezos\Hunters;

use CryptoCore\Setapp;
use CryptoNoises\Core\CustomData;
use CryptoNoises\Core\Log;
use CryptoNoises\Entities\EventFlow;
use CryptoNoises\Enum;
use CryptoNoises\GatewayTezos;
use CryptoNoises\Models;
use CryptoNoises\Networks\Tezos\TezosNetwork;
use Exception;

class FxHashV2Hunter_Issuer extends FxHashV2Hunter
{
    public function parseTransaction(array $recInit, array $subRecords): bool
    {
        if ($recInit['target'] == Setapp\FxHash_v1::IssuerKTID)
        {
            if ($recInit['entrypoint'] == 'mint')
                return $this->analyzeMintEvent($recInit, $subRecords);
        }

        if ($recInit['target'] == Setapp\FxHash_v2::IssuerKTID)
        {
            if ($recInit['entrypoint'] == 'mint' || $recInit['entrypoint'] == 'mint_with_ticket')
                return $this->analyzeMintEvent($recInit, $subRecords);
        }

        return false;
    }

    private function analyzeMintEvent(array $recInit, array $subRecords): bool
    {
        try
        {
            $recMint = self::ExtractRecordByEntrypoint($subRecords, 'mint');

            if ($recMint == null)
                throw new Exception('Cannot find mint-transaction');

            $subRecords = self::RestructureRecordsByRemovingProxyContracts($recInit, $subRecords);

            //----------------------------------------------------------------------------------------------------------
            // There are two kinds of mint:
            //  - token-mint
            //  - ticket-mint

            $isTicketMint = $recMint['target'] == Setapp\FxHash_v2::TicketsKTID;
            $isTokenMint  = !$isTicketMint;

            //----------------------------------------------------------------------------------------------------------
            // The notification shows the issuer token id (not the freshly minted token id) so the preview is correct
            // to show correct preview!

            $issuerAddress = $recInit['target'];
            $issuerTokenId = $recInit['parameter']['value']['issuer_id'];

            if (!is_numeric($issuerTokenId))
                throw new Exception('Cannot detect token-issuer-id');

            $issuerEntity = GatewayTezos\Tokens::GetTokenDetails($issuerAddress, $issuerTokenId);

            if ($issuerEntity == null)
                throw new Exception("Cannot get token-issuer details for #$issuerTokenId");

            //----------------------------------------------------------------------------------------------------------

            if ($issuerEntity->creatorAddress && TezosNetwork::IsWalletAddressValid($issuerEntity->creatorAddress))
                $creatorAddress = $issuerEntity->creatorAddress;
            else
                $creatorAddress = null;

            //----------------------------------------------------------------------------------------------------------

            // Set default values as "empty". But the same will be for "FREE-mint" event
            $amountExpense  = 0;
            $amountIncomes  = [];   # key:address => value:amount
            $amountStoreFee = 0;

            if (count($subRecords) > 0)
            {
                $recStore = self::ExtractRecordByTargetAddress($subRecords, self::kMarketplaceFeeWallet);

                # $recStore may be null, for example when the price is "0.00001 XTZ"

                $amountExpense  = $recInit['amount'];
                $amountStoreFee = $recStore ? $recStore['amount'] : 0;

                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

                foreach ($subRecords as $record)
                {
                    $amountIncomes[$record['target']] = ($amountIncomes[$record['target']] ?? 0) + $record['amount'];
                }
            }

            if (empty($amountIncomes) && $creatorAddress)
            {
                $amountIncomes[$creatorAddress] = 0;
            }

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
            // Event #1 for customer who mint

            $event = new EventFlow();
            $event->initInTezosNetworkByRecord($recInit, 1);

            $event->event_type              = $isTicketMint ? Enum\EventFlowType::TokenMintExpense_Ticket : Enum\EventFlowType::TokenMintExpense;
            $event->sender_address          = $recInit['sender'];           // mint initiator (payer)
            $event->target_address          = $recInit['target'];           // contract
            $event->royalty_address         = $creatorAddress;              // mint-issue-owner (base [or] NULL)

            $event->price                   = $amountExpense;
            $event->currency                = TezosNetwork::kCURRENCY_ID;

            $event->marketplace_address     = $recInit['target'];           // Needed to build the correct generative-token link
            $event->token_address           = $issuerEntity->tokenAddress;
            $event->token_id                = $issuerEntity->tokenId;

            if ($isTokenMint)
            {
                # fx(hash) iteration index, shown in the mint notification
                $event->custom_data             = [
                    CustomData::kITERATION_IDX              => $recMint['parameter']['value']['iteration'],
                    CustomData::kITERATION_MARKET_URL_ID    => Models\Shared\MarketUrls::kTokenFxhash,
                    CustomData::kITERATION_TOKEN_ID         => $recMint['parameter']['value']['token_id'],
                ];
            }

            $this->parser->pushEvent($event);

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
            // Event #2 for issuer owner(s) (this notification may stack MANY-to-1)

            if (!empty($amountIncomes))
            {
                ksort($amountIncomes); // Important!
                $entitySubid = 100;

                foreach ($amountIncomes as $addressIncome => $amountIncome)
                {
                    $event = new EventFlow();
                    $event->initInTezosNetworkByRecord($recInit, $entitySubid++);

                    $event->event_type              = $isTicketMint ? Enum\EventFlowType::TokenMintIncome_Ticket : Enum\EventFlowType::TokenMintIncome;
                    $event->sender_address          = $recInit['target'];           // contract
                    $event->target_address          = $addressIncome;

                    $event->price                   = $amountIncome;
                    $event->fee                     = $amountStoreFee;
                    $event->currency                = TezosNetwork::kCURRENCY_ID;

                    $event->marketplace_address     = $recInit['target'];           // Needed to build the correct generative-token link
                    $event->token_address           = $issuerEntity->tokenAddress;
                    $event->token_id                = $issuerEntity->tokenId;

                    $this->parser->pushEvent($event);
                }
            }

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            return true;
        }
        catch (Exception $e)
        {
            Log::Warning(get_class($this), __FUNCTION__, $e->getMessage()." #{$recInit['id']}");
            return false;
        }
    }
}
