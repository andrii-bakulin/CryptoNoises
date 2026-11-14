<?php

namespace CryptoNoises\Bots;

use CryptoNoises\Bots\Helpers\CurrencyButtonsGrid;
use CryptoNoises\Entities\CallbackAction;
use CryptoNoises\Entities\Coin;
use CryptoNoises\Entities\User;
use CryptoNoises\Entities\UserCoins;
use CryptoNoises\Localize\Notification;
use CryptoNoises\Localize\NotificationButton;
use CryptoNoises\Localize\Phrase;
use CryptoNoises\Models;

trait AbstractBot_CoinsRates
{
    public function doCoinsRates(User $user, ?CallbackAction $callbackAction=null): ?Notification
    {
        $notification = Notification\CoinsRate_Info::I($user);

        //--------------------------------------------------------------------------------------------------------------

        switch ($callbackAction ? $callbackAction->event : null)
        {
            case 'control':
                $currencyInId = (string) ($callbackAction->data['currency_in'] ?? null);

                if ($currencyInId)
                    $this->setupCoinsRates_Step2($user, $callbackAction, $notification, $currencyInId);
                else
                    $this->setupCoinsRates_Step1($user, $callbackAction, $notification);
                break;
        }

        //--------------------------------------------------------------------------------------------------------------
        // Generate content

        $trackingCurrency = Models\UsersCurrencyTrack::model()->selectAll($user->userId);

        if ($trackingCurrency)
        {
            foreach ($trackingCurrency as $curItem)
            {
                $currencyInId   = $curItem['currency_in_id'];
                $currencyOutId  = $curItem['currency_out_id'];

                $item = Models\CurrencyValues::model()->getItemByIds($currencyInId, $currencyOutId);
                $rate = $item ? $item['value'] : 0;

                $notification->appendRate($currencyInId, new Coin($rate, $currencyOutId));
            }
        }
        else
        {
            $notification->nothingSelected();
        }

        if ($notification->getButtonsCount() == 0)
        {
            $caBuilder = CallbackAction::Builder(CallbackAction::Scope_CoinsRates);

            $notification->appendButtonsRow([
                NotificationButton::CallbackAction(Phrase::I()->refresh,        $caBuilder->newEventAuid()),
                NotificationButton::CallbackAction(Phrase::I()->actionSettings, $caBuilder->newEventAuid('control')),
            ]);
        }

        return $notification;
    }

    //------------------------------------------------------------------------------------------------------------------

    private function setupCoinsRates_Step1(User $user, CallbackAction $callbackAction,
                                           Notification\CoinsRate_Info $notification): void
    {
        $caBuilder = CallbackAction::Builder(CallbackAction::Scope_CoinsRates);

        $currencyInItems = Models\CurrencyIn::model()->selectPublic();

        $buttons = [];
        foreach ($currencyInItems as $item)
        {
            $button = NotificationButton::CallbackAction($item['name'],
                $caBuilder->newEventAuid('control', ['currency_in'=>$item['currency_in_id']]));

            $buttons[] = $button;
        }

        $notification->appendButtonsAsRows($buttons, 3);
        $notification->appendButtonsRow([
            NotificationButton::CallbackAction(Phrase::I()->actionBack,
                $caBuilder->newEventAuid())
        ]);

        $notification->appendActionStep1();
    }

    private function setupCoinsRates_Step2(User $user, CallbackAction $callbackAction,
                                           Notification\CoinsRate_Info $notification, string $currencyInId): void
    {
        $currencyOutId = (string) ($callbackAction->data['currency_out'] ?? null);

        $caBuilder = CallbackAction::Builder(CallbackAction::Scope_CoinsRates);

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
        // Update state if required

        if ($currencyOutId && $currencyOutId != $currencyInId)
            UserCoins::ToggleTracking($user->userId, $currencyInId, $currencyOutId);

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
        // Generate control-buttons

        $page = (int) ($callbackAction->data['page'] ?? null);

        $grid = new CurrencyButtonsGrid(Models\CurrencyOut::model()->selectPublicFull($currencyInId), $page,
            fn (array $item): bool => UserCoins::IsTracking($user->userId, $currencyInId, $item['currency_out_id']),
            fn (array $item): string => $caBuilder->newEventAuid('control', [
                'currency_out' => $item['currency_out_id'],
                'currency_in'  => $currencyInId,
                'page'         => $page,
            ]),
            fn (int $i): string => $caBuilder->newEventAuid('control', [
                'currency_in'  => $currencyInId,
                'page'         => $i,
            ]),
            $caBuilder->newEventAuid('control'));

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $notification->appendButtonsAsRows($grid->navigation, 100);
        $notification->appendButtonsAsRows($grid->currencies, $grid->buttonsPerRow);

        $currencyInItem = Models\CurrencyIn::model()->getItem($currencyInId);
        $notification->appendActionStep2($currencyInItem['name']);
    }
}
