<?php
namespace Bdd88\JsonWebToken;

use Bdd88\JsonWebToken\Encoding\Base64Url;
use Bdd88\JsonWebToken\Algorithms\Hmac;
use Bdd88\JsonWebToken\Algorithms\Rsa;

class JwtFactory
{
    private Base64Url $base64Url;
    private Rsa $rsa;
    private Hmac $hmac;
    private string|NULL $verificationKey;
    private string|NULL $signingKey;

    public function __construct(Base64Url $base64Url, Rsa $rsa, Hmac $hmac)
    {
        $this->base64Url = $base64Url;
        $this->rsa = $rsa;
        $this->hmac = $hmac;
    }
    
    public function setKeys(?string $verificationKey, ?string $signingKey): void
    {
        $this->verificationKey = $verificationKey;
        $this->signingKey = $signingKey;
    }

    public function generate(array $header, array $payload): Jwt
    {
        $jwt = new Jwt($this->base64Url, $this->rsa, $this->hmac);
        $jwt->setKeys($this->verificationKey, $this->signingKey);
        $jwt->generate($header, $payload);
        return $jwt;
    }

    public function import(string $tokenString): Jwt
    {
        $jwt = new Jwt($this->base64Url, $this->rsa, $this->hmac);
        $jwt->setKeys($this->verificationKey, $this->signingKey);
        $jwt->import($tokenString);
        return $jwt;
    }

}

?>
