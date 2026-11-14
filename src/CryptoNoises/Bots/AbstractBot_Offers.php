<?php

namespace CryptoNoises\Bots;

use CryptoNoises\Config;
use CryptoNoises\Entities\CallbackAction;
use CryptoNoises\Entities\OffersGrouped;
use CryptoNoises\Entities\User;
use CryptoNoises\GatewayTezos;
use CryptoNoises\Localize\Notification;
use CryptoNoises\Localize\NotificationButton;
use CryptoNoises\Localize\Phrase;

trait AbstractBot_Offers
{
    public function doOffers(User $user, ?CallbackAction $callbackAction=null): ?Notification
    {
        $offers = GatewayTezos\Models\BigmapDataMarketOffers::GetOffersByUserId($user->userId);

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
        // Need group offers list by "same tokens + at same markets"

        $offersGrouped = new OffersGrouped();

        foreach ($offers as $offer)
        {
            $offersGrouped->push($offer);
        }

        //--------------------------------------------------------------------------------------------------------------

        $itemsPerPage   = Config::kUI_OffersPerPage;
        $itemsCount     = $offersGrouped->getItemsCount();

        $pagesCount     = ceil($itemsCount/$itemsPerPage);
        $pageIndex      = (int)($callbackAction->data['page'] ?? null);
        $pageIndex      = max($pageIndex, 0);
        $pageIndex      = max(0, min($pageIndex, $pagesCount-1));

        $caBuilder      = CallbackAction::Builder(CallbackAction::Scope_Offers);

        //--------------------------------------------------------------------------------------------------------------

        $notification = Notification\Offers::I($user);
        $notification->initialize($offersGrouped, $caBuilder, $itemsCount, $pageIndex*$itemsPerPage, $itemsPerPage);
        $notification->appendPagerControl($caBuilder, $pageIndex, $itemsCount, $itemsPerPage);

        $notification->appendButtonsRow([
            NotificationButton::CallbackAction(Phrase::I()->refresh, $caBuilder->newEventAuid('',['page'=>$pageIndex])),
        ]);

        return $notification;
    }
}
