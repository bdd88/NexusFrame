<?php

use NexusFrame\Validate\AbstractConfig;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AbstractConfigTest extends TestCase
{
    private ConcreteConfig $config;
    private string $configPath;

    public static function setUpBeforeClass(): void
    {
        require_once realpath(__DIR__) . DIRECTORY_SEPARATOR . 'ConcreteConfig.php';
    }

    protected function setUp(): void
    {
        $this->configPath = realpath(__DIR__) . DIRECTORY_SEPARATOR . 'example.ini';
        $this->config = new ConcreteConfig($this->configPath);
    }

    #[Test]
    public function validate(): void
    {
        $this->assertTrue($this->config->loadConfig($this->configPath), 'Failed to load configuration file.');
        $this->assertNotEmpty($this->config->getAll(), 'Configuration is empty.');
        $this->assertNotEmpty($this->config->getSection('derp'), 'Failed to get a specific section.');
        $this->assertNotEmpty($this->config->getSetting('stuff'), 'Failed to get a specific setting.');
        $this->assertIsBool($this->config->getSetting('lerp', 'derp'), 'Failed to get a specific setting from a subsection.');
        $this->assertTrue($this->config->validate(), 'Config failed validation.');
    }


    #[Test]
    public function missingSection(): void
    {
        $this->expectException(Exception::class);
        $this->config->getSection('notHere');
    }

    #[Test]
    public function missingSetting(): void
    {
        $this->expectException(Exception::class);
        $this->config->getSetting('missingSetting', 'derp');
    }

    #[Test]
    public function badConfig(): void
    {
        $this->config = new ConcreteConfig();
        $this->assertFalse($this->config->loadConfig('missingFile.ini'), 'Loaded a config that doesn\'t exist.');
        $this->expectException(Exception::class);
        $this->config->getAll();
    }

    #[Test]
    public function badAssertType(): void
    {
        $this->config->loadConfig(realpath(__DIR__) . DIRECTORY_SEPARATOR . 'example.ini');
        $this->expectException(Exception::class);
        $this->config->badAssertType();
    }

    #[Test]
    public function badAssertValue(): void
    {
        $this->config->loadConfig(realpath(__DIR__) . DIRECTORY_SEPARATOR . 'example.ini');
        $this->expectException(Exception::class);
        $this->config->badAssertValue();
    }
}