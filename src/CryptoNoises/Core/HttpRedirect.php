<?php

namespace CryptoNoises\Core;

final class HttpRedirect
{
    /**
     * Redirects to $url, or answers 404 when the target is not configured.
     */
    public static function ToOr404(?string $url): void
    {
        if ($url === null || $url === '')
        {
            http_response_code(404);
            return;
        }

        header('Location: '.$url);
    }
}
