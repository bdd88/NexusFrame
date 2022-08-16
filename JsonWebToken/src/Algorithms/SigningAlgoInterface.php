<?php
namespace Bdd88\JsonWebToken\Algorithms;

/** Defines methods a signing algorithm class must have. */
interface SigningAlgoInterface
{
    /** Sign data using the hashing algorithm and key provided. */
    public function sign(string $hashAlgo, $data, $key): string;
    /** Verify data signature using the hashing algorithm and key provided. */
    public function verify(string $hashAlgo, string $data, string $key, string $signature): bool;
}