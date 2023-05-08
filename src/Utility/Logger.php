<?php
namespace NexusFrame\Utility;

use Exception;

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
    public function createLog(string $name, ?string $path = NULL, ?string $enabled = NULL): void
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
        if ($configured === FALSE) throw new Exception('Settings have not been configured for log: ' . $logName);

        if ($this->logSettings[$logName]['enabled'] === FALSE) return 0;

        $stream = fopen($this->logSettings[$logName]['path'], 'a');
        if ($stream === FALSE) throw new Exception('Unable to open or create log file at path: ' . $this->logSettings[$logName]['path']);

        $write = fwrite($stream, date("Y/m/d H:i:s") . ' - ' . $contents . PHP_EOL);
        if ($write === FALSE) throw new Exception('Unable to write to log file at path: ' . $this->logSettings[$logName]['path']);

        $close = fclose($stream);
        if ($close === FALSE) throw new Exception('Unable to close file pointer to log file at path: ' . $this->logSettings[$logName]['path']);

        return $write;
    }
}
