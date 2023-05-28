<?php
namespace NexusFrame\Webpage\Model;

/** Stores data for routing to and generating a page. */
class Route
{
    private string $name;
    private string $class;
    private string $method;
    private ?string $pageViewPath;
    private ?string $layoutViewPath;
    private ?array $parameters;
    private bool $loginRequired;
    private bool $enabled;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->pageViewPath = NULL;
        $this->layoutViewPath = NULL;
        $this->parameters = array();
        $this->loginRequired = FALSE;
        $this->enabled = TRUE;
    }

    public function setClass(string $class): self
    {
        $this->class = $class;
        return $this;
    }

    public function setMethod(string $method): self
    {
        $this->method = $method;
        return $this;
    }

    public function setPageViewPath(string $pageViewPath): self
    {
        $this->pageViewPath = $pageViewPath;
        return $this;
    }

    public function setLayoutViewPath(string $layoutViewPath): self
    {
        $this->layoutViewPath = $layoutViewPath;
        return $this;
    }

    public function setParameters(array $parameters): self
    {
        $this->parameters = $parameters;
        return $this;
    }

    public function setLoginRequired(string $loginRequired): self
    {
        $this->loginRequired = $loginRequired;
        return $this;
    }

    public function setEnabled(string $enabled): self
    {
        $this->enabled = $enabled;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPageViewPath(): string
    {
        return $this->pageViewPath;
    }

    public function getLayoutViewPath(): string|NULL
    {
        return $this->layoutViewPath;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getLoginRequired(): bool
    {
        return $this->loginRequired;
    }

    public function getEnabled(): bool
    {
        return $this->enabled;
    }
}
