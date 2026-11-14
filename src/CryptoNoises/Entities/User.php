<?php

namespace CryptoNoises\Entities;

use CryptoNoises\Bots\Core\SlashCommand;
use CryptoNoises\Core\Log;
use CryptoNoises\Models;
use CryptoNoises\Networks\Tezos\TezosNetwork;

class User
{
    public /* readonly */ int $userId;
    public /* readonly */ bool $isJustCreatedUser = false;
    private bool $isActive;

    public /* readonly */ string $platform;
    public /* readonly */ ?int $platformUserId;
    public /* readonly */ ?int $platformChannelId;

    public ?UserLimits $limits = null;

    private ?OptionsNotifier $options = null;
    private ?UserRedirect $redirect = null;

    //------------------------------------------------------------------------------------------------------------------

    private function __construct(array $userItem)
    {
        $this->userId               = $userItem['user_id'];
        $this->isActive             = $userItem['status'] == Models\Users::kSTATUS_Active;

        $this->platform             = $userItem['platform'];
        $this->platformUserId       = $userItem['platform_user_id'];
        $this->platformChannelId    = $userItem['platform_channel_id'];
    }

    //------------------------------------------------------------------------------------------------------------------

    // No in-memory cache: the Discord service runs two loops (main and notifier), and a cached User in one loop
    // would not see changes made in the other.
    /**
     * @param bool $activateIfFrozen true only where the user proves to be back (an incoming message / interaction)
     */
    public static function GetByUserId(int $userId, bool $activateIfFrozen = false): ?self
    {
        $userItem = Models\Users::model()->getUserItemById($userId);

        if ($userItem == null)
            return null;

        $instance = new self($userItem);
        $instance->limits = new UserLimits();

        if ($userItem['type'] == Models\Users::kTYPE_User)
        {
            if ($activateIfFrozen && $userItem['status'] == Models\Users::kSTATUS_Frozen)
                $instance->activate();

            Models\QueueWalletsSync::model()->generateTasksForOutdatedWalletsByUserId($instance->userId);
        }

        return $instance;
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    public static function GetByPlatformUserId(string $platform, int $platformUserId, int $platformChannelId): ?self
    {
        $userId = Models\Users::model()->getUserIdByIds($platform, $platformUserId, $platformChannelId);

        return $userId ? self::GetByUserId($userId) : null;
    }

    public static function GetOrCreatePlatformUser(string $platform, int $platformUserId, int $platformChannelId): ?self
    {
        $userId = Models\Users::model()->getUserIdByIds($platform, $platformUserId, $platformChannelId);

        if ($userId == null)
        {
            $userId = Models\Users::model()->createByIds(Models\Users::kTYPE_User, $platform, $platformUserId, $platformChannelId);

            self::InitializeCreatedUser($userId);

            Log::Event_UserCreated($platform, $userId);

            $isJustCreatedUser = true;
        }
        else
        {
            $isJustCreatedUser = false;
        }

        $user = self::GetByUserId($userId, true);
        $user->isJustCreatedUser = $isJustCreatedUser;
        return $user;
    }

    //------------------------------------------------------------------------------------------------------------------

    private static function InitializeCreatedUser(int $userId): void
    {
        // Default currencies
        Models\UsersCurrencyConvert::model()->bindCurrency($userId, 'BTC');
        Models\UsersCurrencyConvert::model()->bindCurrency($userId, 'USD');

        // Default tracked rates
        Models\UsersCurrencyTrack::model()->saveItem($userId, 'BTC', 'USD');
        Models\UsersCurrencyTrack::model()->saveItem($userId, 'BTC', 'ETH');

        Models\UsersCurrencyTrack::model()->saveItem($userId, 'ETH', 'USD');
        Models\UsersCurrencyTrack::model()->saveItem($userId, 'ETH', 'BTC');

        Models\UsersCurrencyTrack::model()->saveItem($userId, 'SOL', 'USD');
        Models\UsersCurrencyTrack::model()->saveItem($userId, 'SOL', 'BTC');

        Models\UsersCurrencyTrack::model()->saveItem($userId, 'XTZ', 'USD');
        Models\UsersCurrencyTrack::model()->saveItem($userId, 'XTZ', 'BTC');
    }

    //------------------------------------------------------------------------------------------------------------------

    public function setUserAttributes(array $fields): void
    {
        if (!empty($fields))
            Models\Users::model()->update($this->userId, $fields);
    }

    //------------------------------------------------------------------------------------------------------------------

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function activate(): void
    {
        Models\Users::model()->update($this->userId, [
            'status'        => Models\Users::kSTATUS_Active,
            'last_notice'   => null,
        ]);

        $this->isActive = true;
    }

    public function deactivate($notice = null): void
    {
        Models\Users::model()->update($this->userId, [
            'status'        => Models\Users::kSTATUS_Frozen,
            'last_notice'   => $notice,
        ]);

        $this->isActive = false;
    }

    //------------------------------------------------------------------------------------------------------------------
    // Wallets

    public function getWalletByAdid(int $walletAdid): ?UserWallet
    {
        return UserWallet::UserWalletByAdid($this->userId, $walletAdid);
    }

    public function getWalletByAddress(string $address): ?UserWallet
    {
        return UserWallet::UserWalletByAddress($this->userId, $address);
    }

    //------------------------------------------------------------------------------------------------------------------

    /**
     * @return UserWallet[]
     */
    public function getWalletsList(): array
    {
        $walletAdids = Models\UsersRefWallets::model()->getWalletAdidsByUserId($this->userId);

        $wallets = [];

        foreach ($walletAdids as $walletAdid)
        {
            $wallet = $this->getWalletByAdid($walletAdid);

            if ($wallet == null)
            {
                Log::Error(get_class($this), __FUNCTION__, "Cannot get wallet instance adid=$walletAdid for userId #$this->userId");
                continue;
            }

            $wallets[] = $wallet;
        }

        return $wallets;
    }

    public function isWalletExistsByAddress(string $address): bool
    {
        $addressAdid = Address::GetAdid($address);

        if ($addressAdid === null)
            return false;

        return Models\UsersRefWallets::model()->getRefItem($this->userId, $addressAdid) !== null;
    }

    public function getWalletsCount(): int
    {
        return Models\UsersRefWallets::model()->getWalletsCount($this->userId);
    }

    public function createWallet($address, $walletName): ?UserWallet
    {
        $walletAdid = Address::GetOrCreateAdid($address);

        Models\Wallets::model()->create($walletAdid, $address, TezosNetwork::kCURRENCY_ID);
        Models\UsersRefWallets::model()->insertRef($this->userId, $walletAdid);

        $wallet = $this->getWalletByAdid($walletAdid);

        if ($wallet == null)
            return null;

        Models\QueueWalletsSync::model()->push($walletAdid, $address, Models\QueueWalletsSync::kPRIORITY_High);

        if ($walletName)
            $wallet->setWalletName($walletName);

        Log::Event_WalletAdded($this->platform, $this->userId, $address);
        return $wallet;
    }

    //------------------------------------------------------------------------------------------------------------------

    public function options(): OptionsNotifier
    {
        if ($this->options === null)
            $this->options = OptionsNotifier::NewForUser($this->userId);

        return $this->options;
    }

    public function redirect(): UserRedirect
    {
        if ($this->redirect === null)
            $this->redirect = UserRedirect::Get($this->userId);

        return $this->redirect;
    }

    //------------------------------------------------------------------------------------------------------------------
    // Following

    public function follow($address)
    {
        $addressAdid = Address::GetOrCreateAdid($address);
        return Models\UsersFollow::model()->insert($this->userId, $addressAdid, $address);
    }

    public function unfollow($address)
    {
        $addressAdid = Address::GetOrCreateAdid($address);
        return Models\UsersFollow::model()->delete($this->userId, $addressAdid);
    }

    public function getFollowing(): array
    {
        return Models\UsersFollow::model()->selectByUserId($this->userId);
    }

    public function getFollowingCount(): int
    {
        return Models\UsersFollow::model()->selectFollowingCount($this->userId);
    }

    //------------------------------------------------------------------------------------------------------------------
    // Next Actions

    private ?array $nextAction;

    public function readNextActionAndCleanUp(): void
    {
        $this->nextAction = Models\UsersAction::model()->getAction($this->userId);

        if ($this->nextAction != null)
            Models\UsersAction::model()->cleanUpByUserId($this->userId);
    }

    public function getNextAction(): ?array
    {
        return $this->nextAction;
    }

    public function setNextAction_Wallet_Add(): void
    {
        Models\UsersAction::model()->setAction($this->userId, SlashCommand::ADD_WALLET, [], 15*60);
    }

    public function setNextAction_Wallet_Delete(): void
    {
        Models\UsersAction::model()->setAction($this->userId, SlashCommand::DELETE_WALLET, [], 15*60);
    }

    public function setNextAction_Wallet_Rename($walletAdid, $walletMessageId): void
    {
        Models\UsersAction::model()->setAction($this->userId, SlashCommand::RENAME_WALLET, [
            'wallet_adid'       => $walletAdid,
            'wallet_message_id' => $walletMessageId,
        ], 15*60);
    }

    public function setNextAction_Follow(): void
    {
        Models\UsersAction::model()->setAction($this->userId, SlashCommand::FOLLOW, [], 15*60);
    }

    public function setNextAction_Unfollow(): void
    {
        Models\UsersAction::model()->setAction($this->userId, SlashCommand::UNFOLLOW, [], 15*60);
    }

    public function cancelNextAction(): void
    {
        Models\UsersAction::model()->cleanUpByUserId($this->userId);
    }
}
