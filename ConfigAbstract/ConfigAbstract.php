<?php
namespace Bdd88\ConfigAbstract;

use Exception;

/**
 * Handles loading and validation of configuration files.
 * Extend this class and create a validate() method that uses verifySettingsAreSet() and verifySettingsType() to handle specific configuration files.
 */
abstract class ConfigAbstract
{
    protected string $configName;
    protected array $settings;
    protected array $settingsTypes;

    public function __construct(protected string $filePath)
    {
        $this->configName = end(explode(DIRECTORY_SEPARATOR, $this->filePath));
        $this->loadFile();
        $this->mapSettingsTypes();
        $this->validate();
    }

    /** Verify necessary settings are present in the configuration file. */
    abstract protected function validate(): void;

    /** Create a flattened hashmap of setting value types that can be used for quick lookup in verification methods. */
    protected function mapSettingsTypes(): void
    {
        $settingsMap = array();
        foreach ($this->settings as $key => $value) {
            if (is_array($this->settings[$key])) {
                foreach ($this->settings[$key] as $subKey => $subValue) {
                    $settingsMap[$key . '.' . $subKey] = gettype($subValue);
                }
            } else {
                $settingsMap[$key] = gettype($value);
            }
        }
        $this->settingsTypes = $settingsMap;
    }

    /**
     * Verify that all required settings are present in the configuration.
     *
     * @param array $settings Settings in subsections should be prefaced by the subsection name and a period, IE: sectionName.settingName
     * @throws Exception Missing settings.
     * @return void
     */
    protected function verifySettingsAreSet(array $settings)
    {
        $missingSettings = array();
        foreach ($settings as $setting) {
            if (!isset($this->settingsTypes[$setting])) {
                $missingSettings[] = $setting;
            }
        }
        if (sizeof($missingSettings) > 0) {
            throw new Exception( $this->configName . ' is missing the following settings: ' . implode(', ', $missingSettings));
        }
    }

    /**
     * Verify that settings are correctly typed in the configuration.
     *
     * @param string $type Should be one of the following: integer, double, string, array, NULL, boolean
     * @param array $settings Settings that are missing from the configuration file will be ignored. Settings in subsections should be prefaced by the subsection name and a period, IE: sectionName.settingName
     * @throws Exception Incorrectly typed settings.
     * @return void
     */
    protected function verifySettingsType(string $type, array $settings)
    {
        $incorrectlyTypedSettings = array();
        foreach ($settings as $setting) {
            if (isset($this->settingsTypes[$setting])) {
                if ($this->settingsTypes[$setting] !== $type) {
                    $incorrectlyTypedSettings[] = $setting . '(' . $this->settingsTypes[$setting] . ')';
                }
            }
        }
        if (sizeof($incorrectlyTypedSettings) > 0) {
            throw new Exception( $this->configName . ' has settings with incorrect typing (should be ' . $type . '): ' . implode(', ', $incorrectlyTypedSettings));
        }
    }

    /**
     * Parse and store settings from the specified config file.
     *
     * @throws Exception Configuration file doesn't exist.
     * @return void
     */
    public function loadFile(): void
    {
        if (file_exists($this->filePath) === FALSE) {
            throw new Exception('Configuration file doesn\'t exist at specified path: ' . $this->filePath);
        }
        $this->settings = parse_ini_file($this->filePath, TRUE, INI_SCANNER_TYPED);
    }

    /**
     * Return all settings from the config.
     *
     * @throws Exception Configuration file is empty.
     * @return array
     */
    public function getAllSettings(): array
    {
        if (sizeof($this->settings) === 0) {
            throw new Exception( $this->configName . ' configuration file doesn\'t have any settings');
        }
        return $this->settings;
    }

    /**
     * Return a specific section from the config.
     *
     * @throws Exception Missing section.
     * @param string $sectionName
     * @return array
     */
    public function getSection(string $sectionName): array
    {
        if (!isset($this->settings[$sectionName])) {
            throw new Exception( $this->configName . ' configuration file doesn\'t have the section: ' . $sectionName);
        }
        return $this->settings[$sectionName];
    }
}

?>
