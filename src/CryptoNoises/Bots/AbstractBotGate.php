<?php

namespace CryptoNoises\Bots;

use CryptoNoises\Localize\Notification;

interface AbstractBotGate
{
    public function sendNotifications(?Notification $notification): void;
}
