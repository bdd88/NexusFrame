<?php

use NexusFrame\Token\Jwt\Algorithms\Hmac;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;

class HmacTest extends TestCase
{
    private Hmac $hmac;
    private string $secret;
    private string $payload;

    public function setUp(): void
    {
        $this->hmac = new Hmac();
        $this->secret = random_string();
        $this->payload = random_string();
    }

    public static function provideAlgo(): array
    {
        return [
            'SHA256' => ['SHA256'],
            'SHA384' => ['SHA384'],
            'SHA512' => ['SHA512']
        ];
    }

    #[Test]
    #[DataProvider('provideAlgo')]
    public function sign($algo): void
    {
        $signature = $this->hmac->sign($algo, $this->payload, $this->secret);
        $this->assertIsString($signature, 'Signature is not a string.');
        $this->assertNotEmpty($signature, 'Signature is empty.');
    }

    #[Test]
    #[DataProvider('provideAlgo')]
    #[Depends('sign')]
    public function verify($algo): void
    {
        $signature = $this->hmac->sign($algo, $this->payload, $this->secret);
        $this->assertTrue($this->hmac->verify($algo, $this->payload, $this->secret, $signature), 'Good secret failed validation.');
        $this->assertFalse($this->hmac->verify($algo, $this->payload, random_string(), $signature), 'Bad secret passed validation.');
    }

}