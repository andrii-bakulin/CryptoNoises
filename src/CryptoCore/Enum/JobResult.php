<?php

namespace CryptoCore\Enum;

class JobResult
{
    const kCompleted                            = 0;
    const kFailedAndPostpone                    = 999000001;
    const kFailedAndRepeat                      = 999000002;
    const kFailedAndClose                       = 999000003;

    private int $code;

    private function __construct(int $code)
    {
        $this->code = $code;
    }

    public function code(): int
    {
        return $this->code;
    }

    public static function Completed(): self            { return new self(self::kCompleted); }
    public static function FailedAndPostpone(): self    { return new self(self::kFailedAndPostpone); }
    public static function FailedAndRepeat(): self      { return new self(self::kFailedAndRepeat); }
    public static function FailedAndClose(): self       { return new self(self::kFailedAndClose); }

    public static function FailedWithCode($code): self
    {
        switch ($code)
        {
            default:
            case self::kFailedAndPostpone:
                return self::FailedAndPostpone();

            case self::kFailedAndRepeat:
                return self::FailedAndRepeat();

            case self::kFailedAndClose:
                return self::FailedAndClose();
        }
    }
}
