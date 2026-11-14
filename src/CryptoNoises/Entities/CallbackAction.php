<?php

namespace CryptoNoises\Entities;

use CryptoNoises\Helpers\Helper;
use CryptoNoises\Localize\NotificationButton;
use CryptoNoises\Models;
use CryptoCore\Time;
use Exception;

class CallbackAction
{
    const Scope_Summary                 = 'summary';
    const Scope_Wallets_Details         = 'wallets-details';
    const Scope_Wallets_Add             = 'wallets-add';
    const Scope_Wallets_Delete          = 'wallets-delete';
    const Scope_Following               = 'following';
    const Scope_Follow                  = 'follow';
    const Scope_Unfollow                = 'unfollow';
    const Scope_Domains                 = 'domains';
    const Scope_Offers                  = 'offers';
    const Scope_CoinsRates              = 'coins-rates';
    const Scope_Settings                = 'settings';
    const Scope_Help                    = 'help';

    const Scope_RedirectDM              = 'redirect_dm';

    const Scope_System_CancelLastAction = 'cancel-last-action';

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    private ?string $builderPrefix = null;
    private int     $builderIndex = 0;

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    public /*readonly*/ ?string $scope = null;
    public /*readonly*/ ?string $event = null;
    public /*readonly*/ array $data = [];

    public ?string $parentMessageId = null;

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    private function __construct()
    {
    }

    public static function Initialize($incomingCallbackId): ?self
    {
        list ($callbackPrefix, $callbackAuid) = array_pad(explode(':', trim((string) $incomingCallbackId), 2), 2, null);

        if ($callbackPrefix !== NotificationButton::CALLBACK_AUID_PREFIX)
            return null;

        $data = Models\CallbackActions::model()->get($callbackAuid);

        if ($data === null)
            return null;

        list ($scope, $event, $data) = $data;

        $cbAction = new self();
        $cbAction->scope = $scope;
        $cbAction->event = $event;
        $cbAction->data  = $data;
        return $cbAction;
    }

    public static function Builder(string $scope, array $baseData=[]): self
    {
        $cbAction = new self();

        try
        {
            $cbAction->builderPrefix = Helper::Uniqid().'.'.dechex(random_int(0,PHP_INT_MAX));
        }
        catch (Exception $e)
        {
            $cbAction->builderPrefix = Time::Time().'.'.rand(0,PHP_INT_MAX);
        }

        $cbAction->scope = $scope;
        $cbAction->data = $baseData;

        return $cbAction;
    }

    /**
     * @param string $event
     * @param array $data
     * @return string AUID of new event-action
     */
    public function newEventAuid(string $event='', array $data=[]): string
    {
        $auid  = $this->builderPrefix.'.'.(++$this->builderIndex);
        $data  = array_merge($this->data, $data);

        Models\CallbackActions::model()->set($auid, $this->scope, $event, $data);

        return $auid;
    }

    //------------------------------------------------------------------------------------------------------------------

    public static function PackFullCustomId(string $callbackAuid): string
    {
        return NotificationButton::CALLBACK_AUID_PREFIX.':'.$callbackAuid;
    }
}
