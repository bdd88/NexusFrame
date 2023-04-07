<?php

use NexusFrame\Token\Jwt\Algorithms\Rsa;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RsaTest extends TestCase
{
    private Rsa $rsa;
    private array $keys;
    private string $payload;

    public function setUp(): void
    {
        $this->rsa = new Rsa();
        $this->keys = $this->rsa->generateKeys();
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
    public function generateKeys(): void
    {
        $this->assertIsArray($this->keys, 'Not an array.');
        $this->assertCount(2, $this->keys, 'Key array doesn\'t contain two entries');
        $this->assertIsString($this->keys['private'], 'Private key is not a string.');
        $this->assertNotEmpty($this->keys['private'], 'Private key is empty.');
        $this->assertIsString($this->keys['public'], 'Public key is not a string.');
        $this->assertNotEmpty($this->keys['public'], 'Public key is empty.');
    }

    #[Test]
    #[DataProvider('provideAlgo')]
    #[Depends('generateKeys')]
    public function sign($algo): void
    {
        $signature = $this->rsa->sign($algo, $this->payload, $this->keys['private']);
        $this->assertIsString($signature, 'Signature is not a string.');
        $this->assertNotEmpty($signature, 'Signature is empty.');
    }

    #[Test]
    #[DataProvider('provideAlgo')]
    #[Depends('generateKeys')]
    #[Depends('sign')]
    public function verify($algo): void
    {
        $signature = $this->rsa->sign($algo, $this->payload, $this->keys['private']);
        $this->assertTrue($this->rsa->verify($algo, $this->payload, $this->keys['public'], $signature), 'Good payload, key, and signature failed verification.');
        $this->assertFalse($this->rsa->verify($algo, random_string(), $this->keys['public'], $signature), 'Bad payload passed verification.');
        $this->assertFalse($this->rsa->verify($algo, $this->payload, $this->keys['public'], $signature . 'derp'), 'Bad signature passed verification.');
        $this->expectException(Exception::class);
        $this->rsa->verify($algo, $this->payload, random_string(), $signature);
    }

}