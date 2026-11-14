<?php

namespace CryptoNoises\Localize;

use CryptoNoises\Entities;

trait Notification_NftExtends
{
    public function appendNftTags(?Entities\Token $tokenEntity): bool
    {
        if ($tokenEntity === null || empty($tokenEntity->tags))
            return false;

        $this->description[] = self::EMPTY_LINE;

        // tags and attributes come from token metadata (anyone who mints a token): escape them for Telegram HTML
        $tags = array_map(fn ($tag): string => self::SafeText((string) $tag), $tokenEntity->tags);

        $maxCount = 32;
        $count    = count($tags);

        if ($count > $maxCount+1)
        {
            $tags = array_slice($tags, 0, $maxCount);
            $tags[] = "and ".($count-$maxCount)." more...";
        }

        $tags = join(', ', $tags);

        if (self::IsTelegram())
            $this->description[] = "<b>🔗 Tags:</b> $tags";
        else if (self::IsDiscord())
            $this->description[] = "**🔗 Tags:** $tags";

        return true;
    }

    public function appendNftAttributes(?Entities\Token $tokenEntity): bool
    {
        if ($tokenEntity === null || empty($tokenEntity->attributes))
            return false;

        $attributes = array_slice($tokenEntity->attributes, 0, 20, true);

        if (self::IsTelegram())
        {
            $this->description[] = self::EMPTY_LINE;
            $this->description[] = "<b>Attributes:</b>";

            foreach ($attributes as $title => $value)
            {
                if ($title == null || $value == null)
                    continue;

                $this->description[] = "- ".self::SafeText((string) $title).": ".self::SafeText((string) $value);
            }
        }
        else if (self::IsDiscord())
        {
            foreach ($attributes as $title => $value)
            {
                if ($title == null || $value == null)
                    continue;

                $this->appendFieldValue($title, $value);
            }

            $this->setFieldsAsInline(true);
        }
        return true;
    }
}
