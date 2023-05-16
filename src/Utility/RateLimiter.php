<?php
namespace NexusFrame\Utility;

/** Delays the execution of a function or method based on a specified rate limit. */
class RateLimiter
{
    private float $executionsPerMinute;
    private float $lastExecutionTime;

    public function __construct(float $executionsPerMinute)
    {
        $this->executionsPerMinute = $executionsPerMinute;
        $this->lastExecutionTime = 0;
    }

    /**
     * Delay the execution of a method until the rate limit has been reached.
     *
     * @param callable $method The method or function to rate limit and execute.
     * @param array $parameters Additional parameters to pass the method or function.
     * @param integer|null $sleepDelay (optional) The delay in microseconds between each attempted execution. Lower values may increase rate limit accuracy and resource usage. Defaults to 10,000 microseconds (0.01 seconds).
     * @return mixed The return value of the supplied method.
     */
    public function exec(callable $method, array $parameters, ?int $sleepDelay = NULL): mixed
    {
        $sleepDelay = $sleepDelay ?? 10000;
        $rateLimitDelay = bcdiv(60, $this->executionsPerMinute, 6);
        $targetExecutionTime = (float) bcadd($this->lastExecutionTime, $rateLimitDelay, 6);
        while (TRUE) {
            if (microtime(TRUE) >= $targetExecutionTime) {
                $methodReturn = $method(...$parameters);
                $this->lastExecutionTime = microtime(TRUE);
                return $methodReturn;
            } else {
                usleep($sleepDelay);
            }
        }
    }
}
