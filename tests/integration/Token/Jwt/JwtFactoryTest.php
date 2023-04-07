<?php

use PHPUnit\Framework\TestCase;
use NexusFrame\Token\Jwt\JwtFactory;
use NexusFrame\Token\Jwt\Algorithms\Rsa;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DataProvider;

class JwtFactoryTest extends TestCase
{
    private JwtFactory $jwtFactory;
    private string $secret;
    private array $keys;

    public function setUp(): void
    {
        $this->jwtFactory = new JwtFactory();
        $rsa = new Rsa();
        $this->keys = $rsa->generateKeys();
        $this->secret = random_string();
    }

    public static function provideAlgo(): array
    {
        return [
            'HS256' => ['HS256'],
            'HS384' => ['HS384'],
            'HS512' => ['HS512'],
            'RS256' => ['RS256'],
            'RS384' => ['RS384'],
            'RS512' => ['RS512']
        ];
    }

    #[Test]
    #[DataProvider('provideAlgo')]
    public function generateImportValidate(string $algo): void
    {
        // Generate the token
        if (str_starts_with($algo, 'HS')) {
            $this->jwtFactory->setKeys($this->secret, $this->secret);
        } elseif (str_starts_with($algo, 'RS')) {
            $this->jwtFactory->setKeys($this->keys['public'], $this->keys['private']);
        }
        $header = array('alg' => $algo, 'typ' => 'JWT');
        $payload = array('username' => 'guy', 'enabled' => TRUE);
        $jwt = $this->jwtFactory->generate($header, $payload);

        // Verify token was generated properly
        $tokenString = (string) $jwt;
        $tokenSections = explode('.', $tokenString);
        $this->assertNotEmpty($tokenString, 'JWT string is empty.');
        $this->assertCount(3, $tokenSections, 'JWT missing sections or malformed.');
        foreach ($tokenSections as $section) {
            $this->assertMatchesRegularExpression('/^[A-Za-z0-9_-]+$/', $section, 'Section is not valid Base64URL.');
        }
        
        // Import the good token and validate
        $importedJwt = $this->jwtFactory->import($tokenString);
        $validation = $importedJwt->validate();
        $this->assertTrue($validation, $validation);

        // Import the token with a modified signature
        $importedJwt = $this->jwtFactory->import($tokenString . 'bad');
        $validation = $importedJwt->validate();
        $this->assertNotTrue($validation, $validation);
    }

    #[Test]
    #[Depends('generateImportValidate')]
    public function badTokens(): void
    {
        $this->jwtFactory->setKeys('badSecret', 'badSecret');
        $badTokens = array(
            'Wrong type' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IlNvbWV0aGluZyJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.ihzbqjaRPia8pzgc6SO2NfyfpoWsnGvsdMHN89lt8y0',
            'Unsupported algo' => 'eyJhbGciOiJFUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiYWRtaW4iOnRydWUsImlhdCI6MTUxNjIzOTAyMn0.tyh-VfuzIxCyGYDlkBA7DfyjrqmSHu6pQ2hoZuFqUSLPNY2N0mpHb3nk5K17HWP_3cYHBw7AhHale5wky6-sVA',
        );
        foreach ($badTokens as $badToken) {
            $importedJwt = $this->jwtFactory->import($badToken);
            $validation = $importedJwt->validate();
            $this->assertNotTrue($validation, $validation);
        }
    }
}