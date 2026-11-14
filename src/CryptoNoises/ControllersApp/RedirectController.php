<?php

namespace CryptoNoises\ControllersApp;

use CryptoNoises\Models;
use CryptoCore\Vss\Vss;
use Exception;

class RedirectController
{
    public function token(): void
    {
        $scope   = 'token/'.Vss::request()->getUriVar('scope');
        $address = (string) Vss::request()->getUriVar('address');
        $tokenId = (string) Vss::request()->getUriVar('token');

        $this->redirect(['scope' => $scope, 'address' => $address ?: null, 'token_id' => $tokenId ?: null],
            function () use ($scope, $address, $tokenId): string
            {
                $redirectUrl = $this->getUrlPattern($scope);
                $redirectUrl = str_replace('{$TOKEN_ADDRESS$}', rawurlencode($address), $redirectUrl);
                return str_replace('{$TOKEN_ID$}', rawurlencode($tokenId), $redirectUrl);
            });
    }

    public function user(): void
    {
        $this->redirectByAddress('user/'.Vss::request()->getUriVar('scope'));
    }

    public function contract(): void
    {
        $this->redirectByAddress('contract/'.Vss::request()->getUriVar('scope'));
    }

    public function collection(): void
    {
        $scope   = 'collection/'.Vss::request()->getUriVar('scope');
        $address = (string) Vss::request()->getUriVar('address');

        $this->redirect(['scope' => $scope, 'address' => $address ?: null],
            function () use ($scope, $address): string
            {
                $redirectUrl = null;

                if ($address)
                    $redirectUrl = $this->getCollectionRedirectUrl("$scope/$address");

                if ($redirectUrl == null)
                    $redirectUrl = $this->getCollectionRedirectUrl($scope, $address);

                return $redirectUrl ?? "https://objkt.com/collection/".rawurlencode($address);
            });
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    private function redirectByAddress(string $scope): void
    {
        $address = (string) Vss::request()->getUriVar('address');

        $this->redirect(['scope' => $scope, 'address' => $address ?: null],
            function () use ($scope, $address): string
            {
                return str_replace('{$ADDRESS$}', rawurlencode($address), $this->getUrlPattern($scope));
            });
    }

    /**
     * Sends the Location header for the URL built by $buildUrl (404 on error) and logs the redirect.
     */
    private function redirect(array $logItem, callable $buildUrl): void
    {
        try
        {
            $redirectUrl = $buildUrl();

            $logItem['status'] = 'success';
            $logItem['redirect_to_url'] = $redirectUrl;

            header("Location: ".$redirectUrl);
        }
        catch (Exception $e)
        {
            $logItem['status'] = 'error';
            $logItem['notice'] = $e->getMessage();

            http_response_code(404);
        }

        Models\Log\Redirects::model()->fix($logItem);
    }

    /**
     * @throws Exception
     */
    private function getUrlPattern(string $scope): string
    {
        $marketUrlItem = Models\Shared\MarketUrls::model()->getItemById($scope);

        if ($marketUrlItem == null)
            throw new Exception("Cannot get marketUrlItem item by id=[$scope]");

        return (string) $marketUrlItem['external_url_pattern'];
    }

    private function getCollectionRedirectUrl(string $scope, ?string $address = null): ?string
    {
        $marketUrlItem = Models\Shared\MarketUrls::model()->getItemById($scope);

        if ($marketUrlItem == null || $marketUrlItem['external_url_pattern'] == null)
            return null;

        $redirectUrl = $marketUrlItem['external_url_pattern'];

        if ($address)
            $redirectUrl = str_replace('{$COLLECTION_URI_ID$}', rawurlencode($address), $redirectUrl);

        return $redirectUrl;
    }
}
