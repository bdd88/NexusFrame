## AutoLoader
A simple PSR-4 compliant class autoloader. This lightweight autoloader is a good replacement for more comprehensive autoloaders (such as composer) in small or simple projects.

### Installation
Simply place the AutoLoader.php in your project directory and require/include it.

### Example
```
// Load and register the autoloader itself
require './AutoLoader.php';
$autoLoader = new \Bdd88\Autoloader\AutoLoader();
spl_autoload_register(array($autoLoader, 'load'));

// Map namespaces to their appropriate directory. Directory paths can be relative or absolute.
// Sub namespaces can be assigned to different directories than the base namespace if needed.
$autoLoader->register('\Test', '.');

// Start using your classes without needing to require/include the files.
$someClass = new \Test\SomeClass();
```