<?php

namespace InstagramAPI;

class Signatures
{
    public static function generateSignature(
        $data)
    {
        $hash = hash_hmac('sha256', $data, Constants::IG_SIG_KEY);

        return 'ig_sig_key_version='.Constants::SIG_KEY_VERSION.'&signed_body='.$hash.'.'.urlencode($data);
    }

    public static function generateDeviceId()
    {
        // This has 10 million possible hash subdivisions per clock second.
        $megaRandomHash = md5(number_format(microtime(true), 7, '', ''));

        return 'android-'.substr($megaRandomHash, 16);
    }

    public static function generateUUID(
        $keepDashes = true)
    {
        $uuid = sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );

        return $keepDashes ? $uuid : str_replace('-', '', $uuid);
    }
}
