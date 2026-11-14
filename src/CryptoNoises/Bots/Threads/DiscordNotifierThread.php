<?php

namespace CryptoNoises\Bots\Threads;

use CryptoNoises\Bots\Core\NotificationSendSkippedException;
use CryptoNoises\Bots\Gates\DiscordBotGate;
use CryptoNoises\Config;
use CryptoNoises\Models;
use CryptoCore\Time;
use CryptoCore\Vss\Vss;
use Discord\Discord;
use Exception;

class DiscordNotifierThread extends DiscordAbstractThread
{
    public function run(): bool
    {
        if ($this->createDiscordInstance() == false)
            return false;

        //--------------------------------------------------------------------------------------------------------------

        $iteration = 0;

        $this->discord->on('ready', function (Discord $discord) use (&$iteration)
        {
            $this->discord->getLogger()->notice("NOTIFIER READY");

            $this->discord->getLoop()->addPeriodicTimer(Config::kDiscord_Notifier_IdleTime, function ($timer) use (&$iteration)
            {
                if ($this->processEventNotification() === null && $this->processInformNotification() === null)
                {
                    $this->discord->getLogger()->notice("No task #".(++$iteration));
                }

                Vss::dbCloseAll();
            });
        });

        $this->discord->run();
        return true;
    }

    //------------------------------------------------------------------------------------------------------------------

    private function processEventNotification(): ?int
    {
        $notificationItem = Models\EventsNotifications::model()->getNextToNotify(Config::kPLATFORM_Discord);

        if ($notificationItem == null)
            return null;

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $tsTaskStart = Time::Microtime();
        $notificationId = $notificationItem['event_notification_id'];
        $resultNote = '';

        try
        {
            Models\EventsNotifications::model()->updateNotifyStatus(Config::kPLATFORM_Discord, $notificationId, Models\EventsNotifications::kNotifyStatus_Active);

            $eventItem = Models\EventsFlow::model()->getEventById($notificationItem['event_flow_id']);

            if (empty($eventItem))
                throw new Exception("Cannot find event-flow #".$notificationItem['event_flow_id']);

            $userId = $notificationItem['user_id'];

            $botGate = DiscordBotGate::NewByUserId($this->discord, $userId);

            if ($botGate === null)
                throw new Exception('Cannot initialize DiscordBotGate by userId#'.$userId);

            if (!$botGate->isRecipientActive())
                throw new NotificationSendSkippedException('User is frozen');

            $botGate->notifyEventFlow($notificationItem, $eventItem, function (string $reason) use ($notificationId)
            {
                Models\EventsNotifications::model()->updateNotifyStatus(Config::kPLATFORM_Discord, $notificationId,
                    Models\EventsNotifications::kNotifyStatus_Error, null, $reason);
            });

            $status = Models\EventsNotifications::kNotifyStatus_Done;
        }
        catch (NotificationSendSkippedException $e)
        {
            $status = Models\EventsNotifications::kNotifyStatus_Skipped;
            $resultNote = $e->getMessage();
        }
        catch (Exception $e)
        {
            $status = Models\EventsNotifications::kNotifyStatus_Error;
            $resultNote = $e->getMessage();
        }

        Models\EventsNotifications::model()->updateNotifyStatus(Config::kPLATFORM_Discord, $notificationId,
            $status, Time::Microtime()-$tsTaskStart, $resultNote);

        $this->discord->getLogger()->notice("Sent event > $status $resultNote");
        return $notificationId;
    }

    private function processInformNotification(): ?int
    {
        $notificationItem = Models\InformNotifications::model()->getNextToNotify(Config::kPLATFORM_Discord);

        if ($notificationItem == null)
            return null;

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $tsTaskStart = Time::Microtime();
        $notificationId = $notificationItem['inform_notification_id'];

        try
        {
            Models\InformNotifications::model()->updateNotifyStatus(Config::kPLATFORM_Discord, $notificationId, Models\InformNotifications::kNotifyStatus_Active);

            $informItem = Models\InformFlow::model()->getInformById($notificationItem['inform_flow_id']);

            if (empty($informItem))
                throw new Exception("Cannot find inform-flow #".$notificationItem['inform_flow_id']);

            $userId = $notificationItem['user_id'];

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            $botGate = DiscordBotGate::NewByUserId($this->discord, $userId);

            if ($botGate === null)
                throw new Exception('Cannot initialize DiscordBotGate by userId#'.$userId);

            if (!$botGate->isRecipientActive())
                throw new NotificationSendSkippedException('User is frozen');

            $botGate->notifyInformFlow($notificationItem, $informItem, function (string $reason) use ($notificationId)
            {
                Models\InformNotifications::model()->updateNotifyStatus(Config::kPLATFORM_Discord, $notificationId,
                    Models\InformNotifications::kNotifyStatus_Error, null, $reason);
            });

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            $status = Models\InformNotifications::kNotifyStatus_Done;
            $resultNote = null;
        }
        catch (Exception $e)
        {
            $status = Models\InformNotifications::kNotifyStatus_Error;
            $resultNote = $e->getMessage();
        }

        Models\InformNotifications::model()->updateNotifyStatus(Config::kPLATFORM_Discord, $notificationId,
            $status, Time::Microtime()-$tsTaskStart, $resultNote);

        $this->discord->getLogger()->notice("Sent inform > $status $resultNote");
        return $notificationId;
    }
}
