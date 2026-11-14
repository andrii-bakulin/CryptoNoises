<?php

namespace CryptoNoises\Bots;

use CryptoNoises\Bots\Helpers\CurrencyButtonsGrid;
use CryptoNoises\Config;
use CryptoNoises\Entities\CallbackAction;
use CryptoNoises\Entities\User;
use CryptoNoises\Localize\Notification;
use CryptoNoises\Localize\NotificationButton;
use CryptoNoises\Localize\Phrase;
use CryptoNoises\Models;

trait AbstractBot_Settings
{
    protected function doSettings(User $user, ?CallbackAction $callbackAction=null): ?Notification
    {
        $caBuilder = CallbackAction::Builder(CallbackAction::Scope_Settings);

        switch ($callbackAction ? $callbackAction->event : null)
        {
            case 'coins-conversion':
                $notification = $this->doSettings_CoinsConversion($user, $callbackAction, $caBuilder);
                break;

            case 'filter-by-type':
            case 'filter-by-type.set':
                $notification = Notification\Settings\FilterByTypes::I($user);
                $this->doOptionsNotifier_FilterByTypes($notification, $user->options(), $callbackAction, $caBuilder);
                break;

            case 'thresholds':
            case 'thresholds.set':
            case 'thresholds.list':
                $notification = Notification\Settings\FilterThresholds::I($user);
                $this->doOptionsNotifier_FilterThresholds($notification, $user->options(), $callbackAction, $caBuilder);
                break;

            default:
                $notification = null;
                break;
        }

        //--------------------------------------------------------------------------------------------------------------

        if ($notification === null)
        {
            $notification = Notification\Settings\Main::I($user);

            $buttons = [];

            $buttons[] = NotificationButton::CallbackAction(Phrase::I()->actionCoinsConversion,
                $caBuilder->newEventAuid('coins-conversion'));

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            $options = $user->options();
            $buttons[] = NotificationButton::CallbackAction(Phrase::I()->actionFilterNotifications.' ('.$options->activeNotifiers().'/'.$options->totalNotifiers().')',
                $caBuilder->newEventAuid('filter-by-type'));

            $buttons[] = NotificationButton::CallbackAction(Phrase::I()->filterThresholds,
                $caBuilder->newEventAuid('thresholds'));

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            if (Config::cfg()->isTelegramPlatform())
            {
                $notification->appendButtonsAsRows($buttons, 1);
            }
            else if(Config::cfg()->isDiscordPlatform())
            {
                $notification->appendButtonsAsRows($buttons, 5);
            }
        }

        return $notification;
    }

    private function doSettings_CoinsConversion(User $user, CallbackAction $callbackAction, CallbackAction $caBuilder): ?Notification
    {
        $notification = Notification\Settings\CoinsConversion::I($user);

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
        // Update state if required

        $toggleCurrency = $callbackAction->data['toggle_currency'] ?? null;

        if ($toggleCurrency)
        {
            Models\UsersCurrencyConvert::model()->toggleCurrency($user->userId, $toggleCurrency);
        }

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
        // Generate control-buttons

        $currencyOutIds = Models\UsersCurrencyConvert::model()->selectIds($user->userId);

        $page = (int) ($callbackAction->data['page'] ?? null);

        $grid = new CurrencyButtonsGrid(Models\CurrencyOut::model()->selectPublicFull(), $page,
            fn (array $item): bool => in_array($item['currency_out_id'], $currencyOutIds),
            fn (array $item): string => $caBuilder->newEventAuid('coins-conversion', [
                'toggle_currency' => $item['currency_out_id'],
                'page' => $page,
            ]),
            fn (int $i): string => $caBuilder->newEventAuid('coins-conversion', ['page'=>$i]),
            $caBuilder->newEventAuid());

        $buttonsNavigation = $grid->navigation;
        $buttonsCurrency   = $grid->currencies;
        $buttonsPerRow     = $grid->buttonsPerRow;

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
        // Generate message

        if ($currencyOutIds)
        {
            foreach ($currencyOutIds as $currencyOutId)
            {
                $currencyItem = Models\CurrencyOut::model()->getItemById($currencyOutId);
                $notification->appendCurrencyName($currencyItem['name']);
            }
        }
        else
        {
            $notification->nothingSelected();
        }

        $notification->appendButtonsAsRows($buttonsNavigation, 100);
        $notification->appendButtonsAsRows($buttonsCurrency, $buttonsPerRow);

        return $notification;
    }
}
