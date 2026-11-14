<?php

namespace CryptoNoises\Entities;

use CryptoNoises\Core\Log;
use CryptoNoises\Helpers\Icons;
use CryptoNoises\Models;

class UserWallet extends Wallet
{
    protected int $userId;

    private ?OptionsNotifier $options = null;
    protected ?array $refConfig;

    //------------------------------------------------------------------------------------------------------------------

    public static function UserWalletByAdid(int $userId, int $walletAdid): ?UserWallet
    {
        $wallet = new self();

        return $wallet->initializeUserWallet($userId, $walletAdid) ? $wallet : null;
    }

    public static function UserWalletByAddress(int $userId, string $address): ?UserWallet
    {
        $walletAdid = Address::GetAdid($address);

        if ($walletAdid == null)
            return null;

        return self::UserWalletByAdid($userId, $walletAdid);
    }

    //------------------------------------------------------------------------------------------------------------------

    private function initializeUserWallet($userId, $walletAdid): bool
    {
        $this->userId = $userId;

        $this->refConfig = Models\UsersRefWallets::model()->getRefItem($userId, $walletAdid);

        if ($this->refConfig == null)
        {
            // No need to log here. Just return FALSE
            return false;
        }

        return $this->initializeWallet($walletAdid);
    }

    //------------------------------------------------------------------------------------------------------------------
    // Getter

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getWalletName(): string
    {
        if ($this->refConfig['wallet_name'])
            return $this->refConfig['wallet_name'];

        return parent::getWalletName();
    }

    public function setWalletName(?string $name): bool
    {
        if (empty($name))
            return false;

        if (mb_strlen($name) > 128)
        {
            Log::Warning(get_class($this), __FUNCTION__, "Too long wallet name (len=".mb_strlen($name).")", $name);
            return false;
        }

        $this->refConfig['wallet_name'] = $name;

        Models\UsersRefWallets::model()->updateValues($this->userId, $this->walletAdid, [
            'wallet_name' => $name,
        ]);
        return true;
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    public function getIconId(): ?string
    {
        return $this->refConfig['wallet_icon'];
    }

    public function getIconName(): ?string
    {
        return Icons::GetWalletIcon($this->getIconId());
    }

    public function setIconId($iconId)
    {
        $this->refConfig['wallet_icon'] = $iconId;

        Models\UsersRefWallets::model()->updateValues($this->userId, $this->walletAdid, [
            'wallet_icon' => $iconId,
        ]);
    }

    //------------------------------------------------------------------------------------------------------------------

    public function options(): OptionsNotifier
    {
        if ($this->options === null)
            $this->options = OptionsNotifier::NewForUserWallet($this->userId, $this->walletAdid);

        return $this->options;
    }

    //------------------------------------------------------------------------------------------------------------------

    public function deleteUserWallet(): bool
    {
        Models\UsersRefWallets::model()->deleteRef($this->userId, $this->walletAdid);

        if (Models\UsersRefWallets::model()->getRefsCountByWalletAdid($this->walletAdid) == 0)
            Models\Wallets::model()->freeze($this->walletAdid);

        Log::Event_WalletDeleted($this->userId, $this->walletAddress);
        return true;
    }
}
