<?php

use NexusFrame\Dependency\ServiceContainer;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ServiceContainerTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        require_once realpath(__DIR__) . DIRECTORY_SEPARATOR . 'FakeClasses.php';
    }

    #[Test]
    public function createAndGet(): void
    {
        $serviceContainer = new ServiceContainer();
        $additionalArguments = $serviceContainer->create('SomeNamespace\SomeSubNamespace\someClassA', ['My Parameter', 5]);
        $recursiveDependencies = $serviceContainer->create('\SomeNamespace\SomeSubNamespace\someClassD');
        $noDependencies = $serviceContainer->create('\SomeNamespace\SomeSubNamespace\someClassE');
        $noConstructor = $serviceContainer->create('\SomeNamespace\SomeSubNamespace\someClassF');

        $indirectlyCreated = $serviceContainer->get('SomeNamespace\SomeSubNamespace\someClassB');
        $multipleDependencies = $serviceContainer->get('\SomeNamespace\SomeSubNamespace\someClassC');
        $getFake = $serviceContainer->get('SomeNamespace\SomeSubNamespace\alsoNotReal');
        
        $this->assertIsObject($additionalArguments, 'Object A was not created.');
        $this->assertIsObject($indirectlyCreated, 'Object B was not created.');
        $this->assertIsObject($multipleDependencies, 'Object C was not created.');
        $this->assertIsObject($recursiveDependencies, 'Object D was not created.');
        $this->assertIsObject($noDependencies, 'Object E was not created.');
        $this->assertNull($getFake, 'Managed to retrieve a class that does not exist.');

        $this->assertEquals('My Parameter', $additionalArguments->someArg, 'Object A was not instantiated correctly.');
        $this->assertEquals('Class B', $indirectlyCreated->name, 'Object B was not instantiated correctly.');
        $this->assertEquals('Class C', $multipleDependencies->name, 'Object C was not instantiated correctly.');
        $this->assertEquals('Class D', $recursiveDependencies->name, 'Object D was not instantiated correctly.');
        $this->assertEquals('Class E', $noDependencies->name, 'Object E was not instantiated correctly.');
        $this->assertEquals('Class F', $noConstructor->name, 'Object F was not instantiated correctly.');

        $this->expectException(Exception::class);
        $abstract = $serviceContainer->create('SomeNamespace\SomeSubNamespace\someAbstract');

        $this->expectException(Exception::class);
        $noDefinition = $serviceContainer->create('SomeNamespace\SomeSubNamespace\notReal');

        $this->expectException(Exception::class);
        $missingDependency = $serviceContainer->create('\SomeNamespace\SomeSubNamespace\someClassG');
    }

/*     #[Test]
    public function failureConditions(): void
    {
        $abstractClass = '';
    } */

}