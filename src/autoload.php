<?php

// Include this file to register the autoloader and NexusFrame namespace.
$basedir = realpath(__DIR__);
require_once $basedir . DIRECTORY_SEPARATOR . 'Dependency' . DIRECTORY_SEPARATOR . 'AutoLoader.php';
$autoLoader = new \NexusFrame\Dependency\AutoLoader();
spl_autoload_register(array($autoLoader, 'load'));
$autoLoader->register('NexusFrame', $basedir);