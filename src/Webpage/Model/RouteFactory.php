<?php
namespace NexusFrame\Webpage\Model;

/** Creates new routes. */
class RouteFactory
{
    public function create($name): Route
    {
        return new Route($name);
    }
}
