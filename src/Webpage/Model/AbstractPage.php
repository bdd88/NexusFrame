<?php
namespace NexusFrame\Webpage\Model;

abstract class AbstractPage
{
    /**
     * Generate data from a page model that can be used by the View to generate html.
     *
     * @return array Variables that can be used in view templates.
     */
    abstract public function generate(): array;
}
