<?php

namespace CryptoNoises\Bots;

use CryptoNoises\Entities\User;
use CryptoNoises\Localize\Notification;
trait AbstractBot_Notification_Informs
{
    public function doNotifyInformFlow(User $user, array $informItem): ?Notification
    {
        $notification = Notification\InformFlowMessage::I($user);
        $notification->setTitle($informItem['title']);
        $notification->setDescription($informItem['message']);
        $notification->setPreviewThumbnailUrl($informItem['image_url']);

        return $notification;
    }
}
