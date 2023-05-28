<?php
namespace NexusFrame\Webpage\Controller;

use NexusFrame\Webpage\Model\View;
use NexusFrame\Webpage\Model\Route;
use NexusFrame\Webpage\Model\RouteFactory;
use NexusFrame\Dependency\ServiceContainer;
use NexusFrame\Webpage\Model\AbstractPage;

/** Handles the primary flow of data between the user, controllers, and models. */
class Main
{
    public function __construct(
        private ServiceContainer $serviceContainer,
        private Router $router,
        private RouteFactory $routeFactory,
        private View $view
        )
    {
    }

    /** Generates a route for a new page. */
    public function createPage(string $name): Route
    {
        $route = $this->routeFactory->create($name);
        $this->router->create($route);
        return $route;
    }

    // TODO: Add methods for enabling/disabling pages, as well as authentication and authorization methods. Possibly handle in a separate class.

    /**
     * Generate and return requested page (or redirected page) and set the appropriate HTTP status code.
     *
     * @param string $requestedPage The page being requested.
     * @return string|FALSE HTML code for the page to output, or FALSE if no output code was generated (e.g. the page is missing and there is no custom 404 page to display).
     */
    public function exec(string $requestedPage): string|FALSE
    {
        // Retrieve the routing information in order to generate the page.
        $route = $this->router->get($requestedPage);
        if ($route === FALSE) return FALSE;
        /** @var AbstractPage $pageObject */
        $pageObject = $this->serviceContainer->create($route->class);
        $pageData = $pageObject->generate($route->parameters);
        $output = $this->view->generate($route->pageViewPath, $pageData);
        if (isset($route->layoutViewPath)) {
            $output = $this->view->generate($route->layoutViewPath, $output);
        }
        return $output;
    }
}
