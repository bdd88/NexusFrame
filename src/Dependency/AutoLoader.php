<?php
namespace NexusFrame\Dependency;

/**
 * A lazy autoloader that follows PSR-4 specifications.
 * @link https://www.php-fig.org/psr/psr-4/
 */
class AutoLoader
{
    private array $namespaces;

    /**
     * Create the autoloader object.
     *
     * @param boolean|null $autoConfigure (optional) Call the autoConfigure method during construction. Defaults to TRUE.
     */
    public function __construct(?bool $autoConfigure = NULL)
    {
        $autoConfigure ??= TRUE;
        if ($autoConfigure) $this->autoConfigure();
    }

    /**
     * Register the autoloader load method with spl_autoload_register, register an empty namespace to the current working directory, and register the framework namespace at the appropriate directory.
     *
     * @return void
     */
    public function autoConfigure(): void
    {
        spl_autoload_register(array($this, 'load'));
        $this->register('', getcwd());
        $this->register('NexusFrame', dirname(__DIR__, 1));
    }

    /**
     * Ensure consistency for class namespaces by removing the leading slash.
     *
     * @param string $className
     * @return string
     */
    private function validateNamespace(string $className): string
    {
        if (!empty($className) && $className[0] === '\\') {
            $className = substr($className, 1);
        }
        return $className;
    }

    /**
     * Register a namespace to a directory.
     * Sub namespace directories will be determined automatically using the base namespace directory (per the PSR-4 spec), unless they are manually registered to a different directory.
     *
     * @param string $namespace
     * @param string $path
     * @return void
     */
    public function register(string $namespace, string $path): void
    {
        $namespace = $this->validateNamespace($namespace);
        $this->namespaces[$namespace] = realpath($path);
    }

    /**
     * Load a class file from a registered namespace.
     *
     * @param string $class Should include the fully qualified namespace.
     * @return boolean TRUE if file loaded, or FALSE if file wasn't found.
     */
    public function load(string $class): void
    {
        $class = $this->validateNamespace($class);

        // Find a base registered namespace by checking for the most specific to the least specific sub namespaces.
        $baseNamespace = explode('\\', $class);
        while (sizeof($baseNamespace) > 0) {
            array_pop($baseNamespace);
            $baseNamespaceString = implode('\\', $baseNamespace);
            if (isset($this->namespaces[$baseNamespaceString])) {
                break;
            }
        }

        // Construct a path to the class file by combining the registered base namespace directory with the expected sub namespace directories.
        $path = $this->namespaces[$baseNamespaceString];
        $subNamespace = array_diff(explode('\\', $class), $baseNamespace);
        foreach ($subNamespace as $dir) {
            $path .= DIRECTORY_SEPARATOR . $dir;
        }
        $path .= '.php';
        if (file_exists($path)) {
            require $path;
        }
    }

}