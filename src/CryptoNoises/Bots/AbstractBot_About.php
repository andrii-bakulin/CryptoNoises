<?php

namespace CryptoNoises\Bots;

use CryptoNoises\Core\Log;
use CryptoNoises\Entities\User;
use CryptoNoises\Localize\Notification;

trait AbstractBot_About
{
    protected function doAboutToken(User $user, ?string $tokenContract, ?string $tokenId): ?Notification
    {
        if ($tokenContract === null || $tokenId === null)
            return Notification\AboutToken_HowToUse::I($user);

        $tokenEntity = \CryptoNoises\GatewayTezos\Tokens::GetTokenDetails($tokenContract, $tokenId, true);

        if ($tokenEntity === null)
        {
            Log::Notice('AbstractBot_About', 'doAboutToken', "$tokenContract:$tokenId", 'NOT-EXISTS');
            return Notification\AboutToken_NotExists::I($user)
                ->bind('{###TOKEN_CONTRACT###}', $tokenContract)
                ->bind('{###TOKEN_ID###}',       $tokenId);
        }

        Log::Notice('AbstractBot_About', 'doAboutToken', "$tokenContract:$tokenId", 'OK');
        return Notification\AboutToken::I($user)->setToken($tokenEntity);
    }
}
