<?php

namespace CryptoNoisesTezos\Core;

class Helper
{
    public static function IsNat(?string $val): bool
    {
        if ($val === null)
            return false;

        $val_str = (string) $val;
        $val_int = (int)    $val;

        if ($val_str !== (string)$val_int)
            return false;

        return $val_int >= 0;
    }

    //------------------------------------------------------------------------------------------------------------------

    public static function UnpackHex2Bin(?string $value): ?string
    {
        if ($value === null || $value === '')
            return $value;

        if (strlen($value) > 4)
        {
            $prefix = substr($value, 0, 4);

            if($prefix == '0500')
            {
                // Samples:               hex      dec
                //  050000      => 0500 + 00    => 0
                //  050006      => 0500 + 06    => 6

                $value = substr($value, 4);
                $value = hexdec($value);
                return $value;
            }

            if ($prefix === '0501')
            {
                // Samples:                                    pref.  length     hex-string
                //                                             1234   12345678   length*2................
                //  05010000000c477265656e4d616368696e65    => 0501 + 0000000c + 477265656e4d616368696e65
                //  050100000006536563726574                => 0501 + 00000006 + 536563726574
                //  050100000003747a74                      => 0501 + 00000003 + 747a74

                $strlen = substr($value, 4, 8);
                $strlen = hexdec($strlen);

                $value  = substr($value, 12);

                if ($strlen*2 != strlen($value))
                    return null;

                return self::SafeUnpackHex2Bin($value);
            }

            if ($prefix === '0502')
            {
                // Michelson-packed structure (0502...) is not supported: return null
                // Sample:
                // 0502000002d607040100000008426972746864617907070509010000000464617465010000000b30342d4d61722d323032320704010000000d426f6e64696e67206c6576656c0707050901000000066e756d62657201000000013107040100000005427265656407070306010000000a546f7920506f6f646c650704010000000e4272656564696e6720636f756e740707050901000000066e756d6265720100000001300704010000000a4579657320636f6c6f72070703060100000007477265792023340704010000000c467269656e646c696e6573730707050901000000066e756d6265720100000001330704010000000946757220636f6c6f7207070306010000000857686974652023330704010000000647656e6465720707030601000000044d616c650704010000000a47656e65726174696f6e070703060100000005416c7068610704010000000547726f757007070306010000000c4e6f6e2d53706f7274696e670704010000000c496e74656c6c6967656e63650707050901000000066e756d626572010000000133070401000000094f62656469656e63650707050901000000066e756d626572010000000135070401000000135072696d61727920706572736f6e616c697479070703060100000009456d6f74696f6e616c0704010000000c5261726974792073636f72650707050901000000066e756d6265720100000007353334352e38310704010000000b526172697479207469657207070306010000000642726f6e7a65070401000000155365636f6e6461727920706572736f6e616c69747907070306010000000542726176650704010000000453697a6507070306010000000a4c61726765206d616c6507040100000006537461747573070703060100000005507570707907040100000008537472656e6774680707050901000000066e756d6265720100000002313207040100000008566974616c6974790707050901000000066e756d626572010000000138
                return null;
            }

            if (substr($value, 0, 2) == '05')
            {
                // other Michelson-packed values are not supported
                return null;
            }
        }

        return self::SafeUnpackHex2Bin($value);
    }

    private static function SafeUnpackHex2Bin(?string $value): ?string
    {
        if (self::IsHexString($value) == false)
            return null;

        return hex2bin($value);
    }

    private static function IsHexString(?string $value): bool
    {
        return $value && strlen($value)%2 == 0 && preg_match('/^([0-9a-f]+)$/iu', $value) != false;
    }
}
