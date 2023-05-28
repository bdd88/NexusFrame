<?php
namespace NexusFrame\Webpage\Model;

/** Stores data for routing to and generating a page. */
class Route
{
    public string $name;
    public string $class;
    public string $pageViewPath;
    public string $layoutViewPath;
    public array $parameters;
    public bool $loginRequired;
    public bool $enabled;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function setClass(string $class): self
    {
        $this->class = $class;
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

    public function setParameters(string $parameters): self
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
}
