<?php

namespace CryptoNoises\Localize;

class NotificationButton
{
    const CALLBACK_AUID_PREFIX              = 'callback_auid';

    const kTYPE_Link                        = 'link';
    const kTYPE_CallbackAction              = 'callback-action';

    const kSTYLE_Primary                    = 'primary';
    const kSTYLE_Secondary                  = 'secondary';
    const kSTYLE_Success                    = 'success';
    const kSTYLE_Danger                     = 'danger';
    const kSTYLE_Disabled                   = 'disabled';

    public string $type                     = '';
    public string $caption                  = '';
    public ?string $url                     = null;
    public ?string $callbackAuid            = null;

    public string $style                    = self::kSTYLE_Primary;

    public static function Link(string $caption, string $url): NotificationButton
    {
        $button = new self();
        $button->type       = self::kTYPE_Link;
        $button->caption    = $caption;
        $button->url        = $url;
        return $button;
    }

    public static function CallbackAction(string $caption, string $callbackAuid): NotificationButton
    {
        $button = new self();
        $button->type           = self::kTYPE_CallbackAction;
        $button->caption        = $caption;
        $button->callbackAuid   = $callbackAuid;
        return $button;
    }

    public function setStylePrimary(): self
    {
        $this->style = self::kSTYLE_Primary;
        return $this;
    }

    public function setStyleSecondary(): self
    {
        $this->style = self::kSTYLE_Secondary;
        return $this;
    }

    public function setStyleSuccess(): self
    {
        $this->style = self::kSTYLE_Success;
        return $this;
    }

    public function setStyleDanger(): self
    {
        $this->style = self::kSTYLE_Danger;
        return $this;
    }

    public function setStyleDisabled(): self
    {
        $this->style = self::kSTYLE_Disabled;
        return $this;
    }
}
