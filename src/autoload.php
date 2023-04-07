<?php

// Include this file to register the autoloader and NexusFrame namespace.
$basedir = realpath(__FILE__);
require_once $basedir . '/Dependency/AutoLoader.php';
$autoLoader = new \NexusFrame\Dependency\AutoLoader();
spl_autoload_register(array($autoLoader, 'load'));
$autoLoader->register('\NexusFrame', $basedir);