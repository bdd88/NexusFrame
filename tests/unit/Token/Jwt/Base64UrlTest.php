<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use NexusFrame\Token\Jwt\Encoding\Base64Url;
use PHPUnit\Framework\Attributes\Depends;

class Base64UrlTest extends TestCase
{
    private Base64Url $base64Url;
    private string $payload;

    public function setUp(): void
    {
        $this->base64Url = new Base64Url();
        $this->payload = random_string();
        
    }

    #[Test]
    public function encode(): void
    {
        $encoded = $this->base64Url->encode($this->payload);
        $this->assertIsString($encoded, 'Not a string.');
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9_-]+$/', $encoded, 'Encoded string is not valid Base64URL.');
    }

    #[Test]
    #[Depends('encode')]
    public function decode(): void
    {
        $encoded = $this->base64Url->encode($this->payload);
        $decoded = $this->base64Url->decode($encoded);
        $this->assertIsString($decoded, 'Not a string.');
        $this->assertEquals($this->payload, $decoded, 'Decoded string doesn\'t match original string.');
    }
}