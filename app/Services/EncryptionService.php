<?php

namespace App\Services;

use phpseclib\Crypt\Rijndael;
use Illuminate\Support\Facades\Crypt;

class EncryptionService
{
    /**
     * Decrypt a string.
     *
     * @param   string  $string
     * @return  string
     */
    public static function decrypt($string = "")
    {
        $data = base64_decode($string);

        $key = Crypt::decrypt(config('axxess.cipher.key'));
        $iv = Crypt::decrypt(config('axxess.cipher.key'));

        return self::strippadding(
            self::cipherDecrypt($data, $key, $iv)
        );
    }

    /**
     * Cipher decrypt.
     *
     * @param   string  $data
     * @param   string  $key
     * @param   string  $iv
     * @return  string
     */
    private static function cipherDecrypt($data, $key, $iv)
    {
        $cipher = new Rijndael(Rijndael::MODE_ECB);
        $cipher->setBlockLength(128);
        $cipher->disablePadding();
        $cipher->setKey($key);
        $cipher->setIV($iv);

        return $cipher->decrypt($data);
    }

    /**
     * Strip padding.
     *
     * @param   string  $string
     * @return  string
     */
    private static function strippadding($string)
    {
        $slast  = ord(substr($string, -1));
        $slastc = chr($slast);

        if (preg_match("/$slastc{" . $slast . "}/", $string)) {
            return substr($string, 0, strlen($string) - $slast);
        }

        return false;
    }

    //
    public static function encrypt($string = "")
    {
        $key = Crypt::decrypt(config('axxess.cipher.key'));
        $iv = Crypt::decrypt(config('axxess.cipher.key'));

        $data = self::cipherEncrypt(
            trim(self::addpadding($string)),
            $key,
            $iv
        );

        return base64_encode($data);
    }

    /**
     * Cipher decrypt.
     *
     * @param   string  $data
     * @param   string  $key
     * @param   string  $iv
     * @return  string
     */
    private static function cipherEncrypt($data, $key, $iv)
    {
        $cipher = new Rijndael(Rijndael::MODE_ECB);
        $cipher->setBlockLength(128);
        $cipher->disablePadding();
        $cipher->setKey($key);
        $cipher->setIV($iv);

        return $cipher->encrypt($data);
    }

    //
    private static function addpadding($string, $blocksize = 16)
    {
        $len = strlen($string);

        $pad = $blocksize - ($len % $blocksize);

        $string .= str_repeat(chr($pad), $pad);

        return $string;
    }
}
