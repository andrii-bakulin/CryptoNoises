<?php

namespace CryptoNoises\Models;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class Exceptions extends VssModel
{
    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'exceptions';
    }

    //------------------------------------------------------------------------------------------------------------------

    public static function IsRequiredToIgnoreTokenOffers(?string $tokenAddress, ?string $tokenId): bool
    {
        return self::model()->checkTokenException('token.offer.ignore', $tokenAddress, $tokenId);
    }

    public static function MarkTokenOffersIgnored(?string $tokenAddress, ?string $tokenId, ?string $notice): void
    {
        self::model()->addRecord('token.offer.ignore', $tokenAddress, $tokenId, $notice);
    }

    //------------------------------------------------------------------------------------------------------------------

    public static function IsRequiredToIgnoreCollectionOffers(?string $tokenAddress, ?string $collectionId): bool
    {
        return self::model()->checkTokenException('collection.offer.ignore', $tokenAddress, $collectionId);
    }

    public static function MarkCollectionOffersIgnored(?string $tokenAddress, ?string $collectionId, ?string $notice): void
    {
        self::model()->addRecord('collection.offer.ignore', $tokenAddress, $collectionId, $notice);
    }

    //------------------------------------------------------------------------------------------------------------------

    private function checkTokenException(string $exceptionType, ?string $tokenAddress, ?string $tokenId): bool
    {
        $sql = "
            SELECT COUNT(*) 
            FROM {$this->tableName()} 
            WHERE exception_type = :exception_type
              AND token_address = :token_address
              AND (token_id IS NULL OR token_id = :token_id) 
        ";
        $rules = [
            'exception_type'    => $exceptionType,
            'token_address'     => $tokenAddress,
            'token_id'          => $tokenId,
        ];

        $count = Vss::db()->queryScalar($sql, $rules);

        if ($count == 0)
            return false;

        $sql = "
            UPDATE {$this->tableName()}
            SET hits = hits+1
            WHERE exception_type = :exception_type
              AND token_address = :token_address
              AND (token_id IS NULL OR token_id = :token_id) 
        ";
        Vss::db()->query($sql, $rules);

        Log\ExceptionsHits::model()->fix([
            'exception_type'    => $exceptionType,
            'token_address'     => $tokenAddress,
            'token_id'          => $tokenId,
        ]);
        return true;
    }

    private function addRecord(string $exceptionType, ?string $tokenAddress, ?string $tokenId, ?string $notice): void
    {
        Vss::db()->insert($this->tableName(), [
            'exception_type'    => $exceptionType,
            'token_address'     => $tokenAddress,
            'token_id'          => $tokenId,
            'notice'            => $notice,
        ], 'IGNORE');
    }
}
