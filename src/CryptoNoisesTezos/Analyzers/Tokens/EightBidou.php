<?php

namespace CryptoNoisesTezos\Analyzers\Tokens;

use CryptoNoisesTezos\Core\Bigmap;
use CryptoNoisesTezos\Core\Helper;
use CryptoNoisesTezos\Entities;
use CryptoNoisesTezos\Entities\IpfsMedia;
use CryptoNoisesTezos\Jobs\AnalyzeNewlyDetectedToken;
use CryptoNoisesTezos\Vendors\Ipfs;
use Exception;

class EightBidou
{
    const kCRYPTONOISES_MARKER_METADATA         = 'cryptonoises://token-metadata/8bidou';
    const kCRYPTONOISES_MARKER_PREVIEW          = 'cryptonoises://token-preview/8bidou';

    // Token Contracts
    //      RGB                                 = KT1MxDwChiDwd6WBVs24g1NjERUoK622ZEFp
    //      24MONO                              = KT1TR1ErEQPTdtaJ7hbvKTJSa1tsGnHGZTpf
    //      24RGB                               = KT1VikAWA8wQHLZgHoAGL7Z9kCjgbCEnvWA3
    //
    // Marketplaces
    //      RGB                                 = KT1BvWGFENd4CXW5F3u4n31xKfJhmBGipoqF
    //      24MONO                              = KT1AHBvSo828QwscsjDjeUuep7MgApi8hXqA
    //      24RGB                               = KT1QtnHR8p2hBjUhPRy9BCWgy7s7L578PA7N

    const kBIGMAP_TOKEN_METADATA_8RGB           = 113216;
    const kBIGMAP_TOKEN_METADATA_24MONO         = 128184;
    const kBIGMAP_TOKEN_METADATA_24RGB          = 124995;

    const kBIGMAP_TOKEN_OWN_DATA_8RGB           = 113218;
    const kBIGMAP_TOKEN_OWN_DATA_24MONO         = 128186;
    const kBIGMAP_TOKEN_OWN_DATA_24RGB          = 124997;

    public static function AnalyzeNewlyDetectedToken(AnalyzeNewlyDetectedToken $job, array $value): bool
    {
        if (!in_array($job->bigmap, [
            self::kBIGMAP_TOKEN_METADATA_8RGB,
            self::kBIGMAP_TOKEN_METADATA_24MONO,
            self::kBIGMAP_TOKEN_METADATA_24RGB,]))
            return false;

        if (isset($value['token_info']['']))
        {
            $metadataUri = $job->token->unpackHex2Bin($value['token_info']['']);

            if (Ipfs::IsExactlyIpfsUri($metadataUri))
                return false; // real IPFS metadata: use the standard flow
        }

        // else
        //  here $metadataUri may have any garbage value, like:
        //      http://localhost:9999/
        //      hello
        //      ...
        //  or don't have it at all...

        $metadataUri = self::kCRYPTONOISES_MARKER_METADATA;

        $job->token->open($metadataUri, $job->bigmap, $job->bigmapExkey, $job->level);

        $job->lastActionNotice = "{$job->token->contractAdid}:{$job->token->tokenUnid} = $metadataUri";
        return true;
    }

    //------------------------------------------------------------------------------------------------------------------

    /**
     * @param Entities\Token $token
     * @return array
     * @throws Exception
     */
    private static function GetDataFromBigmap(Entities\Token $token): array
    {
        switch ($token->bigmap)
        {
            case self::kBIGMAP_TOKEN_METADATA_8RGB:     $bigmap =  self::kBIGMAP_TOKEN_OWN_DATA_8RGB;   break;
            case self::kBIGMAP_TOKEN_METADATA_24MONO:   $bigmap =  self::kBIGMAP_TOKEN_OWN_DATA_24MONO; break;
            case self::kBIGMAP_TOKEN_METADATA_24RGB:    $bigmap =  self::kBIGMAP_TOKEN_OWN_DATA_24RGB;  break;

            default:
                throw new Exception('Unknown token->bigmap value');
        }

        $value = Bigmap::GetValueByBigmapAndExkey($bigmap, $token->bigmapExkey);

        if (!is_array($value))
            throw new Exception('Cannot find bigmap record [or] data is not array');

        return $value;
    }

    //------------------------------------------------------------------------------------------------------------------

    /**
     * Converts the on-chain structure to TZIP-21-like JSON for the core parser
     *
     * @param Entities\Token $token
     * @return array|null
     * @throws Exception
     */
    public static function GetJsonMetadata(Entities\Token $token): ?array
    {
        $jsonIn = self::GetDataFromBigmap($token);

        $jsonOut = [];
        $jsonOut['name']            = $token->unpackHex2Bin($jsonIn['token_name']);
        $jsonOut['description']     = $token->unpackHex2Bin($jsonIn['token_description']);
        $jsonOut['decimals']        = 0;

        $royaltyAmount_d4 = 0;

        switch ($token->bigmap)
        {
            case self::kBIGMAP_TOKEN_METADATA_8RGB:
                $jsonOut['creator'] = $jsonIn['creater']; // 'creater' is the field name in the 8bidou contract
                $royaltyAmount_d4   = 1000;
                break;

            case self::kBIGMAP_TOKEN_METADATA_24MONO:
            case self::kBIGMAP_TOKEN_METADATA_24RGB:
                $jsonOut['creator'] = $jsonIn['creator'];
                $royaltyAmount_d4   = 1666;
                break;
        }

        if ($jsonOut['creator'] && $royaltyAmount_d4 > 0)
        {
            $jsonOut['royalties'] = [
                'decimals' => 4,
                'shares' => [
                    $jsonOut['creator'] => $royaltyAmount_d4,
                ],
            ];
        }

        $jsonOut['displayUri'] = self::kCRYPTONOISES_MARKER_PREVIEW.':'.sha1($jsonIn['rgb']);
        return $jsonOut;
    }

    //------------------------------------------------------------------------------------------------------------------

    /**
     * @param Entities\Token $token
     * @param string $previewUri
     * @return IpfsMedia
     * @throws Exception
     */
    public static function CreatePreview(Entities\Token $token, string $previewUri): IpfsMedia
    {
        $json = self::GetDataFromBigmap($token);

        if (!isset($json['rgb']))
            throw new Exception('Cannot find RGB sequence in JSON');

        $ipfsMedia = IpfsMedia::Dummy($previewUri);

        switch ($token->bigmap)
        {
            case self::kBIGMAP_TOKEN_METADATA_8RGB:     return self::CreatePreview_8rgb($ipfsMedia,   $json['rgb']);
            case self::kBIGMAP_TOKEN_METADATA_24MONO:   return self::CreatePreview_24mono($ipfsMedia, $json['rgb']);
            case self::kBIGMAP_TOKEN_METADATA_24RGB:    return self::CreatePreview_24rgb($ipfsMedia,  $json['rgb']);
        }

        throw new Exception('Unknown token->bigmap value');
    }

    /**
     * @throws Exception
     */
    private static function CreatePreview_8rgb(IpfsMedia $ipfsMedia, string $rgbSequence): IpfsMedia
    {
        return self::CreatePixelPreview($ipfsMedia, $rgbSequence, 8, 3, false);
    }

    /**
     * @throws Exception
     */
    private static function CreatePreview_24mono(IpfsMedia $ipfsMedia, string $rgbSequence): IpfsMedia
    {
        return self::CreatePixelPreview($ipfsMedia, $rgbSequence, 24, 1, true);
    }

    /**
     * @throws Exception
     */
    private static function CreatePreview_24rgb(IpfsMedia $ipfsMedia, string $rgbSequence): IpfsMedia
    {
        return self::CreatePixelPreview($ipfsMedia, $rgbSequence, 24, 3, true);
    }

    /**
     * Draws a 960x960 preview from a hex pixel sequence (2 hex chars per channel, row by row).
     *
     * @param int  $pixelCount      pixels per side (8 or 24)
     * @param int  $channels        3 = RGB, 1 = gray
     * @param bool $mayBeDoubleHex  early 24x24 tokens stored the sequence hex-encoded twice
     * @throws Exception
     */
    private static function CreatePixelPreview(IpfsMedia $ipfsMedia, string $sequence, int $pixelCount, int $channels, bool $mayBeDoubleHex): IpfsMedia
    {
        $pixelSize  = intdiv(960, $pixelCount);
        $imageSize  = $pixelCount * $pixelSize;
        $len        = $pixelCount * $pixelCount * $channels * 2;

        if ($mayBeDoubleHex && strlen($sequence) == $len*2)
            $sequence = (string) Helper::UnpackHex2Bin($sequence);

        if (strlen($sequence) != $len)
            throw new Exception("Bad RGB string length (".strlen($sequence).")");

        $img = imagecreatetruecolor($imageSize, $imageSize);

        for ($index = 0, $offset = 0; $offset < $len; $index++, $offset += $channels*2)
        {
            $r = hexdec(substr($sequence, $offset, 2));
            $g = $channels == 3 ? hexdec(substr($sequence, $offset+2, 2)) : $r;
            $b = $channels == 3 ? hexdec(substr($sequence, $offset+4, 2)) : $r;

            $x0 = ($index % $pixelCount) * $pixelSize;
            $y0 = intdiv($index, $pixelCount) * $pixelSize;

            $color = imagecolorallocate($img, $r, $g, $b);
            imagefilledrectangle($img, $x0, $y0, $x0 + $pixelSize-1, $y0 + $pixelSize-1, $color);
        }

        return self::CreatePreview_Finalize($ipfsMedia, $img);
    }

    /**
     * @param IpfsMedia $ipfsMedia
     * @param resource $img
     * @return IpfsMedia
     * @throws Exception
     */
    private static function CreatePreview_Finalize(IpfsMedia $ipfsMedia, $img): IpfsMedia
    {
        $savePreviewFull = $ipfsMedia->getSaveFullPath();

        if (imagejpeg($img, "$savePreviewFull.jpg", 80) == false)
            throw new Exception('Cannot save preview jpeg-file');

        $ipfsMedia->mediaType    = IpfsMedia::kTYPE_Image;
        $ipfsMedia->width        = imagesx($img);
        $ipfsMedia->height       = imagesy($img);
        $ipfsMedia->filesize     = filesize("$savePreviewFull.jpg");
        $ipfsMedia->mimetype     = 'image/jpeg';
        $ipfsMedia->hasThumbnail = true;
        $ipfsMedia->hasAnimation = false;
        $ipfsMedia->hasMovieClip = false;
        $ipfsMedia->save();

        $ipfsMedia->lastResult   = true;
        return $ipfsMedia;
    }
}
