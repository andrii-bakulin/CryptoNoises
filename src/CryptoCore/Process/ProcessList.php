<?php

namespace CryptoCore\Process;

/**
 * Lists running processes of the current host (excluding the current process).
 */
final class ProcessList
{
    /**
     * @return array<int, string> pid => full command line
     */
    public static function All(): array
    {
        $output = (string) shell_exec('ps axww -o pid=,args=');
        $selfPid = getmypid();

        $processes = [];

        foreach (explode("\n", $output) as $line)
        {
            $parts = preg_split('/\s+/', trim($line), 2);

            if (count($parts) < 2 || !ctype_digit($parts[0]))
                continue;

            $pid = (int) $parts[0];

            if ($pid !== $selfPid)
                $processes[$pid] = $parts[1];
        }

        return $processes;
    }

    /**
     * @return array<int, string> pid => full command line, for processes whose command line contains $needle
     */
    public static function FindContaining(string $needle): array
    {
        return array_filter(self::All(), function (string $args) use ($needle)
        {
            return strpos($args, $needle) !== false;
        });
    }

    /**
     * Counts PHP processes launched with exactly the same script + arguments: `php <command>`, `/usr/bin/php7.4 <command>`.
     * Wrappers with the same tail (cron's `/bin/sh -c <command>`, `timeout ... <command>`) are not counted.
     */
    public static function CountSameCommand(string $command): int
    {
        $count = 0;
        $suffix = ' '.$command;

        foreach (self::All() as $args)
        {
            if (substr($args, -strlen($suffix)) !== $suffix)
                continue;

            $interpreter = substr($args, 0, -strlen($suffix));

            if (preg_match('/^php[0-9.]*$/', basename($interpreter)))
                $count++;
        }

        return $count;
    }
}
