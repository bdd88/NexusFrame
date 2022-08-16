<?php
namespace Bdd88\JsonWebToken\Algorithms;

/** Handles actions related to the HMAC algorithm. */
class Hmac implements SigningAlgoInterface
{
        /** Sign data using HMAC and SHA. */
        public function sign(string $hashAlgo, $data, $key): string
        {
            $signature = hash_hmac($hashAlgo, $data, $key, TRUE);
            return $signature;
        }
    
        /** Verify an HMAC signature by comparing it to a newly generated signature. */
        public function verify(string $hashAlgo, string $data, string $key, string $signature): bool
        {
            $verificationSignature = $this->sign($hashAlgo, $data, $key);
            if ($signature !== $verificationSignature) {
                return FALSE;
            }
            return TRUE;
        }
}