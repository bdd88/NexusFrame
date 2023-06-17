<?php
namespace NexusFrame\Utility;

/** Creates log files and log entries. */
class Logger
{
    private array $logSettings;

    /**
     * Undocumented function
     *
     * @param string $name Name of the log file. Used as a reference in other logger methods.
     * @param string|null $path (optional) Path to the actual log file. Defaults to the current working directory using the $name if not specified.
     * @param string|null $enabled (optional) Whether the log file should be writable. Can be modified with enableLog() and disableLog() methods.
     * @return void
     */
    public function setup(string $name, ?string $path = NULL, ?string $enabled = NULL): void
    {
        if (isset($path)) {
            // Make sure path is stored as an absolute path.
            $pathArray = explode(DIRECTORY_SEPARATOR, $path);
            $fileName = array_pop($pathArray);
            $fileDir = implode(DIRECTORY_SEPARATOR, $pathArray);
            $path = realpath($fileDir) . DIRECTORY_SEPARATOR . $fileName;
        } else {
            // If no path was supplied, default to naming the file $name.log, and place it in the current working directory.
            $path = getcwd() . DIRECTORY_SEPARATOR . $name . '.log';
        }

        $enabled ??= TRUE;
        if (isset($this->logSettings[$name])) trigger_error('Settings for log ' . $name . ' already exist.', E_USER_NOTICE);
        $this->logSettings[$name] = array();
        $this->logSettings[$name]['path'] = $path;
        $this->logSettings[$name]['enabled'] = $enabled;
    }

    /**
     * Enable writing to a log.
     *
     * @param string $logName Name of the log file.
     * @return void
     */
    public function enableLog(string $logName): void
    {
        $this->logSettings[$logName]['enabled'] = TRUE;
    }

    /**
     * Disable writing to a log.
     *
     * @param string $logName Name of the log file.
     * @return void
     */
    public function disableLog(string $logName): void
    {
        $this->logSettings[$logName]['enabled'] = FALSE;
    }

    /**
     * Append contents to a log file.
     *
     * @param string $logName Name of the log file to write to.
     * @param string $contents Contents to append to the log file.
     * @throws Exception Throws exception if there are any issues locating, opening, writing, or closing the log.
     * @return integer Number of bytes written to the log file.
     */
    public function log(string $logName, string $contents): int
    {
        $configured = isset($this->logSettings[$logName]);
        if ($configured === FALSE) {
            trigger_error('Attempting to write to a log that has not be configured: ' . $logName, E_USER_NOTICE);
            return 0;
        }

        if ($this->logSettings[$logName]['enabled'] === FALSE) return 0;

        $stream = fopen($this->logSettings[$logName]['path'], 'a');
        if ($stream === FALSE) {
            trigger_error('Unable to open or create log file at path: ' . $this->logSettings[$logName]['path'], E_USER_NOTICE);
            return 0;
        }

        $write = fwrite($stream, date("Y/m/d H:i:s") . ' - ' . $contents . PHP_EOL);
        if ($write === FALSE) {
            trigger_error('Unable to write to log file at path: ' . $this->logSettings[$logName]['path'], E_USER_NOTICE);
            return 0;
        }

        $close = fclose($stream);
        if ($close === FALSE) {
            trigger_error('Unable to close file pointer to log file at path: ' . $this->logSettings[$logName]['path'], E_USER_NOTICE);
            return 0;
        }

        return $write;
    }
}
