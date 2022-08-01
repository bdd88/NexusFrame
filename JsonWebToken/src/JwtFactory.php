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

    public function __construct(Base64Url $base64Url, Rsa $rsa, Hmac $hmac)
    {
        $this->base64Url = $base64Url;
        $this->rsa = $rsa;
        $this->hmac = $hmac;
    }

    public function create(): Jwt
    {
        return new Jwt($this->base64Url, $this->rsa, $this->hmac);
    }

}

?>
