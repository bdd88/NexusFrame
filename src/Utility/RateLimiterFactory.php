<?php
namespace NexusFrame\Utility;

/** Creates rate limiter objects. */
class RateLimiterFactory
{
    public function create(float $rateLimit): RateLimiter
    {
        return new RateLimiter($rateLimit);
    }
}
