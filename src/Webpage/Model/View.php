<?php
namespace NexusFrame\Webpage\Model;

/** Uses the output buffer to parse html/php to generate a page view. */
class View
{

    public function generate(string $path, mixed $data): string
    {
        // Use the output buffer to parse code with variables to create the output.
        ob_start();
        require $path;
        $output = ob_get_contents();
        ob_end_clean();

        // TODO: Add option to minimize output before returning.
        return $output;
    }
}
