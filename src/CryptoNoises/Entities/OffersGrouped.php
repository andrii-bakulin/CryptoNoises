<?php

namespace CryptoNoises\Entities;

class OffersGrouped
{
    private int $totalOffersCount = 0;
    private array $items = [];

    public function push(Offer $offer): void
    {
        $groupIdx = null;

        foreach ($this->items as $idx => $item)
        {
            /** @var Token $iToken */
            $iToken       = $item['token'];

            /** @var Marketplace $iMarketplace */
            $iMarketplace = $item['marketplace'];

            if ($iMarketplace->address === $offer->marketplace->address &&
                $iToken->tokenAddress === $offer->token->tokenAddress &&
                $iToken->tokenId === $offer->token->tokenId)
            {
                $groupIdx = $idx;
                break;
            }
        }

        if ($groupIdx === null) # -> not found
        {
            $groupIdx = count($this->items);

            $this->items[$groupIdx] = [
                'marketplace'   => $offer->marketplace,
                'token'         => $offer->token,
                'prices'        => [],
            ];
        }

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        if ($offer->priceCurrency && $offer->priceAmount)
            $priceKey = $offer->buyerAddress.':'.$offer->priceCurrency.':'.$offer->priceAmount;
        else
            $priceKey = $offer->buyerAddress.':'.'TOKEN';

        if (isset($this->items[$groupIdx]['prices'][$priceKey]))
        {
            $this->items[$groupIdx]['prices'][$priceKey]['count']++;
        }
        else
        {
            $this->items[$groupIdx]['prices'][$priceKey]['buyerAddress']  = $offer->buyerAddress;
            $this->items[$groupIdx]['prices'][$priceKey]['priceFriendly'] = $offer->priceFriendly;
            $this->items[$groupIdx]['prices'][$priceKey]['count']         = 1;
        }

        $this->totalOffersCount++;
    }

    public function getTotalOffers(): int
    {
        return $this->totalOffersCount;
    }

    public function getItemsCount(): int
    {
        return count($this->items);
    }

    public function getItems(int $offset = 0, ?int $length = null): array
    {
        return array_slice($this->items, $offset, $length);
    }
}
