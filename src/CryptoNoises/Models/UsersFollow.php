<?php

namespace CryptoNoises\Models;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class UsersFollow extends VssModel
{
    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'users_follow';
    }

    public function insert($userId, $followingAdid, $followingAddress)
    {
        return Vss::db()->insert($this->tableName(), [
            'user_id'           => $userId,
            'following_adid'    => $followingAdid,
            'following_address' => $followingAddress,
        ], 'IGNORE');
    }

    public function delete($userId, $followingAdid)
    {
        return Vss::db()->delete($this->tableName(), [
            'user_id'           => $userId,
            'following_adid'    => $followingAdid,
        ]);
    }

    public function selectByUserId($userId): array
    {
        $sql = "
            SELECT following_address
            FROM {$this->tableName()}
            WHERE user_id = :user_id
            ORDER BY ts_created ASC
        ";

        return Vss::db()->queryColumn($sql, ['user_id'=>$userId]);
    }

    public function selectFollowingCount($userId): int
    {
        $sql = "
            SELECT COUNT(*)
            FROM {$this->tableName()}
            WHERE user_id = :user_id
        ";

        return (int) Vss::db()->queryScalar($sql, ['user_id'=>$userId]);
    }

    public function selectUserIdsFollowersToAdid($followingAdid): array
    {
        $sql = "
            SELECT user_id
            FROM {$this->tableName()}
            WHERE following_adid = :following_adid
        ";

        return Vss::db()->queryColumn($sql, ['following_adid'=>$followingAdid]);
    }

    //------------------------------------------------------------------------------------------------------------------

    public function search(int $userId, string $query, int $limit): ?array
    {
        $query = preg_replace('/[%_]/', '', $query); // to prevent MySQL long query in LIKE rule

        $sql = "
            SELECT a.address AS address
            FROM {$this->tableName()} AS f
            JOIN ".Address::model()->tableName()." AS a
                ON f.following_adid = a.adid
            WHERE f.user_id = :user_id
                AND a.address_ci LIKE :address
            LIMIT :limit
        ";

        return Vss::db()->query($sql, [
            'user_id'           => $userId,
            'address'           => $query.'%',
            'limit'             => $limit,
        ]);
    }
}
