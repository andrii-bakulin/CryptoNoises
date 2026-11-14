<?php

namespace CryptoNoisesTezos\Core;

use CryptoNoisesTezos\Config;

/**
 * Builds and runs ImageMagick / ffmpeg commands for untrusted media with resource limits.
 *
 * If a tool fails or is killed by the timeout, its output file is deleted: a half-written file must not become a preview
 * (callers check the result with "file exists and is not empty").
 */
final class MediaTool
{
    // Raster formats ImageMagick may read; the input coder is forced from the detected mimetype, so a file can never
    // be read as SVG / MVG / MSL / TEXT or any other coder that can reference other files
    private const kCODER_BY_MIMETYPE = [
        'image/png'      => 'png',
        'image/jpeg'     => 'jpeg',
        'image/jpg'      => 'jpeg',
        'image/gif'      => 'gif',
        'image/bmp'      => 'bmp',
        'image/x-ms-bmp' => 'bmp',
        'image/tiff'     => 'tiff',
        'image/webp'     => 'webp',
    ];

    public static function CoderByMimetype(?string $mimetype): ?string
    {
        return self::kCODER_BY_MIMETYPE[strtolower(trim((string) $mimetype))] ?? null;
    }

    /**
     * @param string $coder ImageMagick input coder (see CoderByMimetype())
     */
    public static function Convert(string $coder, string $inFile, string $outFile, string $options): void
    {
        $limits = '-limit memory 256MiB -limit map 512MiB -limit disk 1GiB -limit time '.Config::cfg()->media->toolTimeoutSec;

        self::Run(Config::cfg()->cmd_imageMagickConvert." $limits ".escapeshellarg("$coder:$inFile")." $options ".escapeshellarg($outFile), $outFile);
    }

    public static function Ffmpeg(string $inFile, string $outFile, string $options): void
    {
        // Local files only (no http/concat/playlist tricks) and limited input duration
        $input = '-protocol_whitelist file -t '.Config::cfg()->media->maxDurationSec.' -i '.escapeshellarg($inFile);

        self::Run(Config::cfg()->cmd_ffmpeg." -y -loglevel error $input $options ".escapeshellarg($outFile), $outFile);
    }

    private static function Run(string $cmd, string $outFile): void
    {
        $timeout = Config::cfg()->cmd_timeout;

        if ($timeout !== '')
            $cmd = $timeout.' '.Config::cfg()->media->toolTimeoutSec.' '.$cmd;

        $output = [];
        $exitCode = 0;
        exec($cmd.' 2>/dev/null', $output, $exitCode);

        // Non-zero exit (error, or 124 = killed by timeout): drop a partial result
        if ($exitCode !== 0 && is_file($outFile))
            unlink($outFile);
    }
}
