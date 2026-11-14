<?php

namespace CryptoNoises\Bots\Core;

use Discord\Parts\Interactions\Request\Option;

class SlashCommand
{
    const START                             = 'start';
    const HELP                              = 'help';

    const ABOUT_TOKEN                       = 'about_token';

    const SUMMARY                           = 'summary';
    const WALLETS_LIST                      = 'wallets';
    const ADD_WALLET                        = 'add_wallet';
    const RENAME_WALLET                     = 'rename_wallet';          # private usage via callback
    const DELETE_WALLET                     = 'delete_wallet';

    const FOLLOWING                         = 'following';
    const FOLLOW                            = 'follow';
    const UNFOLLOW                          = 'unfollow';

    const DOMAINS                           = 'domains';
    const OFFERS                            = 'offers';

    const COINS                             = 'coins';

    const SETTINGS                          = 'settings';

    const REDIRECT_DM                       = 'redirect_dm';

    private static array $ALIAS = [
        'delete_wallets'                    => self::DELETE_WALLET,
        'remove_wallet'                     => self::DELETE_WALLET,
        'remove_wallets'                    => self::DELETE_WALLET,
        'unlink_wallet'                     => self::DELETE_WALLET,
        'unlink_wallets'                    => self::DELETE_WALLET,
    ];

    //------------------------------------------------------------------------------------------------------------------

    public /* readonly */ ?string $command = null;
    public /* readonly */ ?string $query = null;
    public /* readonly */ array $vars = [];

    public ?Option $focusedOption = null;

    //------------------------------------------------------------------------------------------------------------------

    public static function NewByUserAction(?array $userAction): self
    {
        $instance = new self();
        $instance->setCommand($userAction['action_id']);

        foreach ($userAction['data'] as $k => $v)
        {
            $instance->setVar($k, $v);
        }

        return $instance;
    }

    public function setCommand(?string $command): void
    {
        $command = ltrim(strtolower((string) $command),'/');

        if (isset(self::$ALIAS[$command]))
            $command = self::$ALIAS[$command];

        $this->command = $command;
    }

    public function setQuery(?string $query): void
    {
        $this->query = trim((string) $query);
    }

    public function setVar(string $key, ?string $value): void
    {
        $this->vars[$key] = $value;
    }
}
