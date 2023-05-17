<?php

// Initiate the autoloader.
require_once './src/Dependency/AutoLoader.php';
new \NexusFrame\Dependency\AutoLoader();

// Return a random string that may contain uppercase letters, lowercase letters, numbers, and special characters.
function random_string(int $length = NULL): string
{
    $length ??= rand(1, 100);
    $characters = '~!@#$%^&*()_+`-=[]{};\':"<>?,./\|1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ ';
    $charCount = strlen($characters);
    $string = '';
    for ($i = 0; $i < $length; $i++) {
        $charPos = rand(0, $charCount - 1);
        $string .= $characters[$charPos];
    }
    return $string;
}
