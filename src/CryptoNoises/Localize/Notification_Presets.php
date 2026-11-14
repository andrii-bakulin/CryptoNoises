<?php

namespace CryptoNoises\Localize;

trait Notification_Presets
{
    protected function presetSenderProfile(): string
    {
        if (self::IsTelegram())
        {
            return '<a href="{###SENDER_PROFILE_URL###}">{###SENDER_ADDRESS###}</a>';
        }
        else if(self::IsDiscord())
        {
            return '{###SENDER_ADDRESS###}';
        }
        return '';
    }

    protected function presetTargetProfile(): string
    {
        if (self::IsTelegram())
        {
            return '<a href="{###TARGET_PROFILE_URL###}">{###TARGET_ADDRESS###}</a>';
        }
        else if(self::IsDiscord())
        {
            return '{###TARGET_ADDRESS###}';
        }
        return '';
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    protected function presetNftName(): string
    {
        if (self::IsTelegram())
        {
            return '<a href="{###TOKEN_STORE_URL###}">{###TOKEN_NAME_ESCAPED###}</a>';
        }
        else if(self::IsDiscord())
        {
            return '{###TOKEN_NAME###}';
        }
        return '';
    }

    protected function presetTokenCreatorProfile(): string
    {
        if (self::IsTelegram())
        {
            return '<a href="{###TOKEN_CREATOR_PROFILE_URL###}">{###TOKEN_CREATOR_ADDRESS###}</a>';
        }
        else if(self::IsDiscord())
        {
            return '{###TOKEN_CREATOR_ADDRESS###}';
        }
        return '';
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    protected function presetTokenContractInfo(): string
    {
        if (self::IsTelegram())
        {
            return '<a href="{###TOKEN_CONTRACT_URL###}">{###TOKEN_CONTRACT_NAME###}</a>';
        }
        else if(self::IsDiscord())
        {
            return '{###TOKEN_CONTRACT_NAME###}';
        }
        return '';
    }

    protected function presetCollectionInfo(): string
    {
        if (self::IsTelegram())
        {
            return '<a href="{###TOKENS_COLLECTION_URL###}">{###TOKENS_COLLECTION_NAME###}</a>';
        }
        else if(self::IsDiscord())
        {
            return '{###TOKENS_COLLECTION_NAME###}';
        }
        return '';
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    protected function presetMarketplace(): string
    {
        if (self::IsTelegram())
        {
            return '<b>{###MARKETPLACE_NAME###}</b>';
        }
        else if(self::IsDiscord())
        {
            return '**{###MARKETPLACE_NAME###}**';
        }
        return '';
    }
}
