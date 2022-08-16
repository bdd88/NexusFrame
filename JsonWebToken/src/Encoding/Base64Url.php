<?php
namespace Bdd88\JsonWebToken\Encoding;

/** Adds support for Base64URL standard. */
class Base64Url
{
    /** Encode a string into Base64URL. */
    public function encode(string $string): string
    {
        $base64 = base64_encode($string);
        $base64Url = str_replace(['+','/','='], ['-','_',''], $base64);
        return $base64Url;
    }

    /** Decode Base64URL into a string. */
    public function decode(string $base64Url, ?bool $strict = FALSE): string
    {
        $base64 = str_replace(['-','_'], ['+','/'], $base64Url);
        $string = base64_decode($base64, $strict);
        return $string;
    }
}

?>
