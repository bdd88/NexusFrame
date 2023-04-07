<?php

use NexusFrame\Dependency\AutoLoader;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AutoLoaderTest extends TestCase
{
    private AutoLoader $autoLoader;

    public function setUp(): void
    {
        $this->autoLoader = new AutoLoader();
    }

    #[Test]
    public function registerAndLoad(): void
    {
        $dir = realpath(__DIR__);

        $this->autoLoader->register('SomeDummy', $dir);
        $this->autoLoader->load('SomeDummy\DummyClass');
        $dummyObject = new \SomeDummy\DummyClass();
        $this->assertTrue(is_object($dummyObject));

        $this->autoLoader->register('\SomeDummy\Dum', $dir);
        $this->autoLoader->load('\SomeDummy\Dum\DumClass');
        $dumObject = new \SomeDummy\Dum\DumClass();
        $this->assertTrue(is_object($dumObject));

    }

}