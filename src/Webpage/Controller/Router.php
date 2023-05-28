<?php
namespace NexusFrame\Webpage\Controller;

use NexusFrame\Webpage\Model\Route;

/** Stores and retrieves routes. */
class Router
{
    /** @var Route[] $routes */
    private array $routes;
    private Route $defaultRoute;
    /** @var Route[] $errorRoutes */
    private array $errorRoutes;
    private Route $defaultErrorRoute;

    public function __construct(private Session $session)
    {
    }

    /** Store a new route for a page. */
    public function createRoute(Route $route, bool $default): void
    {
        if ($default === TRUE) {
            $this->defaultRoute = $route;
        }
        $this->routes[$route->getName()] = $route;
    }

    /** Store a new route for an error code. */
    public function createErrorRoute(Route $route, bool $default): void
    {
        if ($default === TRUE) {
            $this->defaultErrorRoute = $route;
        }
        $this->errorRoutes[$route->getName()] = $route;
    }

    public function get(?string $requestedPage = NULL): Route|NULL
    {
        if ($requestedPage === NULL && isset($this->defaultRoute)) $requestedPage = $this->defaultRoute->getName();
        if (!isset($this->routes[$requestedPage])) {
            $errorCode = 404;
        } elseif ($this->routes[$requestedPage]->getEnabled() === FALSE) {
            $errorCode = 403;
        } elseif ($this->routes[$requestedPage]->getLoginRequired() === TRUE && $this->session->status() === FALSE) { // TODO: Implement permissions check.
            $errorCode = 401;
        }
        if (isset($errorCode)) {
            http_response_code($errorCode);
            if (isset($this->errorRoutes[$errorCode])) return $this->errorRoutes[$errorCode];
            if (isset($this->defaultErrorRoute)) return $this->defaultErrorRoute;
            return NULL;
        }
        return $this->routes[$requestedPage];
    }
}
