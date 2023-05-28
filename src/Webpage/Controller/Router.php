<?php
namespace NexusFrame\Webpage\Controller;

use NexusFrame\Webpage\Model\Route;

/** Stores and retrieves routes. */
class Router
{
    /** @var Route[] $routes */
    private array $routes;

    public function __construct(private Session $session)
    {
    }

    /** Store a new route. */
    public function create(Route $route): void
    {
        $this->routes[$route->name] = $route;
    }

    public function get(string $requestedPage): Route|FALSE
    {
        if (!isset($this->routes[$requestedPage])) {
            $errorCode = 404;
        } elseif ($this->routes[$requestedPage]->enabled === FALSE) {
            $errorCode = 403;
        } elseif ($this->routes[$requestedPage]->loginRequired === TRUE && $this->session->status() === FALSE) { // TODO: Implement permissions check.
            $errorCode = 401;
        }
        if (isset($errorCode)) {
            http_response_code($errorCode);
            if (isset($this->routes[$errorCode])) return $this->routes[$errorCode]; // TODO: Handle error pages in a special way, so they don't take up potential page names.
            return FALSE;
        }
        return $this->routes[$requestedPage];
    }
}
