<?php

namespace CryptoNoises\Bots;

use CryptoNoises\Config;
use CryptoNoises\Entities\CallbackAction;
use CryptoNoises\Entities\User;
use CryptoNoises\Entities\UserDomains;
use CryptoNoises\Localize\Notification;

trait AbstractBot_Domains
{
    public function doDomains(User $user, ?CallbackAction $callbackAction=null): ?Notification
    {
        //--------------------------------------------------------------------------------------------------------------
        // Generate content

        $userDomains = UserDomains::GetByUserId($user->userId);

        //--------------------------------------------------------------------------------------------------------------

        $itemsPerPage   = Config::kUI_DomainsPerPage;
        $itemsCount     = $userDomains->count();

        $pagesCount     = ceil($itemsCount/$itemsPerPage);
        $pageIndex      = (int)($callbackAction->data['page'] ?? null);
        $pageIndex      = max($pageIndex, 0);
        $pageIndex      = max(0, min($pageIndex, $pagesCount-1));

        $caBuilder      = CallbackAction::Builder(CallbackAction::Scope_Domains);

        //--------------------------------------------------------------------------------------------------------------

        $notification = Notification\Domains::I($user);
        $notification->initialize($userDomains, $caBuilder, $itemsCount, $pageIndex*$itemsPerPage, $itemsPerPage);
        $notification->appendPagerControl($caBuilder, $pageIndex, $itemsCount, $itemsPerPage);

        return $notification;
    }
}
