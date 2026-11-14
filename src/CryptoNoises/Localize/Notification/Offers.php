<?php

namespace CryptoNoises\Localize\Notification;

use CryptoNoises\Entities\CallbackAction;
use CryptoNoises\Entities\Marketplace;
use CryptoNoises\Entities\OffersGrouped;
use CryptoNoises\Entities\Token;
use CryptoNoises\Entities\User;
use CryptoNoises\Helpers\Helper;
use CryptoNoises\Localize\Notification;
use CryptoNoises\Localize\NotificationButton;

class Offers extends Notification
{
    public ?bool $discord_isAllowEphemeralResponse  = true;

    //------------------------------------------------------------------------------------------------------------------

    public static function I(?User $user): self
    {
        return new self($user);
    }

    public function __construct(?User $user)
    {
        parent::__construct($user);
    }

    public function initialize(OffersGrouped $offersGrouped, CallbackAction $caBuilder, int $totalOffersCount, int $itemsOffset, int $itemsPerPage): self
    {
        if ($offersGrouped->getTotalOffers() == 0)
        {
            $this->title = ['🤝 Your offers on marketplaces'];
            $this->appendDescription('No offers found');
            return $this;
        }

        $titleOffers = $offersGrouped->getTotalOffers();
        $titleOffers = $titleOffers.' offer'.($titleOffers != 1 ? 's' : '');

        $titleTokens = $offersGrouped->getItemsCount();
        $titleTokens = $titleTokens.' token'.($titleTokens != 1 ? 's' : '');

        $this->title = ["🤝 Found $titleOffers on $titleTokens"];

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $index = $itemsOffset + 1;
        foreach ($offersGrouped->getItems($itemsOffset, $itemsPerPage) as $offerGroupedItem)
        {
            /** @var Token $token */
            $token = $offerGroupedItem['token'];

            /** @var Marketplace $marketplace */
            $marketplace = $offerGroupedItem['marketplace'];

            $indexKeycap = Helper::NumberToKeycapEmoji($index);

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            $offerValues = [];

            foreach ($offerGroupedItem['prices'] as $priceItem)
            {
                $buyerAddress  = $priceItem['buyerAddress'];
                $buyerAddress  = $this->fixAddressThreeDots($buyerAddress);

                $priceFriendly = $priceItem['priceFriendly'] ?? 'price in tokens';
                $count         = $priceItem['count'];

                if (self::IsTelegram())
                {
                    $offerValues[] =  "<code>$buyerAddress</code> ⇨ <b>$priceFriendly</b>"
                        . ($count > 1 ? " <i>($count offers)</i>" : '');
                }
                else if (self::IsDiscord())
                {
                    $offerValues[] =  "`$buyerAddress` ⇨ `$priceFriendly`"
                        . ($count > 1 ? " ($count offers)" : '');
                }
            }

            if (count($offerValues) > 8)
            {
                $countHidden = count($offerValues) - 8;
                $offerValues = array_slice($offerValues, 0, 8);
                $offerValues[] = "... and $countHidden more offers";
            }

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            $tokenMarketUrl = $marketplace->getTokenUrl($token);

            if (self::IsTelegram())
            {
                $this->description[] = "$indexKeycap <a href=\"".self::SafeText((string) $tokenMarketUrl)."\">".self::SafeText((string) $token->name)."</a>";
                $this->description   = array_merge($this->description, $offerValues);
                $this->description[] = "Marketplace: <b>$marketplace->name</b>";
                $this->description[] = self::EMPTY_LINE;
            }
            else if (self::IsDiscord())
            {
                $this->description[] = "$indexKeycap **{$token->name}**";
                $this->description   = array_merge($this->description, $offerValues);
                $this->description[] = "*Marketplace:* **$marketplace->name**";
                $this->description[] = self::EMPTY_LINE;

                $this->appendButtonToLastRow(NotificationButton::Link($indexKeycap, $tokenMarketUrl), 4);
            }

            $index++;
        }

        return $this;
    }
}
