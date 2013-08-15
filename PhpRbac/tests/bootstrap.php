<?php
// turn on all errors
error_reporting(E_ALL);

// autoloader
require dirname(__DIR__) . '/autoload.php';

/*
// Not using this (yet?)
// default globals
if (is_readable(__DIR__ . '/globals.default.php')) {
    require __DIR__ . '/globals.default.php';
}

// override globals
if (is_readable(__DIR__ . '/globals.php')) {
    require __DIR__ . '/globals.php';
}
//*/
