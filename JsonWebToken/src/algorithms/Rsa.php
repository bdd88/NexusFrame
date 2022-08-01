<?php
namespace Bdd88\JsonWebToken\Algorithms;

/** Handles actions related to the RSA algorithm. */
class Rsa implements SigningAlgoInterface
{
    public function sign(string $hashAlgo, $data, $key): string
    {
        $hashAlgo = constant('OPENSSL_ALGO_' . $hashAlgo);
        openssl_sign($data, $signature, $key, $hashAlgo);
        return $signature;
    }

    public function verify(string $hashAlgo, string $data, string $key, string $signature): bool
    {
        $hashAlgo = constant('OPENSSL_ALGO_' . $hashAlgo);
        $verificationStatus = openssl_verify($data, $signature, $key, $hashAlgo);
        if ($verificationStatus === 1) {
            return TRUE;
        }
        return FALSE;
    }

    public function generateKeys(): array
    {
        $keyOptions = array(
            "digest_alg" => 'sha512',
            "private_key_bits" => 4096,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        );
        $keys = openssl_pkey_new($keyOptions);
        openssl_pkey_export($keys, $privateKey);
        $publicKey = openssl_pkey_get_details($keys)['key'];
        return array('private' => $privateKey, 'public' => $publicKey);
        //$publicKey = openssl_pkey_get_public($publicKeyPem);
    }

}