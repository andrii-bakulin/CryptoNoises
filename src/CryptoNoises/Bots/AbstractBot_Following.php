<?php

namespace CryptoNoises\Bots;

use CryptoNoises\Config;
use CryptoNoises\Entities\Address;
use CryptoNoises\Entities\CallbackAction;
use CryptoNoises\Entities\User;
use CryptoNoises\Helpers\Helper;
use CryptoNoises\Localize\Notification;
use CryptoNoises\Localize\NotificationButton;
use CryptoNoises\Localize\NotificationDiscord;
use CryptoNoises\Localize\Phrase;
use CryptoNoises\Networks\AbstractNetwork;
use CryptoNoises\Networks\Tezos\Services\TezosDomains;
use CryptoNoises\Networks\Tezos\TezosNetwork;

trait AbstractBot_Following
{
    public function doFollowing(User $user, ?CallbackAction $callbackAction=null): ?Notification
    {
        switch ($callbackAction ? $callbackAction->event : null)
        {
            case 'follow.how-to':
                return $this->getNotificationInfo_HowToFollow($user);

            case 'unfollow.how-to':
                return $this->getNotificationInfo_HowToUnfollow($user);

            case 'cancel-user-action':
            default: // any other event: drop the pending user action
                $user->cancelNextAction();
                break;
        }

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $pageIndex      = (int) ($callbackAction->data['page'] ?? null);
        $isExtendData   = (int) ($callbackAction->data['extend'] ?? null);

        $caBuilder = CallbackAction::Builder(CallbackAction::Scope_Following, ['page'=>$pageIndex, 'extend'=>$isExtendData]);

        $followingAddressList = $user->getFollowing();

        if ($followingAddressList)
        {
            $itemsPerPage   = Config::kUI_FollowingPerPage;
            $itemsCount     = count($followingAddressList);

            $pagesCount     = ceil($itemsCount/$itemsPerPage);
            $pageIndex      = max($pageIndex, 0);
            $pageIndex      = max(0, min($pageIndex, $pagesCount-1));

            $notification = Notification\Following\UsersList::I($user);

            $index              = $pageIndex*$itemsPerPage;
            $followingAddressList = array_slice($followingAddressList, $index, $itemsPerPage);

            $network = AbstractNetwork::GetNetworkById(Config::kNETWORK_Tezos);

            foreach ($followingAddressList as $address)
            {
                $notification->appendAddress(++$index,
                    $network->getWalletPublicName($address),
                    $isExtendData ? $address : null);
            }

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            $buttons = [
                NotificationButton::CallbackAction(Phrase::I()->actionFollow,   $caBuilder->newEventAuid('follow.how-to')),
                NotificationButton::CallbackAction(Phrase::I()->actionUnfollow, $caBuilder->newEventAuid('unfollow.how-to')),
            ];

            if ($isExtendData)
                $buttons[] = NotificationButton::CallbackAction(Phrase::I()->actionCollapse, $caBuilder->newEventAuid('',['extend'=>0]));
            else
                $buttons[] = NotificationButton::CallbackAction(Phrase::I()->actionExpand, $caBuilder->newEventAuid('',['extend'=>1]));

            $notification->appendButtonsRow($buttons);
            $notification->appendPagerControl($caBuilder, $pageIndex, $itemsCount, $itemsPerPage);
        }
        else
        {
            $notification = Notification\Following\UsersListIsEmpty::I($user);

            $notification->appendButtonsRow([
                NotificationButton::CallbackAction(Phrase::I()->actionFollow, $caBuilder->newEventAuid('follow.how-to')),
            ]);
        }

        return $notification;
    }

    //------------------------------------------------------------------------------------------------------------------

    public function doFollow(User $user, ?string $message, ?CallbackAction $callbackAction = null): ?Notification
    {
        return $this->doFollowInnerLogic(true, $user, $message, $callbackAction);
    }

    public function doUnfollow(User $user, ?string $message, ?CallbackAction $callbackAction = null): ?Notification
    {
        return $this->doFollowInnerLogic(false, $user, $message, $callbackAction);
    }

    private function doFollowInnerLogic(bool $logicToFollow, User $user, ?string $message, ?CallbackAction $callbackAction = null): ?Notification
    {
        $message = trim((string) $message);

        switch ($callbackAction ? $callbackAction->event : null)
        {
            case 'follow.modal.show':
                $caBuilder = CallbackAction::Builder(CallbackAction::Scope_Follow);

                $notification = NotificationDiscord\Following\FollowModal::I($user);
                $notification->setCallbackAuid($caBuilder->newEventAuid('follow.modal.submit'));
                return $notification;

            case 'unfollow.modal.show':
                $caBuilder = CallbackAction::Builder(CallbackAction::Scope_Unfollow);

                $notification = NotificationDiscord\Following\UnfollowModal::I($user);
                $notification->setCallbackAuid($caBuilder->newEventAuid('unfollow.modal.submit'));
                return $notification;

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            case 'follow.modal.submit':
                $message = trim($callbackAction->data['modal']['ds.following.follow.address'] ?? '');
                break;

            case 'unfollow.modal.submit':
                $message = trim($callbackAction->data['modal']['ds.following.unfollow.address'] ?? '');
                break;
        }

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        if ($message == '')
        {
            return $logicToFollow
                ? $this->getNotificationInfo_HowToFollow($user)
                : $this->getNotificationInfo_HowToUnfollow($user);
        }

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        if ($logicToFollow == false && $message === 'UNFOLLOW_ALL')
            $addressList = $user->getFollowing();
        else
            $addressList = Helper::SplitBySpaces($message);

        $resultSuccess = [];
        $resultFailed = [];

        $didReachLimit = false;

        $network = AbstractNetwork::GetNetworkById(Config::kNETWORK_Tezos);

        foreach ($addressList as $addressRaw)
        {
            $address = Address::Normalize($addressRaw);
            $domain = null;

            if (TezosNetwork::IsDomainAddressValid($address))
            {
                $domain = $address;
                $address = TezosDomains::GetAddressByDomain($domain);
            }

            if (!TezosNetwork::IsWalletAddressValid($address))
            {
                $resultFailed[] = $addressRaw;
                continue;
            }

            if ($logicToFollow)
            {
                $count = $user->getFollowingCount();

                if ($count >= $user->limits->followingCount)
                {
                    $didReachLimit = true;
                    break;
                }

                $user->follow($address);
            }
            else
            {
                $user->unfollow($address);
            }

            if ($domain)
                $resultSuccess[] = $domain.' ('.$network->getWalletShortName($address).')';
            else
                $resultSuccess[] = $address;
        }

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $notifications = [];

        if (count($resultSuccess) > 0)
        {
            $notification = Notification\ProcessingResult::Success($user, $logicToFollow ? 'follow' : 'unfollow');
            $notification->addWalletNames($resultSuccess);
            $notifications[] = $notification;
        }

        if (count($resultFailed) > 0)
        {
            $notification = Notification\ProcessingResult::Failed($user, $logicToFollow ? 'follow' : 'unfollow');
            $notification->addWalletNames($resultFailed);
            $notifications[] = $notification;
        }

        if ($didReachLimit)
        {
            $notifications[] = Notification\Following\ReachLimit::I($user);
        }

        // Append buttons only for 1 message (last)
        if (count($notifications) > 0)
        {
            $buttons = [];

            $caBuilder = CallbackAction::Builder(CallbackAction::Scope_Following);

            if ($logicToFollow)
            {
                if (self::IsTelegram())
                {
                    $buttons[] = NotificationButton::CallbackAction(Phrase::I()->actionFollowMore,
                        $caBuilder->newEventAuid('follow.how-to'));
                }
                else if(self::IsDiscord())
                {
                    $buttons[] = NotificationButton::CallbackAction(Phrase::I()->actionFollowMore,
                        CallbackAction::Builder(CallbackAction::Scope_Follow)->newEventAuid('follow.modal.show'));
                }
            }
            else if ($user->getFollowingCount() > 0)
            {
                if (self::IsTelegram())
                {
                    $buttons[] = NotificationButton::CallbackAction(Phrase::I()->actionUnfollowMore,
                        $caBuilder->newEventAuid('unfollow.how-to'));
                }
                else if(self::IsDiscord())
                {
                    $buttons[] = NotificationButton::CallbackAction(Phrase::I()->actionUnfollowMore,
                        CallbackAction::Builder(CallbackAction::Scope_Unfollow)->newEventAuid('unfollow.modal.show'));
                }
            }

            $buttons[] =NotificationButton::CallbackAction(Phrase::I()->actionFollowing, $caBuilder->newEventAuid());

            $notifications[count($notifications)-1]->appendButtonsRow($buttons);
        }

        return Notification::ConvertListToQueue($notifications);
    }

    private function getNotificationInfo_HowToFollow(User $user): Notification
    {
        $notification = Notification\Following\HowToFollow::I($user);

        $buttons = [];

        if (self::IsTelegram())
        {
            $user->setNextAction_Follow();
        }
        else if(self::IsDiscord())
        {
            $buttons[] = NotificationButton::CallbackAction(Phrase::I()->actionAdd,
                CallbackAction::Builder(CallbackAction::Scope_Follow)->newEventAuid('follow.modal.show'));
        }

        $buttons[] = NotificationButton::CallbackAction(Phrase::I()->actionCancel,
            CallbackAction::Builder(CallbackAction::Scope_Following)->newEventAuid('cancel-user-action'));

        $notification->appendButtonsRow($buttons);
        return $notification;
    }

    private function getNotificationInfo_HowToUnfollow(User $user): ?Notification
    {
        $notification = Notification\Following\HowToUnfollow::I($user);

        $buttons = [];

        if (self::IsTelegram())
        {
            $user->setNextAction_Unfollow();
        }
        else if(self::IsDiscord())
        {
            $buttons[] = NotificationButton::CallbackAction(Phrase::I()->actionDelete,
                CallbackAction::Builder(CallbackAction::Scope_Unfollow)->newEventAuid('unfollow.modal.show'));
        }

        $buttons[] = NotificationButton::CallbackAction(Phrase::I()->actionCancel,
            CallbackAction::Builder(CallbackAction::Scope_Following)->newEventAuid('cancel-user-action'));

        $notification->appendButtonsRow($buttons);
        return $notification;
    }
}
