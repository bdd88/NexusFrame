# ConfigAbstract
This is an abstract class that can be extended in order to load, validate, and store a PHP ini configuration file as an object. If a configuration file fails validation, then an exception is thrown.

## Installation
#### Composer:
composer require bdd88/configabstract

#### Manual:
Simply download and include/require ConfigAbstract.php

## Usage
1. Extend the ConfigAbstract class, and overwrite the 'validate' method.
2. The validate method should use 'verifySettingsAreSet' and 'verifySettingsType' methods to define how to validate the configuration file settings.
3. Instantiate the concrete config file object by supplying it with a configuration file path.
4. Utilize 'getAllSettings' and 'getSection' methods to retrive settings from the config object.
5. Catch and handle thrown exceptions as needed.

## Example
**Concrete class file**
```
class ConfigDatabase extends \Bdd88\ConfigAbstract\ConfigAbstract
{
    protected function validate(): void
    {
        // Throw an exception if the following settings are missing.
        $this->verifySettingsAreSet(array(
            'hostname',
            'database',
            'username',
            'password'
        ));

        // Throw an exception if the following settings don't have the correct data type.
        $this->verifySettingsType('string', array(
            'hostname',
            'database',
            'username',
            'password'
        ));
    }
}
```

**Implementation**
```
try {
    $databaseConfiguration = new ConfigDatabase('path/to/configuration/file.ini');
    $specificSectionSettings = $databaseConfiguration->getSection('SectionName');
    $allSettings = $databaseConfiguration->getAllSettings();
} catch (Exception $e) {
    // Exception handling code
}
```