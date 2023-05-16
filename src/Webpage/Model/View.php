<?php
namespace NexusFrame\Webpage\Model;

/** Uses the output buffer to parse html/php to generate a page view. */
class View
{

    public function generate(string $path, array $data): string
    {
        // Move each parameter to it's own variable to make writing view templates easier.
        $data ??= array();
        foreach ($data as $key => $value) $$key = $value;

        // Use the output buffer to parse code with variables to create the output.
        ob_start();
        require $path;
        $output = ob_get_contents();
        ob_end_clean();

        // TODO: Add option to minimize output before returning.
        return $output;
    }
}
