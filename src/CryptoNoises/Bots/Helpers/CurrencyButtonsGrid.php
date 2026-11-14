<?php

namespace CryptoNoises\Bots\Helpers;

use CryptoNoises\Config;
use CryptoNoises\Localize\NotificationButton;
use CryptoNoises\Localize\Phrase;

/**
 * One page of currency buttons (🟢 selected, 🟠 crypto, 🟡 fiat) plus a navigation row (back + page numbers).
 */
class CurrencyButtonsGrid
{
    /** @var NotificationButton[] */
    public array $navigation = [];

    /** @var NotificationButton[] */
    public array $currencies = [];

    public int $buttonsPerRow;

    /**
     * @param array    $currencies   rows of CurrencyOut::selectPublicFull()
     * @param callable $isSelected   function (array $currency): bool
     * @param callable $currencyAuid function (array $currency): string - callback id of a currency button
     * @param callable $pageAuid     function (int $page): string - callback id of a page button
     */
    public function __construct(array $currencies, int $page, callable $isSelected, callable $currencyAuid,
                                callable $pageAuid, string $backAuid)
    {
        $buttonsPerPage      = Config::cfg()->uiCoinsCurrencyPerPage;
        $this->buttonsPerRow = Config::cfg()->uiCoinsCurrencyPerRow;
        $pageCount           = ceil(count($currencies) / $buttonsPerPage);

        foreach (array_slice($currencies, $page*$buttonsPerPage, $buttonsPerPage) as $item)
        {
            $isSelectedItem = $isSelected($item);
            $icon = $isSelectedItem ? '🟢' : ($item['type'] == 'crypto' ? '🟠' : '🟡');

            $button = NotificationButton::CallbackAction($icon.$item['name'], $currencyAuid($item));

            if ($isSelectedItem)    $button->setStyleSuccess();
            else                    $button->setStyleSecondary();

            $this->currencies[] = $button;
        }

        $this->navigation[] = NotificationButton::CallbackAction(Phrase::I()->actionBack, $backAuid);

        for ($i=0; $i<$pageCount; $i++)
        {
            $button = NotificationButton::CallbackAction(Phrase::PageIndex($i+1, $i==$page), $pageAuid($i));

            if ($i == $page)    $button->setStylePrimary();
            else                $button->setStyleSecondary();

            $this->navigation[] = $button;
        }
    }
}
