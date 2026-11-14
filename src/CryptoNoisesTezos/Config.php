<?php

namespace CryptoNoisesTezos;

use CryptoCore\Env\Environment;
use CryptoCore\Env\EnvironmentPredicates;
use CryptoNoisesTezos\Core\MediaSettings;

class Config
{
    use EnvironmentPredicates;

    const kSYNC_BLOCK_BEHIND                            = 1;

    const kJOBS_TIMEOUT_DEFAULT                         = 55;

    const kJOBS_TIMEOUT_TRANSACTION_SYNC                = 3*60;
    const kJOBS_TIMEOUT_TRANSACTION_ANALYZE             = 5*60;

    const kJOBS_TIMEOUT_BIGMAP_UPDATE_SYNC              = 3*60;
    const kJOBS_TIMEOUT_BIGMAP_UPDATE_ANALYZE           = 5*60;

    //------------------------------------------------------------------------------------------------------------------
    // Log tables cleanup (blockchain/*/cleanup): a table is dropped only when it is this many levels behind the slowest
    // reader. Recent log tables hold 10000 levels each (older ranges are bigger), so the minimum keeps at least the
    // current and the previous table.
    // Note: the CryptoNoises analyzer also reads transaction tables (via GatewayTezos) and must not lag more than this.

    const kLOG_CLEANUP_DEFAULT_OFFSET                   = 50000;
    const kLOG_CLEANUP_MIN_OFFSET                       = 20000;

    //------------------------------------------------------------------------------------------------------------------
    // Download limits for untrusted URLs (token metadata / media), see Core\Downloader

    const kDOWNLOAD_MAX_METADATA_BYTES                  = 5 * 1024 * 1024;
    const kDOWNLOAD_MAX_MEDIA_BYTES                     = 100 * 1024 * 1024;

    // ImageMagick / ffmpeg limits and preview settings: environment.php tezos.media (Core\MediaSettings)

    //------------------------------------------------------------------------------------------------------------------
    // Constants. Do not change these values.

    const kENVIRONMENT_Production                       = Environment::kPRODUCTION;
    const kENVIRONMENT_Development                      = Environment::kDEVELOPMENT;
    const kENVIRONMENT_Development_Local                = Environment::kDEVELOPMENT_LOCAL;

    //------------------------------------------------------------------------------------------------------------------

    const kTOKEN_EVENTS_FixMintAndUpdate_forProduction      = true;
    const kTOKEN_EVENTS_FixMintAndUpdate_forDevelopment    = false;

    //------------------------------------------------------------------------------------------------------------------

    private ?string $environment = null;                // see Environment::kALLOWED

    //------------------------------------------------------------------------------------------------------------------

    public string $ipfsLocalBaseUrl;
    public string $ipfsLocalBasePath;

    public string $privateStorageBasePath;

    public string $cmd_imageMagickConvert;
    public string $cmd_ffmpeg;
    public string $cmd_timeout;

    public MediaSettings $media;

    //------------------------------------------------------------------------------------------------------------------

    private static ?self $Instance = null;

    public static function cfg(): self
    {
        if (self::$Instance === null)
        {
            self::$Instance = new self();
            self::$Instance->initialize();   // initialized on first use (not as a side effect of loading the class)
        }

        return self::$Instance;
    }

    public function __construct()
    {
        $this->environment = Environment::name();
    }

    public function initialize(): void
    {
        $this->ipfsLocalBaseUrl                     = (string) Environment::get('tezos.ipfs_local_base_url');
        $this->ipfsLocalBasePath                    = (string) Environment::get('tezos.ipfs_local_base_path');

        $this->privateStorageBasePath               = (string) Environment::get('tezos.private_storage_base_path');

        $this->cmd_imageMagickConvert               = (string) Environment::get('tezos.cmd_imagemagick_convert', 'convert');
        $this->cmd_ffmpeg                           = (string) Environment::get('tezos.cmd_ffmpeg', 'ffmpeg');
        $this->cmd_timeout                          = (string) Environment::get('tezos.cmd_timeout', '');

        $this->media                                = MediaSettings::FromEnvironment();
    }

    //------------------------------------------------------------------------------------------------------------------

    public function isTokenSupportsAnimatedPreview(): bool
    {
        return $this->media->animatedPreview;     // off by default
    }

    public function isTokenSupportsVideoPreview(): bool
    {
        return $this->media->videoPreview;        // off by default
    }
}
