<?php
namespace NexusFrame\Dependency;

use Exception;
use ReflectionClass;

/**
 * An autowiring recursive dependency injection container.
 * 
 * @version 1.2.0
 */
class ServiceContainer
{
    private array $reflections;
    private array $parameters;
    private array $objects;

    /** Ensure consistency for class namespaces by removing the leading slash. */
    private function validateNamespace(string $className): string
    {
        if (!empty($className) && $className[0] === '\\') {
            $className = substr($className, 1);
        }
        return $className;
    }

    /**
     * Retrieve a class reflection. Create the reflection and store it if it doesn't exist already.
     *
     * @param string $className Name of a class including the full namespace.
     * @throws Exception Throws exception if the class is missing a definition or can't be instantiated.
     * @return ReflectionClass The reflection of the given class.
     */
    private function getReflection(string $className): ReflectionClass
    {
        if (!class_exists($className)) throw new Exception('Missing class definition: ' . $className);

        if (!isset($this->reflections[$className])) $this->reflections[$className] = new ReflectionClass($className);

        if ($this->reflections[$className]->isInstantiable() === FALSE) throw new Exception('Class can\'t be instantiated.');

        return $this->reflections[$className];
    }

    /**
     * Retrieve a list of parameters with their type hints for a given class. If a list of parameters doesn't exist yet, create and store it.
     *
     * @param string $className Name of a class including the full namespace.
     * @return array Associative array containing $varName => $typeHint.
     */
    private function getParameters(ReflectionClass $reflection): array
    {
        $className = $reflection->getName();
        if (isset($this->parameters[$className])) return $this->parameters[$className];
        $parameters = array();
        if ($reflection !== NULL && $reflection->getConstructor() !== NULL) {
            foreach ($reflection->getConstructor()->getParameters() as $parameter) {
                $varName = $parameter->getName();
                $typeHint = $parameter->getType()->getName();
                $parameters[$varName] = $typeHint;
            }
        }
        $this->parameters[$className] = $parameters;
        return $parameters;
    }

    /**
     * Creates an object of a given class by using the supplied arguments and recursively instantiating and injecting all class dependencies.
     *
     * @param string $className Name of a class including the full namespace.
     * @param array $arguments Optional additional arguments supplied in an array.
     * @throws Exception Throws an exception if a class can't be instantiated, a class definition is missing, or an argument is missing.
     * @return object Returns the requested object with all dependencies injected.
     */
    public function create(string $className, array $arguments = array()): object
    {
        $className = $this->validateNamespace($className);
        $reflection = $this->getReflection($className);
        $parameters = $this->getParameters($reflection);
        $inject = array();

        while (sizeof($parameters) > 0) {
            $parameter = array_shift($parameters);
            if (isset($this->objects[$parameter])) {
                $inject[] = $this->objects[$parameter];
            } elseif (class_exists($parameter)) {
                $inject[] = $this->create($parameter);
            } else {
                if (empty($arguments)) throw new Exception('Missing class definition or arguments for: ' . $className);
                $inject[] = array_shift($arguments);
            }
        }

        $this->objects[$className] = $reflection->newInstanceArgs($inject);
        return $this->objects[$className];
    }

    /**
     * Retrieve a requested object, if it exists.
     *
     * @param string $className $className Name of a class including the full namespace.
     * @return object|NULL Returns NULL if the object hasn't been created yet.
     */
    public function get(string $className): object|NULL
    {
        $className = $this->validateNamespace($className);
        if (isset($this->objects[$className])) return $this->objects[$className];
        return NULL;
    }

}