<?php
// Dummy classes for Service Container unit tests.
namespace SomeNamespace\SomeSubNamespace;

class someClassA
{
    public string $name = 'Class A';
    public function __construct(public string $someArg, public int $anotherArg) {}
}

class someClassB
{
    public string $name = 'Class B';
    public function __construct(private someClassA $someClassA) {}
}

class someClassC
{
    public string $name = 'Class C';
    public function __construct(private someClassA $someClassA, private someClassB $someClassB) {}
}

class someClassD
{
    public string $name = 'Class D';
    public function __construct(private someClassC $someClassC) {}
}

class someClassE
{
    public string $name = 'Class E';
    public function __construct() {}
}

class someClassF
{
    public string $name = 'Class F';
}

class someClassG
{
    public string $name = 'Class G';
    public function __construct(private someClassFake $someClassFake) {}
}

abstract class someAbstract
{

}