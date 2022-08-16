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
    private SigningAlgoInterface $signingAlgo;

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
    private string $encodedHeader;
    private array $payload;
    private string $encodedPayload;
    private string $dataToSign;
    private string $signature;
    private string $encodedSignature;
    private string $encodedToken;
    private string $signingAlgoString;
    private string $hashingAlgoString;
    private string|NULL $verificationKey;
    private string|NULL $signingKey;

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
     * Set the keys to be used for verification and signing of tokens.
     *
     * @param string|null $verificationKey PEM format Public key for RSA. Shared secret for HMAC.
     * @param string|null $signingKey PEM format Private key for RSA. Shared secret for HMAC.
     */
    public function setKeys(?string $verificationKey, ?string $signingKey): void
    {
        $this->verificationKey = $verificationKey;
        $this->signingKey = $signingKey;
    }

    /**
     * Generate a JSON Web Token.
     *
     * @param array $header Should include 'alg' and 'typ'.
     * @param array $payload Claims you want to use.
     */
    public function generate(array $header, array $payload): void
    {
        $this->header = $header;
        $this->payload = $payload;
        $this->encodedHeader = $this->base64Url->encode(json_encode($this->header));
        $this->encodedPayload = $this->base64Url->encode(json_encode($this->payload));
        $this->prepareForSigning();
        $this->signature = $this->signingAlgo->sign($this->hashingAlgoString, $this->dataToSign, $this->signingKey);
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
     * @return true|string
     */
    public function validate(): bool|string
    {
        // Check the header for issues.
        if ($this->header['typ'] !== 'JWT') {
            return 'Type is not JWT.';
        }
        if (!isset($this->header['alg'])) {
            return 'No Algorithm specified.';
        }
        if (!isset(SELF::SUPPORTED_ALGOS[$this->header['alg']])) {
            return 'Unsupported Algorithm. Must use one of the following: ' . implode(', ', array_keys(SELF::SUPPORTED_ALGOS));
        }

        // TODO: Check the payload claims for issues.

        // Verify the signature for tampering.
        $this->prepareForSigning();
        $verifyStatus = $this->signingAlgo->verify($this->hashingAlgoString, $this->dataToSign, $this->verificationKey, $this->signature);
        if ($verifyStatus === FALSE) {
            return 'Signature is bad.';
        }

        return TRUE;
    }
}

?>
