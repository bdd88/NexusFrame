<?php
namespace NexusFrame\Validate;

use Exception;

/** Provides a base class for parsing and validating settings from a PHP parsable ini file. */
abstract class AbstractConfig
{
    public string $filename;
    protected string $directory;
    protected array $settings;

    public function __construct(string $path = NULL)
    {
        if ($path !== NULL) {
            $this->loadConfig($path);
        }
    }

    /**
     * Use this function to perform assertions on the loaded configuration, and handle exceptions as needed.
     *
     * @return boolean Should return TRUE if validation succeeded or FALSE if it failed.
     */
    abstract public function validate(): bool;

    /**
     * Load a PHP parsable ini file for validation.
     *
     * @param string $path
     * @return boolean Returns FALSE if the provided file path could not be loaded.
     */
    public function loadConfig(string $path): bool
    {
        $path = realpath($path);
        if (!file_exists($path)) return FALSE;
        $pathArray = explode(DIRECTORY_SEPARATOR, $path);
        $this->filename = array_pop($pathArray);
        $this->directory = implode(DIRECTORY_SEPARATOR, $pathArray);
        $this->settings = parse_ini_file($path, TRUE, INI_SCANNER_TYPED);
        if ($this->settings === FALSE) return FALSE;
        return TRUE;
    }

    /**
     * Retrieve all settings from the loaded config.
     *
     * @throws Exception Throws exception if the config is empty.
     * @return array
     */
    public function getAll(): array
    {
        if (!isset($this->settings)) throw new Exception("No settings have been loaded.");
        return $this->settings;
    }

    /**
     * Retrieve a specific section from the loaded config.
     *
     * @throws Exception Throws exception if the section is missing.
     * @return array
     */
    public function getSection(string $section = NULL): array
    {
        $allSettings = $this->getAll();
        if ($section === NULL) return $allSettings;
        if (!isset($allSettings[$section]) || gettype($allSettings[$section]) !== 'array') {
            throw new Exception("Specified section '$section' is missing.");
        }
        return $allSettings[$section];
    }

    /**
     * Retrieve a specific setting from the loaded config.
     *
     * @throws Exception Throws exception if the setting is missing
     * @param string $setting
     * @param string $section
     * @return mixed
     */
    public function getSetting(string $setting, string $section = NULL): mixed
    {
        $sectionSettings = $this->getSection($section);
        if (!isset($sectionSettings[$setting]) || gettype($sectionSettings[$setting]) === 'array') {
            throw new Exception("Specified setting '$setting' is missing.");
        }
        return $sectionSettings[$setting];
    }

    /**
     * Assert that a setting exists.
     *
     * @return boolean
     */
    protected function assertExist(string $setting, string $section = NULL): void
    {
        $this->getSetting($setting, $section);
    }

    /**
     * Assert that a setting is equal to a specified datatype.
     * Implicitly asserts that the setting exists.
     *
     * @return boolean
     */
    protected function assertType(string $datatype, string $setting, string $section = NULL): void
    {
        if (gettype($this->getSetting($setting, $section)) !== $datatype) {
            throw new Exception("Asserting setting '$setting' is of datatype '$datatype' failed.");
        }
    }

    /**
     * Assert that a setting is equal to a specified value.
     * Implicitly asserts that the setting exists and is the same datatype.
     *
     * @return boolean
     */
    protected function assertValue(mixed $value, string $setting, string $section = NULL): void
    {
        $this->assertType(gettype($value), $setting, $section);
        if ($this->getSetting($setting, $section) !== $value) {
            throw new Exception("Asserting setting '$setting' is of value '$value' failed.");
        }
    }

    /**
     * Perform a batch of assertions on a provided array.
     *
     * @param string $assertionType assertExist, assertValue, or assertType
     * @param array $values An associative array of settings to validate. Subarrays can be provided to validate subsections.
     * @return void
     */
    protected function assertMultiple(string $assertionType, array $values): void
    {
        $queue = array();
        foreach ($values as $sectionOrSetting => $value) {
            $test = gettype($value);
            if (gettype($value) === 'array') {
                foreach ($value as $setting => $settingValue) {
                    $queue[] = array('section' => $sectionOrSetting, 'setting' => $setting, 'value' => $settingValue);
                }
            } else {
                $queue[] = array('section' => NULL, 'setting' => $sectionOrSetting, 'value' => $value);
            }
        }

        foreach ($queue as $assertion) {
            if ($assertionType === 'assertExist') {
                $this->assertExist($assertion['value'], $assertion['section']);
            } elseif ($assertionType === 'assertValue') {
                $this->assertValue($assertion['value'], $assertion['setting'], $assertion['section']);
            } elseif ($assertionType === 'assertType') {
                $this->assertType($assertion['value'], $assertion['setting'], $assertion['section']);
            }
        }
    }

    /**
     * Validate that all provided settings are present in the config.
     *
     * @param array $settings
     * @return void
     */
    protected function assertExists(array $settings): void
    {
        $this->assertMultiple('assertExist', $settings);
    }

    /**
     * Validate that all provided setting values are equal to setting values in the config.
     *
     * @param array $values
     * @return void
     */
    protected function assertValues(array $values): void
    {
        $this->assertMultiple('assertValue', $values);
    }

    /**
     * Validate that all provided setting datatypes are equal to setting datatypes in the config.
     *
     * @param array $types
     * @return void
     */
    protected function assertTypes(array $types): void
    {
        $this->assertMultiple('assertType', $types);
    }

}