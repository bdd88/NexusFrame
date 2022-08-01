<?php
namespace Bdd88\JsonWebToken;

use Bdd88\JsonWebToken\Encoding\Base64Url;
use Bdd88\JsonWebToken\Algorithms\SigningAlgoInterface;
use Bdd88\JsonWebToken\Algorithms\Hmac;
use Bdd88\JsonWebToken\Algorithms\Rsa;
use Exception;

/** Handles creation and validation of JSON Web Tokens. */
class Jwt
{
    private Base64Url $base64Url;
    private Rsa $rsa;
    private Hmac $hmac;

    private const REGISTERED_CLAIMS = array(
        'iss' => 'Issuer',
        'sub' => 'Subject',
        'aud' => 'Audience',
        'exp' => 'Expiration Time',
        'nbf' => 'Not Before',
        'iat' => 'Issued At',
        'jti' => 'JWT ID'
    );
    private const SUPPORTED_ALGOS = array(
        'HS256' => array('sign' => 'HMAC', 'hash' => 'SHA256'),
        'HS384' => array('sign' => 'HMAC', 'hash' => 'SHA384'),
        'HS512' => array('sign' => 'HMAC', 'hash' => 'SHA512'),
        'RS256' => array('sign' => 'RSA', 'hash' => 'SHA256'),
        'RS384' => array('sign' => 'RSA', 'hash' => 'SHA384'),
        'RS512' => array('sign' => 'RSA', 'hash' => 'SHA512')
    );

    private array $header;
    private array $payload;
    private string $encodedHeader;
    private string $encodedPayload;
    private string $dataToSign;
    private string $signature;
    private string $encodedSignature;
    private string $encodedToken;
    private string $signingAlgoString;
    private string $hashingAlgoString;
    private SigningAlgoInterface $signingAlgo;

    public function __construct(Base64Url $base64Url, Rsa $rsa, Hmac $hmac)
    {
        $this->base64Url = $base64Url;
        $this->rsa = $rsa;
        $this->hmac = $hmac;
    }

    public function __toString(): string
    {
        return $this->encodedToken;
    }

    /** Determine the necessary algorithms and data to sign. */
    private function prepareForSigning(): void
    {
        $this->dataToSign = $this->encodedHeader . '.' . $this->encodedPayload;
        $this->signingAlgoString = SELF::SUPPORTED_ALGOS[$this->header['alg']]['sign'];
        $this->hashingAlgoString = SELF::SUPPORTED_ALGOS[$this->header['alg']]['hash'];
        $this->signingAlgo = $this->{strtolower($this->signingAlgoString)};
    }

    /**
     * Generate a JSON Web Token.
     * Currently only supports SHA256 hashing algo.
     */
    public function generate(array $header, array $payload, string $key): void
    {

        $this->header = $header;
        $this->payload = $payload;
        $this->encodedHeader = $this->base64Url->encode(json_encode($this->header));
        $this->encodedPayload = $this->base64Url->encode(json_encode($this->payload));
        $this->prepareForSigning();
        $this->signature = $this->signingAlgo->sign($this->hashingAlgoString, $this->dataToSign, $key);
        $this->encodedSignature = $this->base64Url->encode($this->signature);
        $this->encodedToken = $this->encodedHeader . '.' . $this->encodedPayload . '.' . $this->encodedSignature;
    }

    /** Decode and store information from a JWT string. */
    public function import(string $token): void
    {
        $this->encodedToken = $token;
        list($this->encodedHeader, $this->encodedPayload, $this->encodedSignature) = explode('.', $this->encodedToken);
        $this->header = json_decode($this->base64Url->decode($this->encodedHeader), TRUE);
        $this->payload = json_decode($this->base64Url->decode($this->encodedPayload), TRUE);
        $this->signature = $this->base64Url->decode($this->encodedSignature);
    }

    /**
     * Determine if the header and payload claims are good, and verify the signature for tampering.
     */
    public function validate(string $key): bool
    {
        // Check the header for issues.
        if ($this->header['typ'] !== 'JWT') {
            throw new Exception('Type is not JWT.');
        }
        if (!isset($this->header['alg'])) {
            throw new Exception('No Algorithm specified.');
        }
        if (!isset(SELF::SUPPORTED_ALGOS[$this->header['alg']])) {
            throw new Exception('Unsupported Algorithm. Must use one of the following: ' . implode(', ', array_keys(SELF::SUPPORTED_ALGOS)));
        }

        // Check the payload claims for issues.

        // Verify the signature for tampering.
        $this->prepareForSigning();
        $verifyStatus = $this->signingAlgo->verify($this->hashingAlgoString, $this->dataToSign, $key, $this->signature);
        if ($verifyStatus === FALSE) {
            throw new Exception('Signature is bad.');
        }

        return $verifyStatus;
    }
}

?>
