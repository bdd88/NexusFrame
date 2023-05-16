<?php
namespace NexusFrame\Webpage\Controller;

use NexusFrame\Dependency\ServiceContainer;
use NexusFrame\Webpage\Model\View;

/** Handles the primary flow of data between the user, controllers, and models. */
class Main
{
    private ServiceContainer $serviceContainer;
    private Router $router;
    private View $view;
    private array $pages;

    public function __construct(ServiceContainer $serviceContainer, Router $router, View $view)
    {
        $this->serviceContainer = $serviceContainer;
        $this->router = $router;
        $this->view = $view;
    }

    /**
     * Create a new page configuration.
     *
     * @param string $name Name of the page. Used for requests and redirection.
     * @param string $class Class that generates data for the page.
     * @param string $viewPath PHP/HTML template used to generate html output for the page.
     * @param array|null $parameters Additional parameters to supply the page class when instantiating.
     * @param boolean|null $login Required Require authentication to load the page.
     * @param boolean|null $enabled Allow the page to be viewed.
     * @return void
     */
    public function createPage(string $name, string $class, string $viewPath, ?array $parameters = NULL, ?bool $loginRequired = NULL, ?bool $enabled = NULL): void
    {
        $parameters ??= array();
        $loginRequired ??= FALSE;
        $enabled ??= TRUE;
        $this->pages[$name] = array(
            'class' => $class,
            'view' => $viewPath,
            'parameters' => $parameters,
            'login' => $loginRequired,
            'enabled' => $enabled
        );
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
        // Use router to redirect the page to output if necessary.
        $pageName = $this->router->route($this->pages, $requestedPage);
        if ($pageName === FALSE) return FALSE;

        // Instantiate the page object, generate page data, inject page data into the view, and return the output HTML code.
        $pageObject = $this->serviceContainer->create($this->pages[$pageName]['class']);
        $pageData = $pageObject->generate($this->pages[$pageName]['parameters']);
        $output = $this->view->generate($this->pages[$pageName]['view'], $pageData);
        return $output;
    }
}
